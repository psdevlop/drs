@extends('layouts.app')
@section('title', $announcement->title)
@section('content')
<div class="page-header">
    <h1>{{ __('messages.announcement_board') }}</h1>
    <a href="{{ route('announcements.index') }}" class="btn btn-outline">{{ __('messages.back_to_announcements') }}</a>
</div>

<div class="card announcement-card announcement-{{ $announcement->priority }}">
    <div class="announcement-show-header">
        <h2 class="announcement-show-title">
            @if($announcement->is_pinned)
                <span class="announcement-pin-icon">&#128204;</span>
            @endif
            {{ $announcement->title }}
        </h2>
        @if($announcement->priority !== 'normal')
            <span class="badge badge-announcement-{{ $announcement->priority }}">{{ __('messages.priority_' . $announcement->priority) }}</span>
        @endif
    </div>
    <div class="announcement-show-meta">
        <span>{{ __('messages.posted_by') }}: <strong>{{ $announcement->user->name }}</strong></span>
        <span>{{ $announcement->created_at->format('M d, Y H:i') }}</span>
        @if($announcement->updated_at->gt($announcement->created_at))
            <span>({{ __('messages.edited') }})</span>
        @endif
    </div>
    <div class="announcement-show-content">{!! nl2br(e($announcement->content)) !!}</div>

    @if(auth()->user()->isAdmin())
        <div class="announcement-show-actions">
            <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-outline">{{ __('messages.edit') }}</a>
            <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_announcement_confirm') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('messages.delete') }}</button>
            </form>
        </div>
    @endif
</div>
@endsection
