@extends('layouts.app')
@section('title', $task->title)
@section('content')
<div class="page-header">
    <h1>{{ __('messages.view_task') }}</h1>
    <div class="actions">
        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary">{{ __('messages.edit') }}</a>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline">{{ __('messages.back_to_tasks') }}</a>
    </div>
</div>

<div class="card">
    <div class="detail-grid">
        <div class="detail-row">
            <div class="detail-label">{{ __('messages.title') }}</div>
            <div class="detail-value text-bold">{{ $task->title }}</div>
        </div>

        @if($task->description)
        <div class="detail-row">
            <div class="detail-label">{{ __('messages.description') }}</div>
            <div class="detail-value ck-content">{!! $task->description !!}</div>
        </div>
        @endif

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.created_by') }}</div>
            <div class="detail-value">{{ $task->user->name }} ({{ $task->user->email }})</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.assigned_to') }}</div>
            <div class="detail-value">{{ $task->assignee ? $task->assignee->name . ' (' . $task->assignee->email . ')' : '-' }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.status') }}</div>
            <div class="detail-value"><span class="badge badge-{{ $task->status }}">{{ __('messages.' . $task->status) }}</span></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.priority') }}</div>
            <div class="detail-value"><span class="badge badge-{{ $task->priority }}">{{ __('messages.' . $task->priority) }}</span></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.progress') }}</div>
            <div class="detail-value">
                <div class="progress-wrapper">
                    <div class="progress-track" style="min-width:120px;">
                        <div class="progress-fill {{ $task->progress == 100 ? 'progress-fill-complete' : '' }}" style="width:{{ $task->progress }}%"></div>
                    </div>
                    <span class="progress-text">{{ $task->progress }}%</span>
                </div>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.expected_end_date') }}</div>
            <div class="detail-value">{{ $task->expected_end_date?->format('M d, Y') ?? '-' }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.start_date') }}</div>
            <div class="detail-value">{{ $task->start_date?->format('M d, Y') ?? '-' }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.due_date') }}</div>
            <div class="detail-value">{{ $task->due_date?->format('M d, Y') ?? '-' }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.created_at') }}</div>
            <div class="detail-value">{{ $task->created_at->format('M d, Y h:i A') }}</div>
        </div>
    </div>
</div>

@if($task->attachments->count())
<div class="card">
    <div class="card-title">{{ __('messages.attachments') }}</div>

    @php
        $images = $task->attachments->where('type', 'image');
        $files = $task->attachments->where('type', 'file');
        $links = $task->attachments->where('type', 'link');
    @endphp

    @if($images->count())
    <div class="form-group">
        <label>{{ __('messages.attach_images') }}</label>
        <div class="attachment-gallery">
            @foreach($images as $image)
                <a href="{{ asset('storage/' . $image->file_path) }}" target="_blank" class="attachment-gallery-item">
                    <img src="{{ asset('storage/' . $image->file_path) }}" alt="{{ $image->original_name }}">
                    <span class="attachment-gallery-name">{{ $image->original_name }}</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($files->count())
    <div class="form-group">
        <label>{{ __('messages.attach_files') }}</label>
        <div class="attachment-list">
            @foreach($files as $file)
                <div class="attachment-item">
                    <span class="attachment-icon">&#128196;</span>
                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="attachment-name">{{ $file->original_name }}</a>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($links->count())
    <div class="form-group">
        <label>{{ __('messages.reference_links') }}</label>
        <div class="attachment-list">
            @foreach($links as $link)
                <div class="attachment-item">
                    <span class="attachment-icon">&#128279;</span>
                    <a href="{{ $link->url }}" target="_blank" class="attachment-name">{{ $link->url }}</a>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif
@endsection
