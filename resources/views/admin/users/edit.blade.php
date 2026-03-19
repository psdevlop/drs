@extends('layouts.app')
@section('title', __('messages.edit_user'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.edit_user') }}</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">{{ __('messages.back_to_users') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label for="name">{{ __('messages.full_name') }}</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            @error('name') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="email">{{ __('messages.email') }}</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            @error('email') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="role">{{ __('messages.role') }}</label>
            <select id="role" name="role" class="form-control">
                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>{{ __('messages.role_user') }}</option>
                @if(auth()->user()->isSuperAdmin())
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>{{ __('messages.role_admin') }}</option>
                    <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>{{ __('messages.role_super_admin') }}</option>
                @endif
            </select>
            @error('role') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">{{ __('messages.new_password') }} <small class="text-muted">({{ __('messages.leave_blank_no_change') }})</small></label>
                <input type="password" id="password" name="password" class="form-control">
                @error('password') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">{{ __('messages.confirm_password') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
            </div>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.update_user') }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
