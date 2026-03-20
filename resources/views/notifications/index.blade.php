@extends('layouts.app')
@section('title', __('messages.notifications'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.notifications') }}</h1>
    @if($notifications->contains(fn($n) => $n->read_at === null))
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline">{{ __('messages.mark_all_read') }}</button>
        </form>
    @endif
</div>

<div class="card">
    @if($notifications->count())
        <div class="notification-list">
            @foreach($notifications as $notification)
                <div class="notification-item {{ $notification->read_at ? '' : 'notification-unread' }}">
                    <div class="notification-icon-type">
                        @if($notification->type === 'task_assigned')
                            <span class="notif-icon notif-icon-task">&#128203;</span>
                        @elseif($notification->type === 'task_created')
                            <span class="notif-icon notif-icon-task">&#128196;</span>
                        @else
                            <span class="notif-icon notif-icon-report">&#128221;</span>
                        @endif
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">{{ $notification->title }}</div>
                        <div class="notification-message">{{ $notification->message }}</div>
                        <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="notification-actions">
                        @if(!$notification->read_at)
                            <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline">{{ $notification->link ? __('messages.view') : __('messages.mark_read') }}</button>
                            </form>
                        @elseif($notification->link)
                            <a href="{{ $notification->link }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="pagination-wrapper">{{ $notifications->links() }}</div>
    @else
        <div class="empty-state">
            <p>{{ __('messages.no_notifications') }}</p>
        </div>
    @endif
</div>
@endsection
