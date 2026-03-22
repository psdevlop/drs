@extends('layouts.app')
@section('title', __('messages.dashboard'))
@section('content')
<div class="welcome-banner card">
    <div>
        <h2>{{ __('messages.welcome_back', ['name' => auth()->user()->name]) }}</h2>
        <p class="welcome-subtitle">{{ now()->format('l, M d, Y') }} &mdash; {{ __('messages.have_productive_day') }}</p>
    </div>
    <div class="welcome-actions">
        <a href="{{ route('schedule') }}" class="btn btn-outline schedule-btn" title="{{ __('messages.schedule_management') }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><rect x="7" y="14" width="3" height="3"/><rect x="14" y="14" width="3" height="3"/></svg>
            <span>{{ __('messages.schedule_management') }}</span>
        </a>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">{{ __('messages.new_task') }}</a>
        <a href="{{ route('reports.create') }}" class="btn btn-success">{{ __('messages.new_report') }}</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-value">{{ $taskStats['total'] }}</div>
        <div class="stat-label">{{ __('messages.total_tasks') }}</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-value">{{ $taskStats['pending'] }}</div>
        <div class="stat-label">{{ __('messages.pending') }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-value">{{ $taskStats['in_progress'] }}</div>
        <div class="stat-label">{{ __('messages.in_progress') }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-value">{{ $taskStats['completed'] }}</div>
        <div class="stat-label">{{ __('messages.completed') }}</div>
    </div>
</div>

@if($todayReport)
    <div class="alert alert-success">{{ __('messages.today_report_submitted') }} <a href="{{ route('reports.edit', $todayReport) }}">{{ __('messages.edit_it') }}</a></div>
@else
    <div class="alert alert-info">{{ __('messages.today_report_not_submitted') }} <a href="{{ route('reports.create') }}">{{ __('messages.submit_now') }}</a></div>
@endif

@if($latestAnnouncements->count())
<div class="card">
    <div class="card-title">{{ __('messages.announcement_board') }}</div>
    @foreach($latestAnnouncements as $announcement)
        <a href="{{ route('announcements.show', $announcement) }}" class="list-item list-item-link">
            <div class="list-item-row">
                <div>
                    <div class="text-bold">
                        @if($announcement->is_pinned)<span class="announcement-pin-icon-sm">&#128204;</span>@endif
                        @if($announcement->priority !== 'normal')<span class="badge badge-announcement-{{ $announcement->priority }}">{{ __('messages.priority_' . $announcement->priority) }}</span>@endif
                        {{ $announcement->title }}
                    </div>
                    <div class="text-muted text-sm">{{ Str::limit($announcement->content, 80) }}</div>
                </div>
                <div class="announcement-meta-sm">
                    <span>{{ $announcement->user->name }}</span>
                    <span>{{ $announcement->created_at->format('M d') }}</span>
                </div>
            </div>
        </a>
    @endforeach
    <div class="text-center mt-lg">
        <a href="{{ route('announcements.index') }}" class="btn btn-sm btn-outline">{{ __('messages.view_all_announcements') }}</a>
    </div>
</div>
@endif

<div class="two-col-grid">
    <div class="card">
        <div class="card-title">{{ __('messages.recent_tasks') }}</div>
        @forelse($recentTasks as $task)
            <a href="{{ route('tasks.show', $task) }}" class="list-item list-item-link">
                <div class="list-item-row">
                    <div>
                        <div class="text-bold">{{ $task->title }}</div>
                        <div class="list-item-meta">
                            <span class="badge badge-{{ $task->priority }}">{{ __('messages.' . $task->priority) }}</span>
                            @if($task->assignee)
                                <span class="list-item-assignee">&rarr; {{ $task->assignee->name }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="badge badge-{{ $task->status }}">{{ __('messages.' . $task->status) }}</span>
                </div>
                <div class="progress-wrapper mt-md">
                    <div class="progress-track progress-track-sm">
                        <div class="progress-fill {{ $task->progress == 100 ? 'progress-fill-complete' : '' }}" style="width:{{ $task->progress }}%"></div>
                    </div>
                    <span class="progress-text progress-text-sm">{{ $task->progress }}%</span>
                </div>
            </a>
        @empty
            <p class="empty-state-inline">{{ __('messages.no_tasks_yet') }}</p>
        @endforelse
        @if($recentTasks->count())
            <div class="text-center mt-lg">
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline">{{ __('messages.view_all_tasks') }}</a>
            </div>
        @endif
    </div>

    <div class="card card-min-height">
        <div class="card-title">{{ __('messages.recent_reports') }}</div>
        @forelse($recentReports as $report)
            <div class="list-item list-item-row">
                <div>
                    <div class="text-bold">{{ $report->report_date->format('M d, Y') }}@if(auth()->user()->isAdmin() && $report->user_id !== auth()->id()) <span class="list-item-report-author">- {{ $report->user->name }}</span>@endif</div>
                    <div class="text-muted text-sm">{{ Str::limit($report->summary, 50) }}</div>
                    @if($report->task)
                        <div class="list-item-task-link">{{ $report->task->title }}</div>
                    @endif
                </div>
                <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
            </div>
        @empty
            <p class="empty-state-inline">{{ __('messages.no_reports_yet') }} <a href="{{ route('reports.create') }}">{{ __('messages.submit_first_report') }}</a></p>
        @endforelse
        @if($recentReports->count())
            <div class="text-center mt-lg">
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline">{{ __('messages.view_all_reports') }}</a>
            </div>
        @endif
    </div>
</div>
@endsection
