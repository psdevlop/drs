<?php

namespace Database\Seeders;

use App\Models\SerpRank;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DEMO DATA ONLY. Generates ~31 days of plausible keyword rank history so the
 * SEO dashboard renders before the real SerpApi sync has run. One row per
 * keyword × distinct property URL × day: the website ranks strongly and trends
 * upward; socials rank weaker with occasional gaps (not ranked).
 *
 * Wipe with:
 *   php artisan tinker --execute="DB::table('serp_ranks')->truncate();"
 */
class SerpRankSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('serp_ranks')->truncate();

        $tz = 'Asia/Seoul';
        $end = CarbonImmutable::now($tz)->subDay();

        $keywords = array_values(array_filter(array_map('trim', config('seo.keywords', []))));
        $links = collect(config('seo.links', []));
        $urls = $links->pluck('url')->filter()->unique()->values()->all();
        $websiteUrl = ($links->firstWhere('key', 'website')['url'] ?? $links->value('url'));

        // Seed a few days beyond the chart window so a full month is always filled.
        $days = max(31, (int) config('seo.window_days', 31)) + 4;

        foreach ($keywords as $kIdx => $keyword) {
            foreach ($urls as $uIdx => $url) {
                $isWebsite = $url === $websiteUrl;

                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = $end->subDays($i)->format('Y-m-d');

                    if ($isWebsite) {
                        // Strong, slowly-improving rank ~1..8.
                        $base = 6 - ($days - 1 - $i) * 0.05;       // trends toward 1
                        $position = (int) max(1, round($base + sin(($i + $kIdx) / 5.0) * 2));
                        $matched = $url;
                    } else {
                        // Weaker socials ~15..60, with gaps (not ranked) some days.
                        $unranked = (($i + $uIdx + $kIdx) % 4) === 0;
                        if ($unranked) {
                            $position = null;
                            $matched = null;
                        } else {
                            $position = (int) max(11, round(30 + $uIdx * 6 + sin(($i + $uIdx) / 4.0) * 10));
                            $matched = $url;
                        }
                    }

                    SerpRank::create([
                        'date' => $date,
                        'keyword' => $keyword,
                        'url' => $url,
                        'matched_url' => $matched,
                        'dim_hash' => SerpRank::dimHash($date, $keyword, $url),
                        'position' => $position,
                    ]);
                }
            }
        }

        $this->command?->info(sprintf(
            'Seeded %d days of demo rank history for %d keywords × %d properties.',
            $days,
            count($keywords),
            count($urls),
        ));
    }
}
