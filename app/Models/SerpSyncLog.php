<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bookkeeping for each seo:sync run — useful for monitoring cron health and API
 * spend (api_calls). Has no updated_at, so model timestamps are disabled.
 */
class SerpSyncLog extends Model
{
    protected $table = 'serp_sync_log';

    public $timestamps = false;

    protected $fillable = [
        'kind', 'snapshot_date', 'keywords', 'rows', 'api_calls', 'error', 'ran_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'ran_at' => 'datetime',
        ];
    }
}
