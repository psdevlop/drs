@extends('layouts.app')
@section('title', __('messages.edit_service'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.edit_service') }}</h1>
    <a href="{{ route('admin.services.index') }}" class="btn btn-outline">{{ __('messages.back_to_services') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.services.update', $service) }}">
        @csrf @method('PUT')
        <div class="form-row">
            <div class="form-group">
                <label for="name">{{ __('messages.name') }}</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $service->name) }}" required>
                @error('name') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="type">{{ __('messages.type') }}</label>
                <select id="type" name="type" class="form-control">
                    <option value="domain" {{ old('type', $service->type) == 'domain' ? 'selected' : '' }}>{{ __('messages.service_domain') }}</option>
                    <option value="hosting" {{ old('type', $service->type) == 'hosting' ? 'selected' : '' }}>{{ __('messages.service_hosting') }}</option>
                    <option value="cdn" {{ old('type', $service->type) == 'cdn' ? 'selected' : '' }}>{{ __('messages.service_cdn') }}</option>
                    <option value="website" {{ old('type', $service->type) == 'website' ? 'selected' : '' }}>{{ __('messages.service_website') }}</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="provider">{{ __('messages.provider') }}</label>
                <input type="text" id="provider" name="provider" class="form-control" value="{{ old('provider', $service->provider) }}" placeholder="{{ __('messages.provider_placeholder') }}">
                @error('provider') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="url">{{ __('messages.url') }}</label>
                <input type="text" id="url" name="url" class="form-control" value="{{ old('url', $service->url) }}" placeholder="https://example.com">
                @error('url') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>
        <div id="registrant-fields" style="display:{{ old('type', $service->type) == 'website' ? 'none' : 'block' }};">
            <div class="form-row">
                <div class="form-group">
                    <label for="registrant">{{ __('messages.registrant') }}</label>
                    <input type="text" id="registrant" name="registrant" class="form-control" value="{{ old('registrant', $service->registrant) }}">
                    @error('registrant') <div class="error-text">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="registrant_id">{{ __('messages.registrant_id') }}</label>
                    <input type="text" id="registrant_id" name="registrant_id" class="form-control" value="{{ old('registrant_id', $service->registrant_id) }}">
                    @error('registrant_id') <div class="error-text">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="registration_date">{{ __('messages.registration_date') }}</label>
                <input type="date" id="registration_date" name="registration_date" class="form-control" value="{{ old('registration_date', $service->registration_date?->format('Y-m-d')) }}">
                @error('registration_date') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="expiration_date">{{ __('messages.expiration_date') }}</label>
                <input type="date" id="expiration_date" name="expiration_date" class="form-control" value="{{ old('expiration_date', $service->expiration_date?->format('Y-m-d')) }}">
                @error('expiration_date') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>
        <div id="website-credentials" style="display:{{ old('type', $service->type) == 'website' ? 'block' : 'none' }};">
            <div class="form-row">
                <div class="form-group">
                    <label for="admin_id">{{ __('messages.admin_id') }}</label>
                    <input type="text" id="admin_id" name="admin_id" class="form-control" value="{{ old('admin_id', $service->admin_id) }}">
                    @error('admin_id') <div class="error-text">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="admin_password">{{ __('messages.admin_password') }}</label>
                    <input type="text" id="admin_password" name="admin_password" class="form-control" value="{{ old('admin_password', $service->admin_password) }}">
                    @error('admin_password') <div class="error-text">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="test_id">{{ __('messages.test_id') }}</label>
                    <input type="text" id="test_id" name="test_id" class="form-control" value="{{ old('test_id', $service->test_id) }}">
                    @error('test_id') <div class="error-text">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="test_password">{{ __('messages.test_password') }}</label>
                    <input type="text" id="test_password" name="test_password" class="form-control" value="{{ old('test_password', $service->test_password) }}">
                    @error('test_password') <div class="error-text">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="status">{{ __('messages.status') }}</label>
            <select id="status" name="status" class="form-control">
                <option value="active" {{ old('status', $service->status) == 'active' ? 'selected' : '' }}>{{ __('messages.service_active') }}</option>
                <option value="pending" {{ old('status', $service->status) == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                <option value="expired" {{ old('status', $service->status) == 'expired' ? 'selected' : '' }}>{{ __('messages.service_expired') }}</option>
                <option value="suspended" {{ old('status', $service->status) == 'suspended' ? 'selected' : '' }}>{{ __('messages.service_suspended') }}</option>
            </select>
        </div>
        <div class="form-group">
            <label for="notes">{{ __('messages.notes') }}</label>
            <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $service->notes) }}</textarea>
            @error('notes') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.update_service') }}</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
<script>
document.getElementById('type').addEventListener('change', function() {
    var isWebsite = this.value === 'website';
    document.getElementById('website-credentials').style.display = isWebsite ? 'block' : 'none';
    document.getElementById('registrant-fields').style.display = isWebsite ? 'none' : 'block';
});
</script>
@endsection
