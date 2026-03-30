<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $holidayDays = Setting::getHolidayDays();
        return view('admin.settings', compact('holidayDays'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'holiday_days' => ['nullable', 'array'],
            'holiday_days.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
        ]);

        $days = $validated['holiday_days'] ?? [];
        Setting::set('holiday_days', json_encode($days));

        return redirect()->route('admin.settings')->with('success', __('messages.settings_updated'));
    }
}
