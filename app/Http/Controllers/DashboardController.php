<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\DailyReport;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;

        $userTasksQuery = function () use ($userId) {
            return Task::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assigned_to', $userId);
            });
        };

        $taskStats = [
            'total' => $userTasksQuery()->count(),
            'pending' => $userTasksQuery()->where('status', 'pending')->count(),
            'in_progress' => $userTasksQuery()->where('status', 'in_progress')->count(),
            'completed' => $userTasksQuery()->where('status', 'completed')->count(),
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

        $latestAnnouncements = Announcement::with('user')
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(3)
            ->get();

        return view('dashboard', compact('taskStats', 'recentTasks', 'recentReports', 'todayReport', 'latestAnnouncements'));
    }
}
