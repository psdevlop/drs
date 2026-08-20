<?php

namespace App\Services\Seo;

/**
 * Extracts keyword + target-URL pairs from an allin112 campaign response.
 *
 * PROVISIONAL: the exact response shape isn't visible from here (Cloudflare
 * blocks server fetches), so this parses the common shapes defensively —
 * JSON first, then an HTML table, then delimited text. Once a real response is
 * captured (seo:import-allin112 --raw), tighten this to the actual format.
 */
class Allin112CampaignParser
{
    /**
     * @return list<array{keyword:string,target_url:?string,rank:?int}>
     */
    public static function parse(string $body): array
    {
        $body = trim($body);
        if ($body === '') {
            return [];
        }

        $first = $body[0];
        if ($first === '{' || $first === '[') {
            $rows = self::fromJson($body);
            if ($rows !== []) {
                return $rows;
            }
        }

        if (str_contains($body, '<table') || str_contains($body, '<tr')) {
            $rows = self::fromHtmlTable($body);
            if ($rows !== []) {
                return $rows;
            }
        }

        return self::fromDelimitedText($body);
    }

    /** @return list<array{keyword:string,target_url:?string,rank:?int}> */
    private static function fromJson(string $body): array
    {
        $data = json_decode($body, true);
        if (! is_array($data)) {
            return [];
        }

        // Unwrap a common wrapper like {data:[...]} / {rows:[...]} / {keywords:[...]}.
        foreach (['data', 'rows', 'keywords', 'results', 'items'] as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                $data = $data[$key];
                break;
            }
        }

        $rows = [];
        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }
            $keyword = self::pick($item, ['keyword', 'kw', 'query', 'term', 'word']);
            if ($keyword === null) {
                continue;
            }
            $rows[] = [
                'keyword' => $keyword,
                'target_url' => self::pick($item, ['target_url', 'target', 'url', 'link', 'page']),
                'rank' => self::pickInt($item, ['rank', 'position', 'pos']),
            ];
        }

        return $rows;
    }

    /** @return list<array{keyword:string,target_url:?string,rank:?int}> */
    private static function fromHtmlTable(string $body): array
    {
        $rows = [];
        $prev = libxml_use_internal_errors(true);
        $doc = new \DOMDocument;
        $doc->loadHTML('<?xml encoding="utf-8"?>'.$body);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        foreach ($doc->getElementsByTagName('tr') as $tr) {
            $cells = [];
            foreach ($tr->getElementsByTagName('td') as $td) {
                $cells[] = trim($td->textContent);
            }
            if ($cells === []) {
                continue; // header row (th) or empty
            }

            // Heuristic: first non-numeric cell is the keyword; first http* cell is the URL.
            $keyword = null;
            $url = null;
            $rank = null;
            foreach ($cells as $cell) {
                if ($url === null && str_starts_with($cell, 'http')) {
                    $url = $cell;
                } elseif ($keyword === null && ! is_numeric($cell) && ! str_starts_with($cell, 'http')) {
                    $keyword = $cell;
                } elseif ($rank === null && is_numeric($cell)) {
                    $rank = (int) $cell;
                }
            }

            if ($keyword !== null && $keyword !== '') {
                $rows[] = ['keyword' => $keyword, 'target_url' => $url, 'rank' => $rank];
            }
        }

        return $rows;
    }

    /** @return list<array{keyword:string,target_url:?string,rank:?int}> */
    private static function fromDelimitedText(string $body): array
    {
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '<')) {
                continue;
            }
            // Split on tab, pipe, or comma.
            $parts = array_map('trim', preg_split('/\t|\s*\|\s*|\s*,\s*/', $line));
            $keyword = $parts[0] ?? '';
            if ($keyword === '' || is_numeric($keyword)) {
                continue;
            }
            $url = null;
            $rank = null;
            foreach (array_slice($parts, 1) as $p) {
                if ($url === null && str_starts_with($p, 'http')) {
                    $url = $p;
                } elseif ($rank === null && is_numeric($p)) {
                    $rank = (int) $p;
                }
            }
            $rows[] = ['keyword' => $keyword, 'target_url' => $url, 'rank' => $rank];
        }

        return $rows;
    }

    /** @param array<string,mixed> $item @param list<string> $keys */
    private static function pick(array $item, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($item[$key]) && is_scalar($item[$key]) && trim((string) $item[$key]) !== '') {
                return trim((string) $item[$key]);
            }
        }

        return null;
    }

    /** @param array<string,mixed> $item @param list<string> $keys */
    private static function pickInt(array $item, array $keys): ?int
    {
        $value = self::pick($item, $keys);

        return $value !== null && is_numeric($value) ? (int) $value : null;
    }
}
