@extends('layouts.app')
@section('title', __('messages.submit_daily_report'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.submit_daily_report') }}</h1>
    <a href="{{ route('reports.index') }}" class="btn btn-outline">{{ __('messages.back_to_reports') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('reports.store') }}">
        @csrf
        <div class="form-group">
            <label for="report_date">{{ __('messages.report_date') }}</label>
            <input type="date" id="report_date" name="report_date" class="form-control" value="{{ old('report_date', today()->format('Y-m-d')) }}" required>
            @error('report_date') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="task_id">{{ __('messages.related_task') }} <span class="label-optional">({{ __('messages.optional') }})</span></label>
            <select id="task_id" name="task_id" class="form-control">
                <option value="">{{ __('messages.no_specific_task') }}</option>
                @foreach($tasks as $task)
                    <option value="{{ $task->id }}" {{ old('task_id') == $task->id ? 'selected' : '' }}>{{ $task->title }} ({{ __('messages.' . $task->status) }})</option>
                @endforeach
            </select>
            @error('task_id') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="summary">{{ __('messages.summary') }}</label>
            <textarea id="summary" name="summary" class="form-control" required placeholder="{{ __('messages.summary_placeholder') }}">{{ old('summary') }}</textarea>
            @error('summary') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="challenges">{{ __('messages.challenges') }}</label>
            <textarea id="challenges" name="challenges" class="form-control" placeholder="{{ __('messages.challenges_placeholder') }}">{{ old('challenges') }}</textarea>
        </div>
        <div class="form-group">
            <label for="plan_for_tomorrow">{{ __('messages.plan_for_tomorrow') }}</label>
            <textarea id="plan_for_tomorrow" name="plan_for_tomorrow" class="form-control" placeholder="{{ __('messages.plan_for_tomorrow_placeholder') }}">{{ old('plan_for_tomorrow') }}</textarea>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-success">{{ __('messages.submit_report') }}</button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
