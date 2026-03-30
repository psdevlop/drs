@extends('layouts.app')
@section('title', __('messages.rotation_preview'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.rotation_preview') }}</h1>
    <a href="{{ route('oncall.index') }}" class="btn btn-outline">{{ __('messages.oncall_back') }}</a>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <form method="GET" action="{{ route('oncall.rotations.preview') }}" class="filter-bar">
        <div class="form-group">
            <label>{{ __('messages.start_date') }}</label>
            <input type="date" name="start_date" class="form-control filter-date" value="{{ $startDate->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label>{{ __('messages.rotation_days_to_show') }}</label>
            <select name="days" class="form-control filter-select">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 {{ __('messages.days') }}</option>
                <option value="14" {{ $days == 14 ? 'selected' : '' }}>14 {{ __('messages.days') }}</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 {{ __('messages.days') }}</option>
                <option value="60" {{ $days == 60 ? 'selected' : '' }}>60 {{ __('messages.days') }}</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 {{ __('messages.days') }}</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">{{ __('messages.filter') }}</button>
    </form>
</div>

@if($rotations->isEmpty())
    <div class="card">
        <div class="empty-state">
            <p>{{ __('messages.rotation_none_active') }}</p>
            <a href="{{ route('oncall.rotations.create') }}" class="btn btn-primary">{{ __('messages.rotation_new') }}</a>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-title">{{ __('messages.rotation_preview_title') }}</div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.day') }}</th>
                        <th>{{ __('messages.oncall_duty_users') }}</th>
                        <th>{{ __('messages.rotation_name') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview as $day)
                        <tr class="{{ $day['date']->isToday() ? 'preview-today' : '' }} {{ $day['date']->isWeekend() ? 'preview-weekend' : '' }}">
                            <td class="text-bold">{{ $day['date']->format('M d, Y') }}</td>
                            <td>{{ $day['date']->format('l') }}</td>
                            <td>
                                @if(count($day['users']))
                                    @foreach($day['users'] as $u)
                                        <span class="badge {{ $u['is_pic'] ? 'badge-pic' : 'badge-oncall' }}">{{ $u['name'] }}@if($u['is_pic']) (PIC)@endif</span>
                                    @endforeach
                                @else
                                    <span class="text-muted text-sm">{{ __('messages.holiday') }}</span>
                                @endif
                            </td>
                            <td>
                                @foreach($day['users'] as $u)
                                    <span class="text-sm text-muted">{{ $u['rotation'] }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">{{ __('messages.rotation_generate_title') }}</div>
        <p class="text-muted text-sm">{{ __('messages.rotation_generate_desc') }}</p>
        <form method="POST" action="{{ route('oncall.rotations.generate') }}" class="filter-bar" onsubmit="return confirm('{{ __('messages.rotation_generate_confirm') }}')">
            @csrf
            <div class="form-group">
                <label>{{ __('messages.start_date') }}</label>
                <input type="date" name="start_date" class="form-control filter-date" value="{{ now()->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>{{ __('messages.end_date') }}</label>
                <input type="date" name="end_date" class="form-control filter-date" value="{{ now()->addDays(30)->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-primary">{{ __('messages.rotation_generate') }}</button>
        </form>
    </div>
@endif
@endsection
