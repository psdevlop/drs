@extends('layouts.app')
@section('title', __('messages.rotation_new'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.rotation_new') }}</h1>
    <a href="{{ route('oncall.index') }}" class="btn btn-outline">{{ __('messages.oncall_back') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('oncall.rotations.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('messages.name') }}</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="{{ __('messages.rotation_name_placeholder') }}">
            @error('name') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="cycle_type">{{ __('messages.rotation_cycle_type') }}</label>
                <select id="cycle_type" name="cycle_type" class="form-control">
                    <option value="daily" {{ old('cycle_type') == 'daily' ? 'selected' : '' }}>{{ __('messages.rotation_daily') }}</option>
                    <option value="weekly" {{ old('cycle_type') == 'weekly' ? 'selected' : '' }}>{{ __('messages.rotation_weekly') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="cycle_length">{{ __('messages.rotation_cycle_length') }}</label>
                <input type="number" id="cycle_length" name="cycle_length" class="form-control" value="{{ old('cycle_length', 1) }}" min="1" max="30" required>
                <div class="form-hint">{{ __('messages.rotation_cycle_hint') }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_date">{{ __('messages.start_date') }}</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                @error('start_date') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="end_date">{{ __('messages.end_date') }}</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                <div class="form-hint">{{ __('messages.rotation_end_hint') }}</div>
                @error('end_date') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label>{{ __('messages.rotation_users_order') }}</label>
            <div class="form-hint">{{ __('messages.rotation_drag_hint') }}</div>
            <div id="rotation-users" class="rotation-user-list">
                @foreach($users as $user)
                    <label class="rotation-user-item">
                        <input type="checkbox" name="users[]" value="{{ $user->id }}" {{ in_array($user->id, old('users', [])) ? 'checked' : '' }}>
                        <span class="rotation-user-name">{{ $user->name }} ({{ $user->email }})</span>
                        <span class="rotation-order-badge"></span>
                    </label>
                @endforeach
            </div>
            @error('users') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="notes">{{ __('messages.oncall_notes') }}</label>
            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="{{ __('messages.oncall_notes_placeholder') }}">{{ old('notes') }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.rotation_create') }}</button>
            <a href="{{ route('oncall.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('rotation-users');
    function updateOrder() {
        let order = 1;
        container.querySelectorAll('.rotation-user-item input:checked').forEach(function(cb) {
            cb.closest('.rotation-user-item').querySelector('.rotation-order-badge').textContent = order++;
        });
        container.querySelectorAll('.rotation-user-item input:not(:checked)').forEach(function(cb) {
            cb.closest('.rotation-user-item').querySelector('.rotation-order-badge').textContent = '';
        });
    }
    container.addEventListener('change', updateOrder);
    updateOrder();
});
</script>
@endsection
