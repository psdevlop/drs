<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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
        @ini_set('memory_limit', '512M');
        $user = auth()->user();
        $userId = $user->id;
        $events = [];

        // Tasks — visible to all users so the schedule view is shared.
        // Every task is rendered: undated tasks (no start/due/expected) fall
        // back to their creation date so newly created pending/in-progress
        // tasks still appear instead of being silently dropped.
        $taskQuery = Task::with(['assignees', 'user']);

        $rangeStart = $request->filled('start') ? $request->start : null;
        $rangeEnd = $request->filled('end') ? $request->end : null;

        $colors = [
            'pending' => ['bg' => '#f59e0b', 'border' => '#d97706'],
            'in_progress' => ['bg' => '#3b82f6', 'border' => '#2563eb'],
            'completed' => ['bg' => '#10b981', 'border' => '#059669'],
        ];

        foreach ($taskQuery->get() as $task) {
            $startDate = $task->start_date?->format('Y-m-d') ?? $task->created_at->format('Y-m-d');
            $endDate = $task->expected_end_date?->format('Y-m-d') ?? $task->due_date?->format('Y-m-d') ?? $startDate;
            // Guard against an end earlier than the start (e.g. a task created
            // after its due date) which FullCalendar would otherwise drop.
            if ($endDate < $startDate) {
                $endDate = $startDate;
            }
            // Overlap test against the requested window so multi-day tasks that
            // span the view — but whose individual dates fall outside it — still show.
            if ($rangeStart && $rangeEnd && ($startDate > $rangeEnd || $endDate < $rangeStart)) {
                continue;
            }
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
                    'status_key' => $task->status,
                    'status' => __('messages.' . $task->status),
                    'priority' => __('messages.' . $task->priority),
                    'progress' => $task->progress,
                    'assignee' => $task->assignees->pluck('name')->join(', ') ?: '-',
                    'start_date' => $task->start_date?->translatedFormat(__('messages.date_format_medium')) ?? '-',
                    'end_date' => $task->expected_end_date?->translatedFormat(__('messages.date_format_medium')) ?? $task->due_date?->translatedFormat(__('messages.date_format_medium')) ?? '-',
                ],
            ];
        }

        // Reports — visible to all users so the schedule view is shared
        $reportQuery = DailyReport::with(['user', 'task']);
        if ($request->filled('start') && $request->filled('end')) {
            $reportQuery->whereBetween('report_date', [$request->start, $request->end]);
        }

        foreach ($reportQuery->get() as $report) {
            $title = $report->user_id !== $userId
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
                    'report_date' => $report->report_date->translatedFormat(__('messages.date_format_medium')),
                    'summary' => $report->summary,
                    'task' => $report->task?->title ?? '-',
                    'challenges' => $report->challenges ?? '-',
                ],
            ];
        }

        // On Duty (Sun-Fri working days) from attendance check-ins
        // On Call (Sat or government holiday) from active rotation rosters
        if ($request->filled('start') && $request->filled('end')) {
            $rangeStart = Carbon::parse($request->start);
            $rangeEnd = Carbon::parse($request->end);
            $rangeStartKey = $rangeStart->format('Y-m-d');
            $rangeEndKey = $rangeEnd->format('Y-m-d');

            $holidayDates = array_flip(array_column(Setting::getScheduleHolidayDates(), 'date'));

            $attendancesByDate = Attendance::with('user')
                ->whereBetween('date', [$rangeStartKey, $rangeEndKey])
                ->whereNotNull('check_in')
                ->get()
                ->groupBy(fn ($a) => $a->date->format('Y-m-d'));

            $activeRotations = \App\Models\OnCallRotation::with('users')->where('is_active', true)->get();

            for ($date = $rangeStart->copy(); $date->lte($rangeEnd); $date->addDay()) {
                $dateKey = $date->format('Y-m-d');
                $isOnCallDay = $date->dayOfWeek === Carbon::SATURDAY || isset($holidayDates[$dateKey]);

                if ($isOnCallDay) {
                    if ($activeRotations->isEmpty()) {
                        continue;
                    }

                    $picUser = null;
                    foreach ($activeRotations as $rotation) {
                        $picUser = $rotation->getOnCallUserForDate($date);
                        if ($picUser) {
                            break;
                        }
                    }

                    $manualEntry = OnCall::with('pic')->where('date', $dateKey)->first();
                    if ($manualEntry && $manualEntry->pic) {
                        $picUser = $manualEntry->pic;
                    }

                    if (!$picUser) {
                        continue;
                    }

                    $userNames = $picUser->name;
                    $title = '📞 ' . __('messages.on_call') . ': ' . $userNames;

                    $events[] = [
                        'id' => 'oncall-' . $dateKey,
                        'title' => $title,
                        'start' => $dateKey,
                        'url' => route('oncall.index'),
                        'backgroundColor' => '#ef4444',
                        'borderColor' => '#dc2626',
                        'extendedProps' => [
                            'type' => 'oncall',
                            'users' => $userNames,
                            'pic' => $userNames,
                            'date' => $date->translatedFormat(__('messages.date_format_medium')),
                        ],
                    ];
                } else {
                    $dayAttendances = $attendancesByDate->get($dateKey);
                    if (!$dayAttendances || $dayAttendances->isEmpty()) {
                        continue;
                    }

                    $userNames = $dayAttendances->pluck('user.name')->filter()->unique()->values()->join(', ');
                    if ($userNames === '') {
                        continue;
                    }

                    // Rotation PIC for the working day (cycles one user per working day)
                    $picName = null;
                    foreach ($activeRotations as $rotation) {
                        foreach ($rotation->getUsersForDate($date) as $u) {
                            if ($u->is_pic ?? false) {
                                $picName = $u->name;
                                break 2;
                            }
                        }
                    }

                    // Manual PIC override from on_calls table
                    $manualEntry = OnCall::with('pic')->where('date', $dateKey)->first();
                    if ($manualEntry && $manualEntry->pic) {
                        $picName = $manualEntry->pic->name;
                    }

                    $title = '🛠️ ' . __('messages.on_duty') . ': ' . $userNames;
                    if ($picName) {
                        $title .= ' (' . __('messages.pic_short') . ': ' . $picName . ')';
                    }

                    $events[] = [
                        'id' => 'onduty-' . $dateKey,
                        'title' => $title,
                        'start' => $dateKey,
                        'url' => route('attendance.index'),
                        'backgroundColor' => '#0ea5e9',
                        'borderColor' => '#0284c7',
                        'extendedProps' => [
                            'type' => 'onduty',
                            'users' => $userNames,
                            'pic' => $picName ?? '-',
                            'date' => $date->translatedFormat(__('messages.date_format_medium')),
                        ],
                    ];
                }
            }
        }

        // Holidays (specific dates with optional reason)
        foreach (Setting::getScheduleHolidayDates() as $holiday) {
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
                    'date' => Carbon::parse($holiday['date'])->translatedFormat(__('messages.date_format_medium')),
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
                    'status' => __('messages.' . $task->status),
                    'priority' => __('messages.' . $task->priority),
                    'progress' => $task->progress,
                    'assignee' => $task->assignees->pluck('name')->join(', ') ?: '-',
                    'start_date' => $task->start_date?->translatedFormat(__('messages.date_format_medium')) ?? '-',
                    'end_date' => $task->expected_end_date?->translatedFormat(__('messages.date_format_medium')) ?? $task->due_date?->translatedFormat(__('messages.date_format_medium')) ?? '-',
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
                    'report_date' => $report->report_date->translatedFormat(__('messages.date_format_medium')),
                    'summary' => $report->summary,
                    'task' => $report->task?->title ?? '-',
                    'challenges' => $report->challenges ?? '-',
                ],
            ];
        }

        return response()->json($events);
    }
}
