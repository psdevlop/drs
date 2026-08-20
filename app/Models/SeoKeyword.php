<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * A tracked keyword that lives outside config — typically imported from an
 * allin112 campaign. The set of keywords seo:sync actually tracks is the union
 * of these (active ones) and config('seo.keywords').
 */
class SeoKeyword extends Model
{
    protected $table = 'seo_keywords';

    protected $fillable = [
        'keyword', 'target_url', 'source', 'external_id', 'is_active', 'keyword_hash',
    ];

    protected function casts(): array
    {
        return [
            'external_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** SHA-256 of the trimmed keyword — the unique key (keyword is too long to index raw). */
    public static function keywordHash(string $keyword): string
    {
        return hash('sha256', trim($keyword));
    }

    /**
     * The keywords seo:sync should track: config keywords ∪ active DB keywords,
     * trimmed and de-duplicated. Safe before the table exists (returns config only).
     */
    public static function tracked(): Collection
    {
        $config = collect(config('seo.keywords', []))
            ->map(fn ($k) => trim((string) $k))
            ->filter();

        $db = Schema::hasTable('seo_keywords')
            ? static::query()->where('is_active', true)->pluck('keyword')->map(fn ($k) => trim($k))->filter()
            : collect();

        return $config->merge($db)->unique()->values();
    }
}
