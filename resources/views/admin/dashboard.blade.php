@extends('layouts.app')
@section('title', __('messages.admin_dashboard'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.admin_dashboard') }}</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">{{ __('messages.manage_users') }}</a>
</div>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-value">{{ $stats['total_users'] }}</div>
        <div class="stat-label">{{ __('messages.total_users') }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-value">{{ $stats['total_reports_today'] }}</div>
        <div class="stat-label">{{ __('messages.reports_today') }}</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-value">{{ $stats['total_reports'] }}</div>
        <div class="stat-label">{{ __('messages.total_reports') }}</div>
    </div>
</div>

<div class="two-col-grid">
    <div class="card">
        <div class="card-title">{{ __('messages.users') }}</div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>{{ __('messages.name') }}</th><th>{{ __('messages.role') }}</th><th>{{ __('messages.tasks') }}</th><th>{{ __('messages.reports') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="text-bold">{{ $user->name }}</div>
                            <div class="text-muted text-xs">{{ $user->email }}</div>
                        </td>
                        <td><span class="badge badge-{{ $user->role }}">{{ $user->role }}</span></td>
                        <td>{{ $user->tasks_count }}</td>
                        <td>{{ $user->daily_reports_count }}</td>
                        <td><a href="{{ route('admin.user-reports', $user) }}" class="btn btn-sm btn-outline">{{ __('messages.reports') }}</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">{{ __('messages.recent_reports_all_users') }}</div>
        @forelse($recentReports as $report)
            <div class="list-item list-item-row">
                <div>
                    <div class="text-bold">{{ $report->user->name }}</div>
                    <div class="text-muted text-sm">{{ $report->report_date->format('M d, Y') }} - {{ Str::limit($report->summary, 40) }}</div>
                </div>
                <a href="{{ route('admin.show-report', $report) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
            </div>
        @empty
            <p class="empty-state-inline">{{ __('messages.no_reports_yet_admin') }}</p>
        @endforelse
    </div>
</div>
@endsection
