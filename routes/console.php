<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-create new on-call rotation after previous one completes
Schedule::command('oncall:auto-rotate')->daily();

// Retrieve Search Console results and track performance (re-pulls trailing days)
Schedule::command('seo:sync')->dailyAt('09:15');
