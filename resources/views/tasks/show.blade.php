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
            <div class="detail-value">{{ $task->expected_end_date?->translatedFormat(__('messages.date_format_medium')) ?? '-' }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.start_date') }}</div>
            <div class="detail-value">{{ $task->start_date?->translatedFormat(__('messages.date_format_medium')) ?? '-' }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.due_date') }}</div>
            <div class="detail-value">{{ $task->due_date?->translatedFormat(__('messages.date_format_medium')) ?? '-' }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">{{ __('messages.created_at') }}</div>
            <div class="detail-value">{{ $task->created_at->translatedFormat(__('messages.date_time_format_medium')) }}</div>
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
                <span class="attachment-icon">&#128206;</span> {{ __('messages.attach_files') }}
                <input type="file" name="attachments[]" class="hidden-input" multiple onchange="document.getElementById('attachment-name').textContent = Array.from(this.files).map(f => f.name).join(', ')">
            </label>
            <span id="attachment-name" class="comment-attach-name"></span>
            <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.post_comment') }}</button>
        </div>
        <div class="form-hint">{{ __('messages.attach_files_hint') }}</div>
        @error('attachments') <div class="error-text">{{ $message }}</div> @enderror
        @error('attachments.*') <div class="error-text">{{ $message }}</div> @enderror
    </form>

    @if($task->comments->count())
        <div class="comments-list">
            @foreach($task->comments->whereNull('parent_id')->sortByDesc('created_at') as $comment)
                @include('tasks._comment', ['comment' => $comment, 'task' => $task, 'canReply' => true])
            @endforeach
        </div>
    @else
        <p class="empty-state-inline">{{ __('messages.no_comments') }}</p>
    @endif
</div>

<script>
function toggleCommentEdit(id, editing) {
    const body = document.querySelector(`[data-comment-body="${id}"]`);
    const form = document.querySelector(`[data-comment-edit="${id}"]`);
    if (!body || !form) return;
    body.style.display = editing ? 'none' : '';
    form.style.display = editing ? '' : 'none';
    if (editing) form.querySelector('textarea')?.focus();
}
function toggleCommentReply(id, open) {
    const form = document.querySelector(`[data-comment-reply="${id}"]`);
    if (!form) return;
    form.style.display = open ? '' : 'none';
    if (open) form.querySelector('textarea')?.focus();
}
function handleReactionSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button');
    if (btn.disabled) return false;
    btn.disabled = true;
    const data = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin',
    })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(payload => {
            const container = form.closest('.comment-reactions');
            if (!container || !payload.reactions) return;
            payload.reactions.forEach(r => {
                const b = container.querySelector(`[data-reaction-emoji="${r.emoji}"]`);
                if (!b) return;
                b.classList.toggle('is-mine', !!r.mine);
                b.classList.toggle('is-empty', !r.count);
                if (r.count) { b.setAttribute('title', r.names.join(', ')); } else { b.removeAttribute('title'); }
                const c = b.querySelector('.comment-reaction-count');
                if (c) { c.textContent = r.count || ''; c.toggleAttribute('hidden', !r.count); }
            });
        })
        .catch(() => { form.submit(); })
        .finally(() => { btn.disabled = false; });
    return false;
}
</script>
@endsection
