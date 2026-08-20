<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Daily SerpApi rank snapshot: where one tracked property URL ranked in Google's
 * organic results for one keyword on one date. position is null when the URL was
 * not found within the tracked horizon (config seo.num, default top 100).
 */
class SerpRank extends Model
{
    protected $table = 'serp_ranks';

    protected $fillable = [
        'date', 'keyword', 'url', 'matched_url', 'dim_hash', 'position',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'position' => 'integer',
        ];
    }

    /**
     * SHA-256 of the dimension tuple. \x1f (unit separator) avoids collisions
     * between values that contain the delimiter. Single source of truth for the
     * unique key — used by the sync service and the demo seeder.
     */
    public static function dimHash(string $date, string $keyword, string $url): string
    {
        return hash('sha256', implode("\x1f", [$date, $keyword, $url]));
    }
}
