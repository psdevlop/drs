<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $holidayDays = Setting::getHolidayDays();
        $holidayDates = Setting::getHolidayDates();
        return view('admin.settings', compact('holidayDays', 'holidayDates'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'holiday_days' => ['nullable', 'array'],
            'holiday_days.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'holiday_date' => ['nullable', 'array'],
            'holiday_date.*.date' => ['required', 'date_format:Y-m-d'],
            'holiday_date.*.reason' => ['nullable', 'string', 'max:255'],
        ]);

        $days = $validated['holiday_days'] ?? [];
        Setting::set('holiday_days', json_encode($days));

        $entries = [];
        $seen = [];
        foreach ($validated['holiday_date'] ?? [] as $entry) {
            $date = trim($entry['date']);
            if ($date && !in_array($date, $seen)) {
                $entries[] = ['date' => $date, 'reason' => trim($entry['reason'] ?? '')];
                $seen[] = $date;
            }
        }
        Setting::set('holiday_dates', json_encode($entries));

        return redirect()->route('admin.settings')->with('success', __('messages.settings_updated'));
    }
}
