<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin HTTP wrapper over the SerpApi Google Search endpoint. One call returns up
 * to `num` organic results for a keyword. Rate-limited responses (HTTP 429 or a
 * rate-limit error payload) are retried with exponential backoff; other SerpApi
 * errors are fatal. The sleeper is injectable so tests run instantly.
 */
class SerpApiClient
{
    /** @var callable(int):void */
    private $sleeper;

    /**
     * @param  array{gl?:string,hl?:string,google_domain?:string,location?:string,num?:int}  $locale
     */
    public function __construct(
        private string $apiKey,
        private array $locale,
        private string $endpoint = 'https://serpapi.com/search',
        private int $maxRetries = 6,
        private int $baseDelayMs = 500,
        private int $maxDelayMs = 60000,
        private int $timeoutSeconds = 30,
        ?callable $sleeper = null,
    ) {
        $this->sleeper = $sleeper ?? static function (int $ms): void {
            usleep($ms * 1000);
        };
    }

    /**
     * Organic results for a keyword, ordered by Google rank. Each item carries at
     * least `position` (int) and `link` (string); `title` when present.
     *
     * @return list<array<string,mixed>>
     */
    public function organicResults(string $keyword): array
    {
        $query = array_filter([
            'engine' => 'google',
            'q' => $keyword,
            'api_key' => $this->apiKey,
            'gl' => $this->locale['gl'] ?? null,
            'hl' => $this->locale['hl'] ?? null,
            'google_domain' => $this->locale['google_domain'] ?? null,
            'location' => $this->locale['location'] ?? null,
            'num' => $this->locale['num'] ?? 100,
            'no_cache' => 'true', // daily snapshots must be fresh, not SerpApi-cached
        ], static fn ($v) => $v !== null && $v !== '');

        $data = $this->getWithBackoff($query);

        $results = $data['organic_results'] ?? [];

        return is_array($results) ? array_values($results) : [];
    }

    /**
     * @param  array<string,mixed>  $query
     * @return array<string,mixed>
     */
    private function getWithBackoff(array $query): array
    {
        $attempt = 0;

        while (true) {
            try {
                return $this->get($query);
            } catch (RateLimitException $e) {
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                $delay = (int) min($this->baseDelayMs * (2 ** $attempt), $this->maxDelayMs);
                ($this->sleeper)($delay);
                $attempt++;
            }
        }
    }

    /**
     * @param  array<string,mixed>  $query
     * @return array<string,mixed>
     */
    private function get(array $query): array
    {
        $response = Http::timeout($this->timeoutSeconds)->get($this->endpoint, $query);

        if ($response->status() === 429) {
            throw new RateLimitException('SerpApi rate limit (HTTP 429)');
        }

        $body = $response->json();

        // SerpApi reports problems in a JSON `error` field even on 200.
        if (is_array($body) && isset($body['error'])) {
            $message = (string) $body['error'];
            if (stripos($message, 'rate') !== false && stripos($message, 'limit') !== false) {
                throw new RateLimitException("SerpApi: {$message}");
            }
            throw new RuntimeException("SerpApi error: {$message}");
        }

        if ($response->failed()) {
            throw new RuntimeException("SerpApi request failed (HTTP {$response->status()})");
        }

        return is_array($body) ? $body : [];
    }
}
