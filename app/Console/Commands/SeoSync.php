<?php

namespace App\Console\Commands;

use App\Models\SeoKeyword;
use App\Models\SerpSyncLog;
use App\Services\Seo\RankSyncService;
use App\Services\Seo\SerpApiClient;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * Snapshot today's Google organic rankings for every configured keyword via
 * SerpApi, recording where each tracked property URL ranks. Charts in the SEO
 * dashboard accrue one snapshot per day — SerpApi has no historical backfill.
 *
 *   php artisan seo:sync                  # snapshot today (Asia/Seoul)
 *   php artisan seo:sync --date=2026-06-17  # re-run / fill a specific day
 *
 * Cost: one SerpApi search per keyword per run.
 */
class SeoSync extends Command
{
    protected $signature = 'seo:sync {--date= : Snapshot date (Y-m-d, Asia/Seoul); defaults to today}';

    protected $description = 'Snapshot Google rankings for tracked keywords via SerpApi';

    private const TZ = 'Asia/Seoul';

    public function handle(): int
    {
        $apiKey = (string) config('seo.serpapi_key');

        if ($apiKey === '') {
            $this->error('Missing SerpApi key. Set SERPAPI_KEY in .env.');

            return self::FAILURE;
        }

        $date = $this->option('date') ?: CarbonImmutable::now(self::TZ)->toDateString();

        // config keywords ∪ active DB keywords (e.g. imported allin112 campaigns).
        $keywords = SeoKeyword::tracked()->all();
        $urls = collect(config('seo.links', []))->pluck('url')->filter()->unique()->values()->all();

        if ($keywords === []) {
            $this->warn('No keywords configured (config/seo.php → keywords). Nothing to sync.');

            return self::SUCCESS;
        }

        $client = new SerpApiClient(
            apiKey: $apiKey,
            locale: [
                'gl' => config('seo.gl'),
                'hl' => config('seo.hl'),
                'google_domain' => config('seo.google_domain'),
                'location' => config('seo.location'),
                'num' => (int) config('seo.num', 100),
            ],
            endpoint: (string) config('seo.serpapi_endpoint', 'https://serpapi.com/search'),
        );
        $service = new RankSyncService($client, (string) config('seo.match_mode', 'host'));

        $this->info("Snapshotting rankings for {$date} (".count($keywords).' keywords) ...');

        try {
            $result = $service->syncDay($date, $keywords, $urls);
        } catch (\Throwable $e) {
            SerpSyncLog::create([
                'kind' => 'daily',
                'snapshot_date' => $date,
                'keywords' => count($keywords),
                'rows' => 0,
                'api_calls' => 0,
                'error' => $e->getMessage(),
                'ran_at' => now(),
            ]);
            $this->error('Sync failed: '.$e->getMessage());

            return self::FAILURE;
        }

        SerpSyncLog::create([
            'kind' => 'daily',
            'snapshot_date' => $date,
            'keywords' => $result['keywords'],
            'rows' => $result['rows'],
            'api_calls' => $result['apiCalls'],
            'ran_at' => now(),
        ]);

        $this->info("Tracked: keywords={$result['keywords']} rows={$result['rows']} api_calls={$result['apiCalls']}");

        return self::SUCCESS;
    }
}
