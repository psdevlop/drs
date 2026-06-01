<?php

use App\Support\NepaliGovernmentHolidays;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('settings')->where('key', 'holiday_dates')->value('value');

        if ($existing !== null && trim((string) $existing) !== '' && trim((string) $existing) !== '[]') {
            return;
        }

        DB::table('settings')->updateOrInsert(
            ['key' => 'holiday_dates'],
            [
                'value' => NepaliGovernmentHolidays::datesJson(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'holiday_dates')
            ->where('value', NepaliGovernmentHolidays::datesJson())
            ->delete();
    }
};
