<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;

        $userTasks = Task::where('user_id', $userId)->orWhere('assigned_to', $userId);

        $taskStats = [
            'total' => (clone $userTasks)->count(),
            'pending' => (clone $userTasks)->where('status', 'pending')->count(),
            'in_progress' => (clone $userTasks)->where('status', 'in_progress')->count(),
            'completed' => (clone $userTasks)->where('status', 'completed')->count(),
        ];

        $recentTasks = Task::with(['assignee', 'user'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assigned_to', $userId);
            })
            ->latest()
            ->take(5)
            ->get();

        if ($user->isAdmin()) {
            $recentReports = DailyReport::with(['task', 'user'])->latest('report_date')->take(5)->get();
        } else {
            $recentReports = $user->dailyReports()->with('task')->latest('report_date')->take(5)->get();
        }
        $todayReport = $user->dailyReports()->where('report_date', today())->first();

        return view('dashboard', compact('taskStats', 'recentTasks', 'recentReports', 'todayReport'));
    }
}
