<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin PHP client for the BangBang FastAPI chat server.
 *
 * Server-to-server: uses X-Service-Key on /auth/service-token to mint a
 * per-DRS-user JWT that the browser then uses for REST and WebSocket.
 * Per-user JWTs are cached briefly so we don't hammer /auth/service-token.
 */
class ChatClient
{
    public function isConfigured(): bool
    {
        return filled(config('chat.api_url')) && filled(config('chat.service_key'));
    }

    /**
     * Mint (or reuse a cached) BangBang access token for a DRS user.
     *
     * @return array{access_token:string,refresh_token:string,expires_in:int,user:array}
     */
    public function mintUserToken(User $user): array
    {
        $this->assertConfigured();

        return Cache::remember(
            $this->tokenCacheKey($user),
            (int) config('chat.token_cache_seconds', 300),
            fn () => $this->requestServiceToken($user),
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function rooms(User $user, array $query = []): array
    {
        $data = $this->userRequest($user)->get('/rooms', $query)->throw()->json();
        return $data['rooms'] ?? $data['items'] ?? (is_array($data) ? $data : []);
    }

    /**
     * Open (or reuse) the member's support room with the automated assistant.
     *
     * Not `createRoom(type: support)`: a support room made by hand has nobody
     * assigned, so it sits in the queue looking answered while nothing
     * replies. This is the signed-in equivalent of the public /support/guest
     * entry point - BangBang attaches the assistant and it answers.
     */
    public function startSupport(User $user, bool $automated = true): array
    {
        return $this->userRequest($user)
            ->post('/support/start', ['automated' => $automated])
            ->throw()->json();
    }

    /**
     * Start a fresh /support/guest session (bot auto-replies) and cache the
     * guest bearer so subsequent /messages calls post as the guest — the only
     * user BangBang's automated assistant will engage with.
     *
     * @return array{room:array,messages:array,access_token:string,user:array}
     */
    public function initGuestSupport(User $user, string $firstMessage): array
    {
        $resp = Http::baseUrl(config('chat.api_url'))
            ->timeout(config('chat.http_timeout'))
            ->acceptJson()
            ->asJson()
            ->post('/support/guest', [
                'full_name' => $user->name,
                'contact'   => $user->email,
                'message'   => $firstMessage,
            ])
            ->throw()
            ->json();

        // Persist for the life of the ticket. Cleared on closeRoom / feedback.
        Cache::put(
            $this->guestSessionKey($user),
            [
                'access_token' => $resp['access_token'],
                'user_id'      => $resp['user']['id'] ?? null,
                'room_id'      => $resp['room']['id'] ?? null,
            ],
            (int) config('chat.token_cache_seconds', 300) * 6, // ~30 min
        );

        return $resp;
    }

    private function guestSession(User $user): ?array
    {
        return Cache::get($this->guestSessionKey($user));
    }

    private function guestSessionKey(User $user): string
    {
        return 'chat:guest:user:' . $user->id;
    }

    public function forgetGuestSession(User $user): void
    {
        Cache::forget($this->guestSessionKey($user));
    }

    public function createRoom(User $user, array $payload): array
    {
        // Use the user's bearer token so BangBang attributes ownership correctly.
        // (BangBang's `on_behalf_of` / X-Service-Key path uses BB's internal id,
        //  not our external_id, so it's simpler to go through the JWT.)
        unset($payload['on_behalf_of']);
        return $this->userRequest($user)->post('/rooms', $payload)->throw()->json();
    }

    /** @return array<int,array<string,mixed>> */
    public function messages(User $user, int $roomId, array $query = []): array
    {
        $req = $this->requestForRoom($user, $roomId);
        $data = $req->get('/messages', array_merge(['room_id' => $roomId, 'limit' => 50], $query))
            ->throw()
            ->json();
        return $data['messages'] ?? $data['items'] ?? (is_array($data) ? $data : []);
    }

    /**
     * Send a message. Uses the cached guest bearer for a bot-enabled support
     * room; otherwise mints a normal user JWT. Idempotent via client_msg_id.
     */
    public function sendMessage(User $user, array $payload): array
    {
        unset($payload['sender_external_id'], $payload['sender_id']);
        $req = $this->requestForRoom($user, (int) ($payload['room_id'] ?? 0));
        return $req->post('/messages', $payload)->throw()->json();
    }

    /**
     * Close (archive) a support room so a fresh session is created next time.
     */
    public function closeRoom(User $user, int $roomId): array
    {
        $req = $this->requestForRoom($user, $roomId);
        try {
            return $req->delete('/rooms/' . $roomId)->throw()->json();
        } finally {
            // Whether the archive succeeded or not, drop the local guest
            // session so a new /support/guest is created next time.
            $this->forgetGuestSession($user);
        }
    }

    /**
     * Pick the right bearer for a call against a given room:
     * - guest bearer if we have a cached guest session for this room (bot flow)
     * - otherwise the DRS user's minted service-token JWT
     */
    private function requestForRoom(User $user, int $roomId): PendingRequest
    {
        $g = $this->guestSession($user);
        if ($g && $roomId && (int) ($g['room_id'] ?? 0) === $roomId) {
            return Http::baseUrl(config('chat.api_url'))
                ->timeout(config('chat.http_timeout'))
                ->acceptJson()
                ->asJson()
                ->withToken($g['access_token']);
        }
        return $this->userRequest($user);
    }

    public function presence(User $user): array
    {
        return $this->userRequest($user)->get('/presence')->throw()->json();
    }

    /**
     * List chat users the current DRS user can talk to (BangBang /users).
     * @return array<int,array<string,mixed>>
     */
    public function users(User $user, array $query = []): array
    {
        $data = $this->userRequest($user)->get('/users', array_merge(['limit' => 50], $query))->throw()->json();
        return $data['users'] ?? $data['items'] ?? (is_array($data) ? $data : []);
    }

    /**
     * Open (or create) a 1-on-1 room between the DRS user and a chat user.
     * Accepts either the BangBang internal user id (int) or a numeric string.
     */
    public function openDirect(User $user, int|string $targetId): array
    {
        return $this->createRoom($user, [
            'type' => 'direct',
            'member_ids' => [(int) $targetId],
        ]);
    }

    public function notifications(User $user, array $query = []): array
    {
        return $this->userRequest($user)->get('/notifications', $query)->throw()->json();
    }

    public function unreadCount(User $user): int
    {
        $data = $this->userRequest($user)->get('/notifications/unread-count')->throw()->json();
        return (int) ($data['count'] ?? $data['unread'] ?? 0);
    }

    public function health(): array
    {
        return Http::baseUrl(config('chat.api_url'))
            ->timeout(config('chat.http_timeout'))
            ->get('/health')
            ->throw()
            ->json();
    }

    public function forgetToken(User $user): void
    {
        Cache::forget($this->tokenCacheKey($user));
    }

    private function requestServiceToken(User $user): array
    {
        try {
            $resp = $this->serviceRequest()->post('/auth/service-token', [
                'external_id' => (string) $user->id,
                'username' => $user->email,
                'display_name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'auto_create' => true,
            ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('chat_unreachable: ' . $e->getMessage(), 502, $e);
        }

        if (!$resp->successful()) {
            $body = $resp->json();
            $err = $body['error'] ?? 'chat_error';
            Log::warning('BangBang service-token failed', [
                'status' => $resp->status(),
                'error' => $err,
                'request_id' => $body['request_id'] ?? null,
            ]);
            throw new RuntimeException($err . ':' . $resp->status(), $resp->status());
        }

        return $resp->json();
    }

    private function serviceRequest(): PendingRequest
    {
        return Http::baseUrl(config('chat.api_url'))
            ->timeout(config('chat.http_timeout'))
            ->acceptJson()
            ->asJson()
            ->withHeaders(['X-Service-Key' => (string) config('chat.service_key')]);
    }

    private function userRequest(User $user): PendingRequest
    {
        $token = $this->mintUserToken($user);
        return Http::baseUrl(config('chat.api_url'))
            ->timeout(config('chat.http_timeout'))
            ->acceptJson()
            ->asJson()
            ->withToken($token['access_token']);
    }

    private function assertConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('chat_not_configured');
        }
    }

    private function tokenCacheKey(User $user): string
    {
        return 'chat:token:user:' . $user->id;
    }
}
