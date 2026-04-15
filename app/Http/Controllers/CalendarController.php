<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use Carbon\Carbon;
use App\Models\OnCall;
use App\Models\Setting;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index');
    }

    public function schedule()
    {
        return view('calendar.schedule');
    }

    public function scheduleData(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $events = [];

        // Tasks
        $taskQuery = Task::with(['assignees', 'user'])
            ->where(function ($q) {
                $q->whereNotNull('start_date')
                  ->orWhereNotNull('due_date')
                  ->orWhereNotNull('expected_end_date');
            });
        if (!$user->isAdmin()) {
            $taskQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('assignees', fn ($q2) => $q2->where('users.id', $userId));
            });
        }

        if ($request->filled('start') && $request->filled('end')) {
            $start = $request->start;
            $end = $request->end;
            $taskQuery->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('due_date', [$start, $end])
                  ->orWhereBetween('expected_end_date', [$start, $end]);
            });
        }

        $colors = [
            'pending' => ['bg' => '#f59e0b', 'border' => '#d97706'],
            'in_progress' => ['bg' => '#3b82f6', 'border' => '#2563eb'],
            'completed' => ['bg' => '#10b981', 'border' => '#059669'],
        ];

        foreach ($taskQuery->get() as $task) {
            $startDate = $task->start_date?->format('Y-m-d') ?? $task->created_at->format('Y-m-d');
            $endDate = $task->expected_end_date?->format('Y-m-d') ?? $task->due_date?->format('Y-m-d') ?? $startDate;
            $color = $colors[$task->status] ?? $colors['pending'];

            $events[] = [
                'id' => 'task-' . $task->id,
                'title' => $task->title,
                'start' => $startDate,
                'end' => date('Y-m-d', strtotime($endDate . ' +1 day')),
                'url' => route('tasks.show', $task),
                'backgroundColor' => $color['bg'],
                'borderColor' => $color['border'],
                'extendedProps' => [
                    'type' => 'task',
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'progress' => $task->progress,
                    'assignee' => $task->assignees->pluck('name')->join(', ') ?: '-',
                    'start_date' => $task->start_date?->format('M d, Y') ?? '-',
                    'end_date' => $task->expected_end_date?->format('M d, Y') ?? $task->due_date?->format('M d, Y') ?? '-',
                ],
            ];
        }

        // Reports
        $reportQuery = DailyReport::with(['user', 'task']);
        if (!$user->isAdmin()) {
            $reportQuery->where('user_id', $userId);
        }
        if ($request->filled('start') && $request->filled('end')) {
            $reportQuery->whereBetween('report_date', [$request->start, $request->end]);
        }

        foreach ($reportQuery->get() as $report) {
            $title = $user->isAdmin() && $report->user_id !== $userId
                ? $report->user->name . ': ' . Str::limit($report->summary, 30)
                : Str::limit($report->summary, 40);

            $events[] = [
                'id' => 'report-' . $report->id,
                'title' => '📝 ' . $title,
                'start' => $report->report_date->format('Y-m-d'),
                'url' => route('reports.show', $report),
                'backgroundColor' => '#8b5cf6',
                'borderColor' => '#7c3aed',
                'extendedProps' => [
                    'type' => 'report',
                    'report_date' => $report->report_date->format('M d, Y'),
                    'summary' => $report->summary,
                    'task' => $report->task?->title ?? '-',
                    'challenges' => $report->challenges ?? '-',
                ],
            ];
        }

        // On Call (from rotation schedules)
        $activeRotations = \App\Models\OnCallRotation::with('users')->where('is_active', true)->get();
        if ($activeRotations->isNotEmpty() && $request->filled('start') && $request->filled('end')) {
            $onCallStart = Carbon::parse($request->start);
            $onCallEnd = Carbon::parse($request->end);

            for ($date = $onCallStart->copy(); $date->lte($onCallEnd); $date->addDay()) {
                $dutyUsers = collect();
                $picName = null;

                foreach ($activeRotations as $rotation) {
                    $users = $rotation->getUsersForDate($date);
                    foreach ($users as $u) {
                        $dutyUsers->push($u);
                        if ($u->is_pic ?? false) {
                            $picName = $u->name;
                        }
                    }
                }

                // Check for manual PIC override
                $manualEntry = OnCall::with('pic')->where('date', $date->format('Y-m-d'))->first();
                if ($manualEntry && $manualEntry->pic) {
                    $picName = $manualEntry->pic->name;
                }

                if ($dutyUsers->isEmpty()) {
                    continue;
                }

                $userNames = $dutyUsers->pluck('name')->unique()->join(', ');
                $title = '📞 ' . __('messages.on_call') . ': ' . $userNames;
                if ($picName) {
                    $title .= ' (PIC: ' . $picName . ')';
                }

                $events[] = [
                    'id' => 'oncall-' . $date->format('Y-m-d'),
                    'title' => $title,
                    'start' => $date->format('Y-m-d'),
                    'url' => route('oncall.index'),
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#dc2626',
                    'extendedProps' => [
                        'type' => 'oncall',
                        'users' => $userNames,
                        'pic' => $picName ?? '-',
                        'date' => $date->format('M d, Y'),
                    ],
                ];
            }
        }

        // Holidays (specific dates with optional reason)
        foreach (Setting::getHolidayDates() as $holiday) {
            if (empty($holiday['date'])) {
                continue;
            }
            if ($request->filled('start') && $request->filled('end')) {
                if ($holiday['date'] < $request->start || $holiday['date'] > $request->end) {
                    continue;
                }
            }
            $reason = $holiday['reason'] ?? '';
            $events[] = [
                'id' => 'holiday-' . $holiday['date'],
                'title' => '🎉 ' . __('messages.holiday') . ($reason ? ': ' . $reason : ''),
                'start' => $holiday['date'],
                'allDay' => true,
                'backgroundColor' => '#ec4899',
                'borderColor' => '#db2777',
                'extendedProps' => [
                    'type' => 'holiday',
                    'date' => Carbon::parse($holiday['date'])->format('M d, Y'),
                    'reason' => $reason ?: '-',
                ],
            ];
        }

        return response()->json($events);
    }

    public function tasks(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;

        $query = Task::with(['assignees', 'user'])
            ->where(function ($q) {
                $q->whereNotNull('start_date')
                  ->orWhereNotNull('due_date')
                  ->orWhereNotNull('expected_end_date');
            });
        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('assignees', fn ($q2) => $q2->where('users.id', $userId));
            });
        }

        if ($request->filled('start') && $request->filled('end')) {
            $start = $request->start;
            $end = $request->end;
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('due_date', [$start, $end])
                  ->orWhereBetween('expected_end_date', [$start, $end]);
            });
        }

        $tasks = $query->get();

        $events = [];
        foreach ($tasks as $task) {
            $startDate = $task->start_date?->format('Y-m-d') ?? $task->created_at->format('Y-m-d');
            $endDate = $task->expected_end_date?->format('Y-m-d') ?? $task->due_date?->format('Y-m-d') ?? $startDate;

            $colors = [
                'pending' => ['bg' => '#f59e0b', 'border' => '#d97706'],
                'in_progress' => ['bg' => '#3b82f6', 'border' => '#2563eb'],
                'completed' => ['bg' => '#10b981', 'border' => '#059669'],
            ];

            $color = $colors[$task->status] ?? $colors['pending'];

            $events[] = [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $startDate,
                'end' => date('Y-m-d', strtotime($endDate . ' +1 day')),
                'url' => route('tasks.show', $task),
                'backgroundColor' => $color['bg'],
                'borderColor' => $color['border'],
                'extendedProps' => [
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'progress' => $task->progress,
                    'assignee' => $task->assignees->pluck('name')->join(', ') ?: '-',
                    'start_date' => $task->start_date?->format('M d, Y') ?? '-',
                    'end_date' => $task->expected_end_date?->format('M d, Y') ?? $task->due_date?->format('M d, Y') ?? '-',
                ],
            ];
        }

        return response()->json($events);
    }

    public function reportsCalendar()
    {
        return view('calendar.reports');
    }

    public function reports(Request $request)
    {
        $user = auth()->user();

        $query = DailyReport::with(['user', 'task']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('report_date', [$request->start, $request->end]);
        }

        $events = [];
        foreach ($query->get() as $report) {
            $title = $user->isAdmin()
                ? $report->user->name . ': ' . Str::limit($report->summary, 30)
                : Str::limit($report->summary, 40);

            $events[] = [
                'id' => $report->id,
                'title' => $title,
                'start' => $report->report_date->format('Y-m-d'),
                'url' => route('reports.show', $report),
                'backgroundColor' => '#8b5cf6',
                'borderColor' => '#7c3aed',
                'extendedProps' => [
                    'report_date' => $report->report_date->format('M d, Y'),
                    'summary' => $report->summary,
                    'task' => $report->task?->title ?? '-',
                    'challenges' => $report->challenges ?? '-',
                ],
            ];
        }

        return response()->json($events);
    }
}
