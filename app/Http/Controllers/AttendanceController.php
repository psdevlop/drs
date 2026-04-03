<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();
        $todayAttendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        $query = Attendance::with('user');

        if ($user->isAdmin()) {
            // Admin sees all attendance
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date', substr($request->month, 5, 2))
                  ->whereYear('date', substr($request->month, 0, 4));
        } else {
            // Default: current month
            $query->whereMonth('date', now()->month)
                  ->whereYear('date', now()->year);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(31);

        $users = $user->isAdmin() ? User::orderBy('name')->get() : collect();

        // Monthly summary
        $summaryQuery = Attendance::where('user_id', $user->id)->whereNotNull('total_hours');
        if ($request->filled('month')) {
            $summaryQuery->whereMonth('date', substr($request->month, 5, 2))
                         ->whereYear('date', substr($request->month, 0, 4));
        } else {
            $summaryQuery->whereMonth('date', now()->month)->whereYear('date', now()->year);
        }
        $monthlySummary = [
            'total_days' => $summaryQuery->count(),
            'total_hours' => $summaryQuery->sum('total_hours'),
            'avg_hours' => $summaryQuery->count() > 0 ? round($summaryQuery->sum('total_hours') / $summaryQuery->count(), 2) : 0,
        ];

        return view('attendance.index', compact('todayAttendance', 'attendances', 'users', 'monthlySummary'));
    }

    public function checkIn(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance && $attendance->check_in) {
            return redirect()->back()->with('error', __('messages.already_checked_in'));
        }

        Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['check_in' => now()]
        );

        return redirect()->back()->with('success', __('messages.checked_in_success'));
    }

    public function checkOut(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if (!$attendance || !$attendance->check_in) {
            return redirect()->back()->with('error', __('messages.not_checked_in'));
        }

        if ($attendance->check_out) {
            return redirect()->back()->with('error', __('messages.already_checked_out'));
        }

        $attendance->update([
            'check_out' => now(),
            'total_hours' => $attendance->calculateHours() ?? round($attendance->check_in->diffInMinutes(now()) / 60, 2),
        ]);

        // Recalculate after update
        $attendance->refresh();
        $attendance->update(['total_hours' => $attendance->calculateHours()]);

        return redirect()->back()->with('success', __('messages.checked_out_success'));
    }
}
