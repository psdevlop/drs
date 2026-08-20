<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Fetches a campaign from the allin112 rank service (rank.php?pw=…&cid=…&run=1).
 *
 * allin112 sits behind Cloudflare's managed challenge, which 403s server-side
 * requests. To get through, paste a fresh `cf_clearance` cookie (from a browser
 * that solved the challenge) into ALLIN112_COOKIE — it is sent with the request.
 */
class Allin112Client
{
    public function __construct(
        private string $baseUrl,
        private string $pw,
        private ?string $cookie = null,
        private int $timeoutSeconds = 40,
    ) {}

    /** Raw response body for a campaign. Throws on a Cloudflare challenge / error. */
    public function campaignRaw(int $cid, bool $run = true): string
    {
        $request = Http::timeout($this->timeoutSeconds)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/json,application/xhtml+xml,*/*',
            'Accept-Language' => 'ko-KR,ko;q=0.9,en;q=0.8',
        ]);

        if ($this->cookie) {
            $request = $request->withHeaders(['Cookie' => $this->cookie]);
        }

        $query = ['cid' => $cid];
        if ($this->pw !== '') {
            $query['pw'] = $this->pw; // legacy /SEO/rank.php auth; admin path uses a session cookie
        }
        if ($run) {
            $query['run'] = 1;
        }

        $response = $request->get($this->baseUrl, $query);
        $body = $response->body();

        if ($this->looksLikeChallenge($response->status(), $body)) {
            throw new RuntimeException(
                'allin112 returned a Cloudflare challenge (HTTP '.$response->status().'). '
                .'Set a fresh cf_clearance cookie in ALLIN112_COOKIE, or run from an allowlisted IP.'
            );
        }

        if ($response->failed()) {
            throw new RuntimeException("allin112 request failed (HTTP {$response->status()}).");
        }

        return $body;
    }

    private function looksLikeChallenge(int $status, string $body): bool
    {
        if ($status === 403 || $status === 503) {
            return true;
        }

        return str_contains($body, 'Just a moment')
            || str_contains($body, 'challenge-platform')
            || str_contains($body, '_cf_chl_opt');
    }
}
