@php
    $canEdit = $comment->user_id === auth()->id() && $comment->created_at->diffInMinutes(now()) <= 15;
    $reactionsByEmoji = $comment->reactions->groupBy('emoji');
    $myReactions = $comment->reactions->where('user_id', auth()->id())->pluck('emoji')->all();
    $canReply = $canReply ?? false;
@endphp
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
                @if($comment->updated_at->gt($comment->created_at))
                    <span class="comment-time">({{ __('messages.edited') }})</span>
                @endif
            </div>
        </div>
        <div class="comment-form-actions">
            @if($canReply)
                <button type="button" class="btn btn-sm btn-outline" onclick="toggleCommentReply({{ $comment->id }}, true)">{{ __('messages.reply') }}</button>
            @endif
            @if($comment->user_id === auth()->id())
                @if($canEdit)
                    <button type="button" class="btn btn-sm btn-outline" onclick="toggleCommentEdit({{ $comment->id }}, true)">{{ __('messages.edit') }}</button>
                @endif
                <form action="{{ route('tasks.comments.destroy', [$task, $comment]) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_comment_confirm') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.delete') }}</button>
                </form>
            @endif
        </div>
    </div>
    <div class="comment-body" data-comment-body="{{ $comment->id }}">{!! nl2br(preg_replace('~(https?://[^\s<]+)~i', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', e($comment->body))) !!}</div>
    @if($canEdit)
        <form action="{{ route('tasks.comments.update', [$task, $comment]) }}" method="POST" class="comment-edit-form" data-comment-edit="{{ $comment->id }}" style="display:none;">
            @csrf @method('PUT')
            <div class="form-group">
                <textarea name="body" class="form-control" rows="3" required maxlength="5000">{{ $comment->body }}</textarea>
            </div>
            <div class="comment-form-actions">
                <button type="submit" class="btn btn-sm btn-primary">{{ __('messages.save_changes') }}</button>
                <button type="button" class="btn btn-sm btn-outline" onclick="toggleCommentEdit({{ $comment->id }}, false)">{{ __('messages.cancel') }}</button>
            </div>
        </form>
    @endif
    <div class="comment-reactions" data-reactions-for="{{ $comment->id }}">
        @foreach(['👍','❤️','😄','🎉','👀'] as $emoji)
            @php($rows = $reactionsByEmoji[$emoji] ?? collect())
            @php($count = $rows->count())
            @php($mine = in_array($emoji, $myReactions, true))
            @php($names = $rows->map(fn ($r) => $r->user->name ?? '')->filter()->values()->all())
            <form action="{{ route('tasks.comments.reactions.toggle', [$task, $comment]) }}" method="POST" class="comment-reaction-form" onsubmit="return handleReactionSubmit(event)">
                @csrf
                <input type="hidden" name="emoji" value="{{ $emoji }}">
                <button type="submit" class="comment-reaction-btn {{ $mine ? 'is-mine' : '' }} {{ $count ? '' : 'is-empty' }}" data-reaction-emoji="{{ $emoji }}" @if($count) title="{{ implode(', ', $names) }}" @endif>
                    <span class="comment-reaction-emoji">{{ $emoji }}</span>
                    <span class="comment-reaction-count" @if(!$count) hidden @endif>{{ $count ?: '' }}</span>
                </button>
            </form>
        @endforeach
    </div>
    @if($comment->attachments->count())
        <div class="comment-attachments">
            @foreach($comment->attachments as $attachment)
                <div class="comment-attachment">
                    @if(in_array(strtolower(pathinfo($attachment->original_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->original_name }}" class="comment-attachment-image">
                        </a>
                    @else
                        <a href="{{ asset('storage/' . $attachment->file_path) }}" download="{{ $attachment->original_name }}" class="comment-attachment-file">
                            <span class="attachment-icon">&#128196;</span> {{ $attachment->original_name }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
    @if($canReply)
        <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="comment-reply-form" data-comment-reply="{{ $comment->id }}" enctype="multipart/form-data" style="display:none;">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
            <div class="form-group">
                <textarea name="body" class="form-control" rows="2" required maxlength="5000" placeholder="{{ __('messages.write_reply') }}"></textarea>
            </div>
            <div class="comment-form-actions">
                <label class="btn btn-sm btn-outline comment-attach-btn">
                    <span class="attachment-icon">&#128206;</span> {{ __('messages.attach_files') }}
                    <input type="file" name="attachments[]" class="hidden-input" multiple onchange="this.parentElement.nextElementSibling.textContent = Array.from(this.files).map(f => f.name).join(', ')">
                </label>
                <span class="comment-attach-name"></span>
                <button type="submit" class="btn btn-sm btn-primary">{{ __('messages.post_reply') }}</button>
                <button type="button" class="btn btn-sm btn-outline" onclick="toggleCommentReply({{ $comment->id }}, false)">{{ __('messages.cancel') }}</button>
            </div>
        </form>
        @if($comment->replies->count())
            <div class="comment-replies">
                @foreach($comment->replies as $reply)
                    @include('tasks._comment', ['comment' => $reply, 'task' => $task, 'canReply' => false])
                @endforeach
            </div>
        @endif
    @endif
</div>
