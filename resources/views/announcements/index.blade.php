@extends('layouts.app')
@section('title', __('messages.announcement_board'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.announcement_board') }}</h1>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('announcements.create') }}" class="btn btn-primary">{{ __('messages.new_announcement') }}</a>
    @endif
</div>

<form method="GET" action="{{ route('announcements.index') }}" class="filter-bar">
    <div class="form-group" style="flex:1;">
        <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search_announcements') }}" value="{{ request('search') }}">
    </div>
    <div class="form-group">
        <select name="priority" class="form-control filter-select">
            <option value="">{{ __('messages.all_priorities') }}</option>
            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>{{ __('messages.priority_normal') }}</option>
            <option value="important" {{ request('priority') == 'important' ? 'selected' : '' }}>{{ __('messages.priority_important') }}</option>
            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>{{ __('messages.priority_urgent') }}</option>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">{{ __('messages.search') }}</button>
    @if(request()->hasAny(['search', 'priority']))
        <a href="{{ route('announcements.index') }}" class="btn btn-sm btn-secondary">{{ __('messages.clear') }}</a>
    @endif
</form>

<div class="announcements-list">
    @forelse($announcements as $announcement)
        <div class="card announcement-card {{ $announcement->is_pinned ? 'announcement-pinned' : '' }} announcement-{{ $announcement->priority }}">
            <div class="announcement-header">
                <div>
                    @if($announcement->is_pinned)
                        <span class="announcement-pin-icon" title="{{ __('messages.pinned') }}">&#128204;</span>
                    @endif
                    @if($announcement->priority !== 'normal')
                        <span class="badge badge-announcement-{{ $announcement->priority }}">{{ __('messages.priority_' . $announcement->priority) }}</span>
                    @endif
                    <a href="{{ route('announcements.show', $announcement) }}" class="announcement-title">{{ $announcement->title }}</a>
                </div>
                <div class="announcement-meta">
                    <span>{{ $announcement->user->name }}</span>
                    <span class="announcement-date">{{ $announcement->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
            <div class="announcement-excerpt">{{ Str::limit($announcement->content, 200) }}</div>
            <div class="announcement-footer">
                <a href="{{ route('announcements.show', $announcement) }}" class="btn btn-sm btn-outline">{{ __('messages.read_more') }}</a>
                @if(auth()->user()->isAdmin())
                    <div class="actions">
                        <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-sm btn-outline">{{ __('messages.edit') }}</a>
                        <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_announcement_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.delete') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card">
            <div class="empty-state">
                <p>{{ __('messages.no_announcements') }}</p>
            </div>
        </div>
    @endforelse
</div>

<div class="pagination-wrapper">{{ $announcements->withQueryString()->links() }}</div>
@endsection
