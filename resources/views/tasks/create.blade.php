@extends('layouts.app')
@section('title', __('messages.new_task_title'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.new_task_title') }}</h1>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline">{{ __('messages.back_to_tasks') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('tasks.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="title">{{ __('messages.title') }}</label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required>
            @error('title') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="description">{{ __('messages.description') }}</label>
            <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
            @error('description') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="assigned_to">{{ __('messages.assign_to') }}</label>
            <select id="assigned_to" name="assigned_to" class="form-control">
                <option value="">{{ __('messages.unassigned') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            @error('assigned_to') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="expected_end_date">{{ __('messages.expected_end_date') }}</label>
            <input type="date" id="expected_end_date" name="expected_end_date" class="form-control" value="{{ old('expected_end_date') }}">
            @error('expected_end_date') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="status">{{ __('messages.status') }}</label>
                <select id="status" name="status" class="form-control">
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="priority">{{ __('messages.priority') }}</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ __('messages.low') }}</option>
                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>{{ __('messages.medium') }}</option>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('messages.high') }}</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="progress">{{ __('messages.progress') }} ({{ old('progress', 0) }}%)</label>
            <input type="range" id="progress" name="progress" min="0" max="100" step="5" value="{{ old('progress', 0) }}" class="form-control form-control-range" oninput="this.previousElementSibling.textContent='{{ __('messages.progress') }} (' + this.value + '%)'">
            <div class="progress-range-labels"><span>0%</span><span>50%</span><span>100%</span></div>
            @error('progress') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="start_date">{{ __('messages.start_date') }}</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                @error('start_date') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="due_date">{{ __('messages.due_date') }}</label>
                <input type="date" id="due_date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                @error('due_date') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="files">{{ __('messages.attach_files') }}</label>
            <input type="file" id="files" name="files[]" class="form-control" multiple>
            <div class="form-hint">{{ __('messages.attach_files_hint') }}</div>
            @error('files.*') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="images">{{ __('messages.attach_images') }}</label>
            <input type="file" id="images" name="images[]" class="form-control" multiple accept="image/*">
            <div class="form-hint">{{ __('messages.attach_images_hint') }}</div>
            @error('images.*') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="links">{{ __('messages.reference_links') }}</label>
            <textarea id="links" name="links" class="form-control" rows="3" placeholder="{{ __('messages.reference_links_placeholder') }}">{{ old('links') }}</textarea>
            @error('links') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.create_task') }}</button>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
