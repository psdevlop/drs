@extends('layouts.app')
@section('title', __('messages.change_password'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.change_password') }}</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-outline">{{ __('messages.back_to_dashboard') }}</a>
</div>

<div class="card card-narrow">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <div class="form-group">
            <label for="current_password">{{ __('messages.current_password') }}</label>
            <input type="password" id="current_password" name="current_password" class="form-control" required>
            @error('current_password') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="password">{{ __('messages.new_password') }}</label>
            <input type="password" id="password" name="password" class="form-control" required>
            @error('password') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="password_confirmation">{{ __('messages.confirm_new_password') }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('messages.update_password') }}</button>
    </form>
</div>
@endsection
