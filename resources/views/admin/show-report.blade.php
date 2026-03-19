@extends('layouts.app')
@section('title', __('messages.view_report'))
@section('content')
<div class="page-header">
    <h1>{{ $report->user->name }} - {{ $report->report_date->format('M d, Y (l)') }}</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">{{ __('messages.back_to_admin') }}</a>
</div>

<div class="card">
    <div class="report-section">
        <h3>{{ __('messages.summary') }}</h3>
        <div class="report-content">{{ $report->summary }}</div>
    </div>

    @if($report->tasks_completed)
    <div class="report-section">
        <h3>{{ __('messages.tasks_completed') }}</h3>
        <div class="report-content">{{ $report->tasks_completed }}</div>
    </div>
    @endif

    @if($report->tasks_in_progress)
    <div class="report-section">
        <h3>{{ __('messages.tasks_in_progress_label') }}</h3>
        <div class="report-content">{{ $report->tasks_in_progress }}</div>
    </div>
    @endif

    @if($report->challenges)
    <div class="report-section">
        <h3>{{ __('messages.challenges_blockers') }}</h3>
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
