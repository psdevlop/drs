@extends('layouts.app')
@section('title', __('messages.edit_task'))
@section('ckeditor', true)
@section('content')
<div class="page-header">
    <h1>{{ __('messages.edit_task') }}</h1>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline">{{ __('messages.back_to_tasks') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('tasks.update', $task) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="form-group">
            <label for="title">{{ __('messages.title') }}</label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $task->title) }}" required>
            @error('title') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="description">{{ __('messages.description') }}</label>
            <textarea id="description" name="description" class="form-control ckeditor-field">{{ old('description', $task->description) }}</textarea>
            @error('description') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="assigned_to">{{ __('messages.assign_to') }}</label>
            <select id="assigned_to" name="assigned_to" class="form-control">
                <option value="">{{ __('messages.unassigned') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            @error('assigned_to') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="expected_end_date">{{ __('messages.expected_end_date') }}</label>
            <input type="date" id="expected_end_date" name="expected_end_date" class="form-control" value="{{ old('expected_end_date', $task->expected_end_date?->format('Y-m-d')) }}">
            @error('expected_end_date') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="status">{{ __('messages.status') }}</label>
                <select id="status" name="status" class="form-control">
                    <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                    <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                    <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="priority">{{ __('messages.priority') }}</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>{{ __('messages.low') }}</option>
                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>{{ __('messages.medium') }}</option>
                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>{{ __('messages.high') }}</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="progress">{{ __('messages.progress') }} ({{ old('progress', $task->progress) }}%)</label>
            <input type="range" id="progress" name="progress" min="0" max="100" step="5" value="{{ old('progress', $task->progress) }}" class="form-control form-control-range" oninput="this.previousElementSibling.textContent='{{ __('messages.progress') }} (' + this.value + '%)'">
            <div class="progress-range-labels"><span>0%</span><span>50%</span><span>100%</span></div>
            @error('progress') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="start_date">{{ __('messages.start_date') }}</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}">
                @error('start_date') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="due_date">{{ __('messages.due_date') }}</label>
                <input type="date" id="due_date" name="due_date" class="form-control" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
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
        <button type="submit" class="btn btn-primary">{{ __('messages.update_task') }}</button>
    </form>
</div>

@if($task->attachments->count())
<div class="card">
    <div class="card-title">{{ __('messages.current_attachments') }}</div>
    <div class="attachment-list">
        @foreach($task->attachments as $attachment)
            <div class="attachment-item">
                @if($attachment->type === 'image')
                    <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->original_name }}" class="attachment-thumb">
                @elseif($attachment->type === 'file')
                    <span class="attachment-icon">&#128196;</span>
                @else
                    <span class="attachment-icon">&#128279;</span>
                @endif
                <span class="attachment-name">
                    @if($attachment->type === 'link')
                        <a href="{{ $attachment->url }}" target="_blank">{{ $attachment->url }}</a>
                    @else
                        <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">{{ $attachment->original_name }}</a>
                    @endif
                </span>
                <form action="{{ route('tasks.attachments.destroy', $attachment) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_attachment_confirm') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.delete') }}</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
