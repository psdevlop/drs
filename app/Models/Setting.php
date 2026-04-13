<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getHolidayDays(): array
    {
        $value = static::get('holiday_days');
        return $value ? json_decode($value, true) : ['sunday'];
    }

    public static function getHolidayDates(): array
    {
        $value = static::get('holiday_dates');
        if (!$value) {
            return [];
        }
        $decoded = json_decode($value, true);
        if (!$decoded) {
            return [];
        }
        // Normalize: support both old format ["2026-04-14"] and new format [{"date":"2026-04-14","reason":"..."}]
        $normalized = [];
        foreach ($decoded as $entry) {
            if (is_string($entry)) {
                $normalized[] = ['date' => $entry, 'reason' => ''];
            } else {
                $normalized[] = $entry;
            }
        }
        return $normalized;
    }

    public static function getHolidayDatesList(): array
    {
        return array_column(static::getHolidayDates(), 'date');
    }

    public static function getHolidayReason(string $date): ?string
    {
        foreach (static::getHolidayDates() as $entry) {
            if ($entry['date'] === $date) {
                return $entry['reason'] ?: null;
            }
        }
        return null;
    }
}
