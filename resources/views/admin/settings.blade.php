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

        <div class="form-group">
            <label>{{ __('messages.holiday_dates') }}</label>
            <p class="text-muted text-sm">{{ __('messages.holiday_dates_desc') }}</p>
            <div id="holiday-dates-list">
                @foreach($holidayDates as $i => $entry)
                    <div class="holiday-date-row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;">
                        <input type="date" name="holiday_date[{{ $i }}][date]" class="form-control" value="{{ $entry['date'] }}" required style="flex:0 0 180px;">
                        <input type="text" name="holiday_date[{{ $i }}][reason]" class="form-control" value="{{ $entry['reason'] ?? '' }}" placeholder="{{ __('messages.holiday_reason_placeholder') }}" style="flex:1;">
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">{{ __('messages.delete') }}</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline" onclick="addHolidayDate()">+ {{ __('messages.holiday_add_date') }}</button>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('messages.save_settings') }}</button>

        <script>
        let holidayIdx = {{ count($holidayDates) }};
        function addHolidayDate() {
            const list = document.getElementById('holiday-dates-list');
            const row = document.createElement('div');
            row.className = 'holiday-date-row';
            row.style.cssText = 'display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;';
            row.innerHTML = `<input type="date" name="holiday_date[${holidayIdx}][date]" class="form-control" required style="flex:0 0 180px;">` +
                `<input type="text" name="holiday_date[${holidayIdx}][reason]" class="form-control" placeholder="{{ __('messages.holiday_reason_placeholder') }}" style="flex:1;">` +
                `<button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">{{ __('messages.delete') }}</button>`;
            list.appendChild(row);
            holidayIdx++;
        }
        </script>
    </form>
</div>
@endsection
