@extends('layouts.app')
@section('title', __('messages.attendance'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.attendance') }}</h1>
</div>

{{-- Check-in / Check-out Card --}}
<div class="card attendance-action-card">
    <div class="attendance-status-section">
        <div class="attendance-date">
            <div class="attendance-date-day">{{ now()->format('d') }}</div>
            <div>
                <div class="attendance-date-month">{{ now()->format('F Y') }}</div>
                <div class="attendance-date-weekday">{{ now()->format('l') }}</div>
            </div>
        </div>
        <div class="attendance-clock" id="liveClock">{{ now()->format('h:i:s A') }}</div>
    </div>

    <div class="attendance-actions-row">
        @if(!$todayAttendance || !$todayAttendance->check_in)
            {{-- Not checked in --}}
            <div class="attendance-status-info">
                <span class="attendance-status-dot status-absent"></span>
                <span>{{ __('messages.not_checked_in_today') }}</span>
            </div>
            <form action="{{ route('attendance.check-in') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-attendance">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    {{ __('messages.check_in') }}
                </button>
            </form>
        @elseif($todayAttendance->isCheckedIn())
            {{-- Checked in, not checked out --}}
            <div class="attendance-status-info">
                <span class="attendance-status-dot status-checked-in"></span>
                <span>{{ __('messages.checked_in_at') }} <strong>{{ $todayAttendance->check_in->format('h:i A') }}</strong></span>
            </div>
            <form action="{{ route('attendance.check-out') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-attendance" onclick="return confirm('{{ __('messages.check_out_confirm') }}')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    {{ __('messages.check_out') }}
                </button>
            </form>
        @else
            {{-- Completed --}}
            <div class="attendance-status-info">
                <span class="attendance-status-dot status-completed"></span>
                <span>
                    {{ __('messages.todays_attendance_complete') }}
                    &mdash; {{ $todayAttendance->check_in->format('h:i A') }} ~ {{ $todayAttendance->check_out->format('h:i A') }}
                    <strong>({{ $todayAttendance->formattedHours() }})</strong>
                </span>
            </div>
        @endif
    </div>
</div>

{{-- Monthly Summary --}}
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-value">{{ $monthlySummary['total_days'] }}</div>
        <div class="stat-label">{{ __('messages.days_worked') }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-value">{{ \App\Models\Attendance::formatDecimalHours($monthlySummary['total_hours']) }}</div>
        <div class="stat-label">{{ __('messages.total_hours_worked') }}</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-value">{{ \App\Models\Attendance::formatDecimalHours($monthlySummary['avg_hours']) }}</div>
        <div class="stat-label">{{ __('messages.avg_hours_per_day') }}</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('attendance.index') }}" class="filter-bar">
    <div class="form-group">
        <label>{{ __('messages.month') }}</label>
        <input type="month" name="month" class="form-control filter-date" value="{{ request('month', now()->format('Y-m')) }}">
    </div>
    @if(auth()->user()->isAdmin() && $users->count())
        <div class="form-group">
            <label>{{ __('messages.user') }}</label>
            <select name="user_id" class="form-control filter-select">
                <option value="">{{ __('messages.all_users') }}</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <button type="submit" class="btn btn-outline">{{ __('messages.filter') }}</button>
    @if(request()->hasAny(['month', 'user_id']))
        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-secondary">{{ __('messages.clear') }}</a>
    @endif
</form>

{{-- Attendance Records --}}
<div class="card">
    <div class="card-title">{{ __('messages.attendance_records') }}</div>
    @if($attendances->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.day') }}</th>
                        @if(auth()->user()->isAdmin() && !request('user_id'))
                            <th>{{ __('messages.user') }}</th>
                        @endif
                        <th>{{ __('messages.check_in') }}</th>
                        <th>{{ __('messages.check_out') }}</th>
                        <th>{{ __('messages.total_hours_col') }}</th>
                        <th>{{ __('messages.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $att)
                        <tr class="{{ $att->date->isToday() ? 'preview-today' : '' }}">
                            <td class="text-bold">{{ $att->date->format('M d, Y') }}</td>
                            <td>{{ $att->date->format('l') }}</td>
                            @if(auth()->user()->isAdmin() && !request('user_id'))
                                <td>{{ $att->user->name }}</td>
                            @endif
                            <td>{{ $att->check_in?->format('h:i A') ?? '-' }}</td>
                            <td>{{ $att->check_out?->format('h:i A') ?? '-' }}</td>
                            <td>{{ $att->formattedHours() }}</td>
                            <td>
                                @if($att->isCompleted())
                                    <span class="badge badge-completed">{{ __('messages.completed') }}</span>
                                @elseif($att->isCheckedIn())
                                    <span class="badge badge-in_progress">{{ __('messages.working') }}</span>
                                @else
                                    <span class="badge badge-pending">{{ __('messages.pending') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">{{ $attendances->withQueryString()->links() }}</div>
    @else
        <div class="empty-state">
            <p>{{ __('messages.no_attendance_records') }}</p>
        </div>
    @endif
</div>

<script>
setInterval(function() {
    var now = new Date();
    var h = now.getHours(), m = now.getMinutes(), s = now.getSeconds();
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    document.getElementById('liveClock').textContent =
        String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0') + ' ' + ampm;
}, 1000);
</script>
@endsection
