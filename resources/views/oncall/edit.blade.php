@extends('layouts.app')
@section('title', __('messages.oncall_edit'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.oncall_edit') }}</h1>
    <a href="{{ route('oncall.index') }}" class="btn btn-outline">{{ __('messages.oncall_back') }}</a>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <form method="POST" action="{{ route('oncall.update', $oncall) }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label for="date">{{ __('messages.date') }}</label>
            <input type="date" id="date" name="date" class="form-control" value="{{ old('date', $oncall->date->format('Y-m-d')) }}" required>
            @error('date') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>{{ __('messages.oncall_select_users') }}</label>
            <div class="oncall-user-selector">
                @foreach($users as $user)
                    <label class="oncall-user-checkbox">
                        <input type="checkbox" name="users[]" value="{{ $user->id }}"
                            {{ in_array($user->id, old('users', $selectedUsers)) ? 'checked' : '' }}>
                        <span class="oncall-user-label">
                            <span class="oncall-user-name">{{ $user->name }}</span>
                            <span class="oncall-user-email">{{ $user->email }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('users') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="pic_user_id">{{ __('messages.person_in_charge') }}</label>
            <select id="pic_user_id" name="pic_user_id" class="form-control">
                <option value="">-- {{ __('messages.select') }} --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('pic_user_id', $oncall->pic_user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <div class="form-hint">{{ __('messages.pic_hint') }}</div>
            @error('pic_user_id') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="notes">{{ __('messages.oncall_notes') }} <small>({{ __('messages.optional') }})</small></label>
            <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="{{ __('messages.oncall_notes_placeholder') }}">{{ old('notes', $oncall->notes) }}</textarea>
            @error('notes') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.oncall_update') }}</button>
            <a href="{{ route('oncall.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
