<?php

namespace Tests\Unit;

use App\Support\NepaliGovernmentHolidays;
use PHPUnit\Framework\TestCase;

class NepaliGovernmentHolidaysTest extends TestCase
{
    public function test_it_lists_unique_2083_bs_government_holiday_dates(): void
    {
        $holidays = NepaliGovernmentHolidays::dates();
        $dates = array_column($holidays, 'date');

        $this->assertSame(2083, NepaliGovernmentHolidays::YEAR_BS);
        $this->assertCount(86, $holidays);
        $this->assertSame($dates, array_values(array_unique($dates)));
        $this->assertSame('2026-04-14', $holidays[0]['date']);
        $this->assertSame('2027-04-11', $holidays[count($holidays) - 1]['date']);
    }

    public function test_it_combines_source_entries_that_share_one_english_date(): void
    {
        $holidays = collect(NepaliGovernmentHolidays::dates())->keyBy('date');

        $this->assertSame(
            'Buddha Jayanti; International Labour Day; Ubhauli Parba',
            $holidays['2026-05-01']['reason']
        );
        $this->assertSame(
            'Public Holiday; Dashain Holiday',
            $holidays['2026-10-18']['reason']
        );
        $this->assertSame(
            'Pahadma Holi; Public Holiday',
            $holidays['2027-03-21']['reason']
        );
    }

    public function test_schedule_holidays_hide_generic_public_holidays(): void
    {
        $holidays = collect(NepaliGovernmentHolidays::forSchedule(NepaliGovernmentHolidays::dates()))->keyBy('date');

        $this->assertFalse($holidays->has('2026-04-19'));
        $this->assertFalse($holidays->has('2026-04-26'));
        $this->assertSame('Dashain Holiday', $holidays['2026-10-18']['reason']);
        $this->assertSame('Pahadma Holi', $holidays['2027-03-21']['reason']);
    }
}
