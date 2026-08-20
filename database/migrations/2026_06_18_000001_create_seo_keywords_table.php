<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracked keywords sourced from outside config (e.g. imported from an allin112
 * campaign). seo:sync tracks the union of these and config('seo.keywords').
 * Importing reconciles the set — adding new keywords and reactivating/updating
 * existing ones — instead of duplicating.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_keywords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('keyword', 512);
            $table->string('target_url', 2048)->nullable(); // campaign's intended landing URL
            $table->string('source', 32)->default('manual'); // manual | allin112 | ...
            $table->unsignedInteger('external_id')->nullable(); // campaign id (cid) it came from
            $table->boolean('is_active')->default(true);
            $table->char('keyword_hash', 64); // sha256(keyword) — unique key (keyword too long to index raw)
            $table->timestamps();
            $table->unique('keyword_hash', 'uniq_keyword');
            $table->index(['source', 'is_active'], 'idx_source_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_keywords');
    }
};
