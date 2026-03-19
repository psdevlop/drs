@extends('layouts.app')
@section('title', __('messages.view_report'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.view_report') }} - {{ $report->report_date->format('M d, Y (l)') }}@if(auth()->user()->isAdmin() && $report->user_id !== auth()->id()) <span class="text-muted text-xs">by {{ $report->user->name }}</span>@endif</h1>
    <div class="actions">
        <a href="{{ route('reports.edit', $report) }}" class="btn btn-outline">{{ __('messages.edit') }}</a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline">{{ __('messages.back_to_reports') }}</a>
    </div>
</div>

<div class="card">
    @if($report->task)
    <div class="report-section">
        <h3>{{ __('messages.related_task') }}</h3>
        <div class="report-content">
            <span class="text-bold">{{ $report->task->title }}</span>
            <span class="badge badge-{{ $report->task->status }}">{{ __('messages.' . $report->task->status) }}</span>
            <span class="badge badge-{{ $report->task->priority }}">{{ __('messages.' . $report->task->priority) }}</span>
        </div>
    </div>
    @endif

    <div class="report-section">
        <h3>{{ __('messages.summary') }}</h3>
        <div class="report-content">{{ $report->summary }}</div>
    </div>

    @if($report->tasks_completed)
    <div class="report-section">
        <h3>{{ __('messages.completed') }}</h3>
        <div class="report-content">{{ $report->tasks_completed }}</div>
    </div>
    @endif

    @if($report->tasks_in_progress)
    <div class="report-section">
        <h3>{{ __('messages.in_progress') }}</h3>
        <div class="report-content">{{ $report->tasks_in_progress }}</div>
    </div>
    @endif

    @if($report->challenges)
    <div class="report-section">
        <h3>{{ __('messages.challenges') }}</h3>
        <div class="report-content">{{ $report->challenges }}</div>
    </div>
    @endif

    @if($report->plan_for_tomorrow)
    <div class="report-section">
        <h3>{{ __('messages.plan_for_tomorrow') }}</h3>
        <div class="report-content">{{ $report->plan_for_tomorrow }}</div>
    </div>
    @endif

    <div class="report-meta">
        {{ __('messages.submitted') }} {{ $report->created_at->diffForHumans() }} &middot; {{ __('messages.last_updated') }} {{ $report->updated_at->diffForHumans() }}
    </div>
</div>
@endsection
