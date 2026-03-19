<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Task;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->isAdmin()) {
            $query = DailyReport::with('user');
        } else {
            $query = $request->user()->dailyReports();
        }

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
            $q->where('user_id', $userId)->orWhere('assigned_to', $userId);
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

        $request->user()->dailyReports()->create($validated);

        return redirect()->route('reports.index')->with('success', 'Daily report submitted successfully.');
    }

    public function show(DailyReport $report)
    {
        $this->authorizeReport($report);
        $report->load(['task', 'user']);
        return view('reports.show', compact('report'));
    }

    public function edit(DailyReport $report)
    {
        $this->authorizeReport($report);
        $userId = auth()->id();
        $tasks = Task::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('assigned_to', $userId);
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
