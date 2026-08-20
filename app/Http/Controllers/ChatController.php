<?php

namespace App\Http\Controllers;

use App\Services\ChatClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ChatController extends Controller
{
    public function __construct(private ChatClient $chat)
    {
    }

    public function config(): JsonResponse
    {
        if (!$this->chat->isConfigured()) {
            return response()->json([
                'configured' => false,
                'error' => 'chat_not_configured',
                'message' => 'Set CHAT_SERVICE_KEY in .env to enable chat.',
            ]);
        }

        try {
            $token = $this->chat->mintUserToken(auth()->user());
        } catch (RuntimeException $e) {
            return $this->error($e);
        }

        return response()->json([
            'configured' => true,
            'ws_url' => config('chat.ws_url'),
            'access_token' => $token['access_token'],
            'expires_in' => $token['expires_in'] ?? null,
            'user' => $token['user'] ?? null,
        ]);
    }

    public function rooms(): JsonResponse
    {
        return $this->guard(fn () => response()->json([
            'rooms' => $this->chat->rooms(auth()->user()),
        ]));
    }

    public function messages(Request $request, int $roomId): JsonResponse
    {
        return $this->guard(fn () => response()->json([
            'messages' => $this->chat->messages(auth()->user(), $roomId, [
                'limit' => (int) $request->query('limit', 50),
                'before_id' => $request->query('before_id'),
                'after_id' => $request->query('after_id'),
            ]),
        ]));
    }

    public function send(Request $request, int $roomId): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:4000'],
            'client_msg_id' => ['required', 'string', 'max:64'],
            'reply_to_id' => ['nullable', 'integer'],
        ]);

        return $this->guard(fn () => response()->json(
            $this->chat->sendMessage(auth()->user(), [
                'room_id' => $roomId,
                'content' => $validated['content'],
                'client_msg_id' => $validated['client_msg_id'],
                'reply_to_id' => $validated['reply_to_id'] ?? null,
            ])
        ));
    }

    /** The "Real Time Chat Support" button: the assistant, not the queue. */
    public function startSupport(): JsonResponse
    {
        return $this->guard(fn () => response()->json(
            $this->chat->startSupport(auth()->user())
        ));
    }

    /**
     * Initialise a bot-enabled support session with the user's first message.
     * Routes to BangBang's /support/guest (only path that engages the
     * automated assistant) but attributes the ticket with DRS user info.
     */
    public function supportInit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);
        return $this->guard(fn () => response()->json(
            $this->chat->initGuestSupport(auth()->user(), $validated['message'])
        ));
    }

    /** Close (archive) a support room after the feedback flow. */
    public function closeRoom(int $roomId): JsonResponse
    {
        return $this->guard(fn () => response()->json(
            $this->chat->closeRoom(auth()->user(), $roomId)
        ));
    }

    public function createRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', 'in:direct,group,support,announcement'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer'],
            'member_external_ids' => ['nullable', 'array'],
            'member_external_ids.*' => ['string'],
        ]);

        return $this->guard(fn () => response()->json(
            $this->chat->createRoom(auth()->user(), $validated)
        ));
    }

    public function unread(): JsonResponse
    {
        return $this->guard(fn () => response()->json([
            'unread' => $this->chat->unreadCount(auth()->user()),
        ]));
    }

    public function users(Request $request): JsonResponse
    {
        return $this->guard(fn () => response()->json([
            'users' => $this->chat->users(auth()->user(), array_filter([
                'search' => $request->query('q'),
                'online_only' => $request->boolean('online_only') ? true : null,
                'limit' => (int) $request->query('limit', 50),
            ], fn ($v) => $v !== null && $v !== '')),
        ]));
    }

    public function direct(string $userId): JsonResponse
    {
        return $this->guard(fn () => response()->json(
            $this->chat->openDirect(auth()->user(), $userId)
        ));
    }

    private function guard(\Closure $fn): JsonResponse
    {
        if (!$this->chat->isConfigured()) {
            return response()->json([
                'error' => 'chat_not_configured',
                'message' => 'Chat backend is not configured.',
            ], 503);
        }
        try {
            return $fn();
        } catch (RuntimeException $e) {
            return $this->error($e);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'chat_error', 'message' => $e->getMessage()], 502);
        }
    }

    private function error(RuntimeException $e): JsonResponse
    {
        $status = 502;
        $code = $e->getMessage();
        if (str_starts_with($code, 'chat_unreachable')) $status = 502;
        elseif ($code === 'chat_not_configured') $status = 503;
        elseif ($e->getCode() >= 400 && $e->getCode() < 600) $status = $e->getCode();
        return response()->json([
            'error' => explode(':', $code, 2)[0],
            'message' => $code,
        ], $status);
    }
}
