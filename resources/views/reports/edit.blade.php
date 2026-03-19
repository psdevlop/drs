@extends('layouts.app')
@section('title', __('messages.edit_report'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.edit_report') }} - {{ $report->report_date->format('M d, Y') }}</h1>
    <a href="{{ route('reports.index') }}" class="btn btn-outline">{{ __('messages.back_to_reports') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('reports.update', $report) }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label for="task_id">{{ __('messages.related_task') }} <span class="label-optional">({{ __('messages.optional') }})</span></label>
            <select id="task_id" name="task_id" class="form-control">
                <option value="">{{ __('messages.no_specific_task') }}</option>
                @foreach($tasks as $task)
                    <option value="{{ $task->id }}" {{ old('task_id', $report->task_id) == $task->id ? 'selected' : '' }}>{{ $task->title }} ({{ __('messages.' . $task->status) }})</option>
                @endforeach
            </select>
            @error('task_id') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="summary">{{ __('messages.summary') }}</label>
            <textarea id="summary" name="summary" class="form-control" required>{{ old('summary', $report->summary) }}</textarea>
            @error('summary') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="challenges">{{ __('messages.challenges') }}</label>
            <textarea id="challenges" name="challenges" class="form-control">{{ old('challenges', $report->challenges) }}</textarea>
        </div>
        <div class="form-group">
            <label for="plan_for_tomorrow">{{ __('messages.plan_for_tomorrow') }}</label>
            <textarea id="plan_for_tomorrow" name="plan_for_tomorrow" class="form-control">{{ old('plan_for_tomorrow', $report->plan_for_tomorrow) }}</textarea>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-success">{{ __('messages.update_report') }}</button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
