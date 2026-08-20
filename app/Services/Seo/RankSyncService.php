<?php

namespace App\Services\Seo;

use App\Models\SerpRank;

/**
 * Orchestrates a one-day rank snapshot: for each keyword, one SerpApi search,
 * then record where each distinct property URL ranked. Writes are idempotent
 * upserts keyed on (date, dim_hash), so re-running the same day overwrites
 * instead of duplicating.
 */
class RankSyncService
{
    public function __construct(
        private SerpApiClient $client,
        private string $matchMode = 'host',
    ) {}

    /**
     * Pull rankings for [$keywords] against [$propertyUrls] on $date and upsert.
     *
     * @param  list<string>  $keywords
     * @param  list<string>  $propertyUrls
     * @return array{keywords:int,rows:int,apiCalls:int}
     */
    public function syncDay(string $date, array $keywords, array $propertyUrls): array
    {
        // Dedup property URLs (X / Twitter share a URL) so each ranks once.
        $urls = array_values(array_unique($propertyUrls));
        $hosts = [];
        foreach ($urls as $url) {
            $hosts[$url] = self::host($url);
        }

        $now = now();
        $rows = [];
        $apiCalls = 0;

        foreach ($keywords as $keyword) {
            $results = $this->client->organicResults($keyword);
            $apiCalls++;

            foreach ($urls as $url) {
                [$position, $matchedUrl] = $this->locate($url, $hosts[$url], $results);

                $rows[] = [
                    'date' => $date,
                    'keyword' => $keyword,
                    'url' => $url,
                    'matched_url' => $matchedUrl,
                    'dim_hash' => SerpRank::dimHash($date, $keyword, $url),
                    'position' => $position,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            // upsert bypasses model events, so dim_hash + timestamps are set above.
            SerpRank::upsert($rows, ['date', 'dim_hash'], ['matched_url', 'position', 'updated_at']);
        }

        return [
            'keywords' => count($keywords),
            'rows' => count($rows),
            'apiCalls' => $apiCalls,
        ];
    }

    /**
     * Find a property URL among organic results. Returns [position|null, matchedUrl|null].
     * Results are pre-ordered by rank, so the first match is the best position.
     *
     * @param  list<array<string,mixed>>  $results
     * @return array{0:?int,1:?string}
     */
    private function locate(string $url, string $host, array $results): array
    {
        foreach ($results as $i => $result) {
            $link = (string) ($result['link'] ?? '');
            if ($link === '') {
                continue;
            }

            $matches = $this->matchMode === 'exact'
                ? rtrim($link, '/') === rtrim($url, '/')
                : self::host($link) === $host;

            if ($matches) {
                // Prefer SerpApi's own position; fall back to 1-based index.
                $position = isset($result['position']) ? (int) $result['position'] : ($i + 1);

                return [$position, $link];
            }
        }

        return [null, null];
    }

    /** Normalize a URL to a comparable host: lowercase, no scheme, no leading www. */
    public static function host(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?: $url;
        $host = strtolower($host);

        return preg_replace('/^www\./', '', $host) ?? $host;
    }
}
