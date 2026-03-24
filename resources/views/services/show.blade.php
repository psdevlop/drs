@extends('layouts.app')
@section('title', $service->name)
@section('content')
<div class="page-header">
    <h1>{{ $service->name }}</h1>
    <div class="actions">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-outline">{{ __('messages.edit') }}</a>
        @endif
        <a href="{{ route('services.index') }}" class="btn btn-outline">{{ __('messages.back_to_services') }}</a>
    </div>
</div>

<div class="card">
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">{{ __('messages.type') }}</div>
            <div class="detail-value"><span class="badge badge-service-{{ $service->type }}">{{ __('messages.service_' . $service->type) }}</span></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">{{ __('messages.status') }}</div>
            <div class="detail-value"><span class="badge badge-service-status-{{ $service->status }}">{{ __('messages.service_' . $service->status) }}</span></div>
        </div>
        @if($service->provider)
        <div class="detail-item">
            <div class="detail-label">{{ __('messages.provider') }}</div>
            <div class="detail-value">{{ $service->provider }}</div>
        </div>
        @endif
        @if($service->url)
        <div class="detail-item">
            <div class="detail-label">{{ __('messages.url') }}</div>
            <div class="detail-value"><a href="{{ $service->url }}" target="_blank">{{ $service->url }}</a></div>
        </div>
        @endif
        @if($service->type !== 'website')
            @if($service->registrant)
            <div class="detail-item">
                <div class="detail-label">{{ __('messages.registrant') }}</div>
                <div class="detail-value">{{ $service->registrant }}</div>
            </div>
            @endif
            @if($service->registrant_id)
            <div class="detail-item">
                <div class="detail-label">{{ __('messages.registrant_id') }}</div>
                <div class="detail-value">{{ $service->registrant_id }}</div>
            </div>
            @endif
        @endif
        @if($service->registration_date)
        <div class="detail-item">
            <div class="detail-label">{{ __('messages.registration_date') }}</div>
            <div class="detail-value">{{ $service->registration_date->format('M d, Y') }}</div>
        </div>
        @endif
        @if($service->expiration_date)
        <div class="detail-item">
            <div class="detail-label">{{ __('messages.expiration_date') }}</div>
            <div class="detail-value">
                <span class="{{ $service->isExpired() ? 'text-danger' : ($service->isExpiringSoon() ? 'text-warning' : '') }}">
                    {{ $service->expiration_date->format('M d, Y') }}
                </span>
                @if($service->isExpired())
                    <span class="text-danger text-sm">({{ __('messages.service_expired') }})</span>
                @elseif($service->isExpiringSoon())
                    <span class="text-warning text-sm">({{ __('messages.service_expiring_soon') }})</span>
                @endif
            </div>
        </div>
        @endif
        @if($service->type === 'website')
            @if($service->admin_id)
            <div class="detail-item">
                <div class="detail-label">{{ __('messages.admin_id') }}</div>
                <div class="detail-value">{{ $service->admin_id }}</div>
            </div>
            @endif
            @if($service->admin_password)
            <div class="detail-item">
                <div class="detail-label">{{ __('messages.admin_password') }}</div>
                <div class="detail-value"><code>{{ $service->admin_password }}</code></div>
            </div>
            @endif
            @if($service->test_id)
            <div class="detail-item">
                <div class="detail-label">{{ __('messages.test_id') }}</div>
                <div class="detail-value">{{ $service->test_id }}</div>
            </div>
            @endif
            @if($service->test_password)
            <div class="detail-item">
                <div class="detail-label">{{ __('messages.test_password') }}</div>
                <div class="detail-value"><code>{{ $service->test_password }}</code></div>
            </div>
            @endif
        @endif
        <div class="detail-item">
            <div class="detail-label">{{ __('messages.created_by') }}</div>
            <div class="detail-value">{{ $service->creator->name }}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">{{ __('messages.created_at') }}</div>
            <div class="detail-value">{{ $service->created_at->format('M d, Y H:i') }}</div>
        </div>
    </div>
    @if($service->notes)
    <div class="detail-section">
        <div class="detail-label">{{ __('messages.notes') }}</div>
        <div class="detail-content">{{ $service->notes }}</div>
    </div>
    @endif
</div>
@endsection
