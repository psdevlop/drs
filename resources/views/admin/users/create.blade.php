@extends('layouts.app')
@section('title', __('messages.create_user'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.create_user') }}</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">{{ __('messages.back_to_users') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('messages.full_name') }}</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="email">{{ __('messages.email') }}</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
            @error('email') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="role">{{ __('messages.role') }}</label>
            <select id="role" name="role" class="form-control">
                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>{{ __('messages.role_user') }}</option>
                @if(auth()->user()->isSuperAdmin())
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>{{ __('messages.role_admin') }}</option>
                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>{{ __('messages.role_super_admin') }}</option>
                @endif
            </select>
            @error('role') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="intern_role">Intern Role <small class="text-muted">(optional — adds user to evaluation cohort)</small></label>
            <select id="intern_role" name="intern_role" class="form-control">
                <option value="">— Not an intern —</option>
                @foreach(\App\Models\User::INTERN_ROLES as $val => $label)
                    <option value="{{ $val }}" {{ old('intern_role') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('intern_role') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">{{ __('messages.password') }}</label>
                <input type="password" id="password" name="password" class="form-control" required>
                @error('password') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">{{ __('messages.confirm_password') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
            </div>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.create_user') }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
