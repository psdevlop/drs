@extends('layouts.app')
@section('title', __('messages.settings'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.settings') }}</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">{{ __('messages.back') }}</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-title">{{ __('messages.holiday_settings') }}</div>
    <p class="text-muted text-sm">{{ __('messages.holiday_settings_desc') }}</p>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')

        <div class="form-group">
            <label>{{ __('messages.holiday_days') }}</label>
            <div class="holiday-days-grid">
                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                    <label class="checkbox-label holiday-day-item">
                        <input type="checkbox" name="holiday_days[]" value="{{ $day }}" {{ in_array($day, $holidayDays) ? 'checked' : '' }}>
                        <span>{{ __('messages.day_' . $day) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('messages.save_settings') }}</button>
    </form>
</div>
@endsection
