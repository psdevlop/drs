<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SerpApi keyword rank-tracking tables. Replaces the earlier Google Search
 * Console tables (gsc_*), which are dropped here for any local DB that already
 * ran that (never-deployed) migration.
 *
 * One search per keyword per day records where each tracked property URL appears
 * in Google's organic results. Charts accrue one snapshot per day — SerpApi has
 * no historical backfill.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Replaced GSC tables — drop if a local DB ran the old migration.
        Schema::dropIfExists('gsc_sync_log');
        Schema::dropIfExists('gsc_detail');
        Schema::dropIfExists('gsc_totals');

        Schema::create('serp_ranks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->string('keyword', 512);
            $table->string('url', 2048);                       // tracked property URL
            $table->string('matched_url', 2048)->nullable();   // actual organic result that matched
            // SHA-256 of date|keyword|url — keyword+url exceed the InnoDB index
            // byte limit, so the unique key is on (date, dim_hash).
            $table->char('dim_hash', 64);
            $table->unsignedSmallInteger('position')->nullable(); // organic rank 1..num; null = not ranked
            $table->timestamps();
            $table->unique(['date', 'dim_hash'], 'uniq_date_dim');
            $table->index('date', 'idx_date');
        });

        // Prefix index for the long keyword column (Blueprint can't express it;
        // only MySQL/MariaDB needs/supports the prefix length).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE serp_ranks ADD INDEX idx_keyword (keyword(191))');
        }

        Schema::create('serp_sync_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('ran_at')->useCurrent();
            $table->string('kind', 16); // daily
            $table->date('snapshot_date')->nullable();
            $table->unsignedSmallInteger('keywords')->default(0);
            $table->unsignedInteger('rows')->default(0);
            $table->unsignedSmallInteger('api_calls')->default(0);
            $table->text('error')->nullable();
            $table->index('ran_at', 'idx_ran_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serp_sync_log');
        Schema::dropIfExists('serp_ranks');
    }
};
