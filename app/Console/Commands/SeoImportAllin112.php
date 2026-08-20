<?php

namespace App\Console\Commands;

use App\Models\SeoKeyword;
use App\Services\Seo\Allin112CampaignParser;
use App\Services\Seo\Allin112Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Import an allin112 campaign's keywords (+ target URLs) into seo_keywords so the
 * SerpApi sync (seo:sync) tracks them. Re-running reconciles the set: new
 * keywords are added, existing ones updated/reactivated — no duplicates.
 *
 *   php artisan seo:import-allin112                 # cid from config
 *   php artisan seo:import-allin112 --cid=3
 *   php artisan seo:import-allin112 --raw           # dump the raw response, don't import
 */
class SeoImportAllin112 extends Command
{
    protected $signature = 'seo:import-allin112 {--cid= : Campaign id (defaults to config seo.allin112.cid)} {--run : Append run=1 to trigger a fresh rank run} {--raw : Dump the raw response and exit}';

    protected $description = 'Import an allin112 campaign\'s keywords into the SerpApi tracker';

    public function handle(): int
    {
        $cid = (int) ($this->option('cid') ?: config('seo.allin112.cid', 3));

        $client = new Allin112Client(
            baseUrl: (string) config('seo.allin112.base_url'),
            pw: (string) config('seo.allin112.pw'),
            cookie: config('seo.allin112.cookie') ?: null,
        );

        try {
            $body = $client->campaignRaw($cid, (bool) $this->option('run'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('raw')) {
            $path = "seo/allin112-cid{$cid}.txt";
            Storage::put($path, $body);
            $this->info('Raw response saved to storage/app/'.$path.' ('.strlen($body).' bytes).');
            $this->line(mb_substr($body, 0, 1500));

            return self::SUCCESS;
        }

        $rows = Allin112CampaignParser::parse($body);

        if ($rows === []) {
            $this->warn('No keywords parsed from the campaign. Re-run with --raw to inspect the response, then tighten the parser.');

            return self::FAILURE;
        }

        $added = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $keyword = trim($row['keyword']);
            if ($keyword === '') {
                continue;
            }

            $existing = SeoKeyword::where('keyword_hash', SeoKeyword::keywordHash($keyword))->first();

            SeoKeyword::updateOrCreate(
                ['keyword_hash' => SeoKeyword::keywordHash($keyword)],
                [
                    'keyword' => $keyword,
                    'target_url' => $row['target_url'],
                    'source' => 'allin112',
                    'external_id' => $cid,
                    'is_active' => true,
                ],
            );

            $existing ? $updated++ : $added++;
        }

        $this->info("Imported allin112 campaign cid={$cid}: added={$added} updated={$updated} total_tracked=".SeoKeyword::where('is_active', true)->count());
        $this->line('Run php artisan seo:sync to snapshot rankings for the imported keywords.');

        return self::SUCCESS;
    }
}
