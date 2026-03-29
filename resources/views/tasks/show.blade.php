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
            <div class="detail-value">
                @if($task->assignees->count())
                    @foreach($task->assignees as $assignee)
                        <span class="badge badge-user">{{ $assignee->name }}</span>
                    @endforeach
                @else
                    -
                @endif
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.requester') }}</div>
            <div class="detail-value">{{ $task->requester ? $task->requester->name . ' (' . $task->requester->email . ')' : '-' }}</div>
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

<div class="card">
    <div class="card-title">{{ __('messages.comments') }} ({{ $task->comments->count() }})</div>

    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="comment-form" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <textarea name="body" class="form-control" rows="3" placeholder="{{ __('messages.write_comment') }}" required>{{ old('body') }}</textarea>
            @error('body') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="comment-form-actions">
            <label class="btn btn-sm btn-outline comment-attach-btn">
                <span class="attachment-icon">&#128206;</span> {{ __('messages.attach_file') }}
                <input type="file" name="attachment" class="hidden-input" onchange="document.getElementById('attachment-name').textContent = this.files[0]?.name || ''">
            </label>
            <span id="attachment-name" class="comment-attach-name"></span>
            <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.post_comment') }}</button>
        </div>
        <div class="form-hint">{{ __('messages.max_file_size', ['size' => '5MB']) }}</div>
        @error('attachment') <div class="error-text">{{ $message }}</div> @enderror
    </form>

    @if($task->comments->count())
        <div class="comments-list">
            @foreach($task->comments as $comment)
                <div class="comment-item">
                    <div class="comment-header">
                        <div class="comment-author">
                            @if($comment->user->avatar)
                                <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="" class="comment-avatar">
                            @else
                                <div class="comment-avatar-placeholder">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</div>
                            @endif
                            <div>
                                <span class="comment-name">{{ $comment->user->name }}</span>
                                <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @if($comment->user_id === auth()->id() || auth()->user()->isAdmin())
                            <form action="{{ route('tasks.comments.destroy', [$task, $comment]) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_comment_confirm') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.delete') }}</button>
                            </form>
                        @endif
                    </div>
                    <div class="comment-body">{!! nl2br(e($comment->body)) !!}</div>
                    @if($comment->attachment_path)
                        <div class="comment-attachment">
                            @if(in_array(pathinfo($comment->attachment_name, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                <a href="{{ asset('storage/' . $comment->attachment_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $comment->attachment_path) }}" alt="{{ $comment->attachment_name }}" class="comment-attachment-image">
                                </a>
                            @else
                                <a href="{{ asset('storage/' . $comment->attachment_path) }}" target="_blank" class="comment-attachment-file">
                                    <span class="attachment-icon">&#128196;</span> {{ $comment->attachment_name }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="empty-state-inline">{{ __('messages.no_comments') }}</p>
    @endif
</div>
@endsection
