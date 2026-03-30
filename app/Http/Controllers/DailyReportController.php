<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyReport::with('user');

        if ($request->filled('date')) {
            $query->where('report_date', $request->date);
        }

        $reports = $query->latest('report_date')->paginate(15);

        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        $todayReport = auth()->user()->dailyReports()->where('report_date', today())->first();
        if ($todayReport) {
            return redirect()->route('reports.edit', $todayReport)
                ->with('info', 'You already have a report for today. You can edit it here.');
        }

        $userId = auth()->id();
        $tasks = Task::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereHas('assignees', fn ($q2) => $q2->where('users.id', $userId));
        })->whereIn('status', ['pending', 'in_progress', 'completed'])->orderBy('title')->get();

        return view('reports.create', compact('tasks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_date' => ['required', 'date', 'unique:daily_reports,report_date,NULL,id,user_id,' . $request->user()->id],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'summary' => ['required', 'string'],
            'tasks_completed' => ['nullable', 'string'],
            'tasks_in_progress' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
            'plan_for_tomorrow' => ['nullable', 'string'],
        ]);

        $report = $request->user()->dailyReports()->create($validated);

        // Notify admins about new report
        $admins = User::where('role', '!=', 'user')->where('id', '!=', $request->user()->id)->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'report_submitted',
                'title' => __('messages.notif_report_submitted_title'),
                'message' => __('messages.notif_report_submitted_message', ['user' => $request->user()->name, 'date' => $report->report_date->format('M d, Y')]),
                'link' => route('reports.show', $report),
            ]);
        }

        // Notify task owner if report is linked to a task they own
        if ($report->task_id) {
            $task = Task::find($report->task_id);
            if ($task && $task->user_id !== $request->user()->id && !User::find($task->user_id)?->isAdmin()) {
                Notification::create([
                    'user_id' => $task->user_id,
                    'type' => 'report_submitted',
                    'title' => __('messages.notif_report_submitted_title'),
                    'message' => __('messages.notif_report_for_task_message', ['user' => $request->user()->name, 'task' => $task->title]),
                    'link' => route('reports.show', $report),
                ]);
            }
        }

        return redirect()->route('reports.index')->with('success', 'Daily report submitted successfully.');
    }

    public function show(DailyReport $report)
    {
        $report->load(['task', 'user']);
        return view('reports.show', compact('report'));
    }

    public function edit(DailyReport $report)
    {
        $this->authorizeReport($report);
        $userId = auth()->id();
        $tasks = Task::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereHas('assignees', fn ($q2) => $q2->where('users.id', $userId));
        })->whereIn('status', ['pending', 'in_progress', 'completed'])->orderBy('title')->get();

        return view('reports.edit', compact('report', 'tasks'));
    }

    public function update(Request $request, DailyReport $report)
    {
        $this->authorizeReport($report);

        $validated = $request->validate([
            'task_id' => ['nullable', 'exists:tasks,id'],
            'summary' => ['required', 'string'],
            'tasks_completed' => ['nullable', 'string'],
            'tasks_in_progress' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
            'plan_for_tomorrow' => ['nullable', 'string'],
        ]);

        $report->update($validated);

        return redirect()->route('reports.index')->with('success', 'Daily report updated successfully.');
    }

    public function destroy(DailyReport $report)
    {
        $this->authorizeReport($report);
        $report->delete();

        return redirect()->route('reports.index')->with('success', 'Daily report deleted successfully.');
    }

    private function authorizeReport(DailyReport $report): void
    {
        if (auth()->user()->isAdmin()) {
            return;
        }
        if ($report->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
