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
                <div class="detail-value password-field">
                    <code class="password-mask">••••••••</code>
                    <code class="password-value" style="display:none;">{{ $service->admin_password }}</code>
                    <button type="button" class="btn-eye" onclick="togglePassword(this)">
                        <svg class="eye-icon eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        <svg class="eye-icon eye-on" style="display:none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
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
                <div class="detail-value password-field">
                    <code class="password-mask">••••••••</code>
                    <code class="password-value" style="display:none;">{{ $service->test_password }}</code>
                    <button type="button" class="btn-eye" onclick="togglePassword(this)">
                        <svg class="eye-icon eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        <svg class="eye-icon eye-on" style="display:none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
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
<script>
function togglePassword(btn) {
    const mask = btn.parentElement.querySelector('.password-mask');
    const value = btn.parentElement.querySelector('.password-value');
    const eyeOff = btn.querySelector('.eye-off');
    const eyeOn = btn.querySelector('.eye-on');
    if (mask.style.display === 'none') {
        mask.style.display = '';
        value.style.display = 'none';
        eyeOff.style.display = '';
        eyeOn.style.display = 'none';
    } else {
        mask.style.display = 'none';
        value.style.display = '';
        eyeOff.style.display = 'none';
        eyeOn.style.display = '';
    }
}
</script>
@endsection
