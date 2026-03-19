@extends('layouts.app')
@section('title', __('messages.edit_profile'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.edit_profile') }}</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-outline">{{ __('messages.back_to_dashboard') }}</a>
</div>

<div class="profile-grid">
    <div class="card card-center">
        <div class="profile-avatar-section">
            @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="profile-avatar">
            @else
                <div class="profile-avatar-placeholder">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
        </div>
        <h3 class="profile-name">{{ $user->name }}</h3>
        <p class="profile-email">{{ $user->email }}</p>
        <span class="badge badge-{{ $user->role }} profile-role">{{ ucfirst($user->role) }}</span>
        @if($user->avatar)
            <form action="{{ route('profile.remove-avatar') }}" method="POST" class="profile-remove-form" onsubmit="return confirm('{{ __('messages.remove_avatar_confirm') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.remove_avatar') }}</button>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="card-title">{{ __('messages.profile_information') }}</div>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="form-group">
                <label for="avatar">{{ __('messages.avatar') }}</label>
                <input type="file" id="avatar" name="avatar" class="form-control form-control-file" accept="image/*">
                <div class="form-hint">{{ __('messages.avatar_hint') }}</div>
                @error('avatar') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="name">{{ __('messages.name') }}</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                @error('name') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="email">{{ __('messages.email') }}</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                @error('email') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">{{ __('messages.save_changes') }}</button>
        </form>
    </div>
</div>
@endsection
