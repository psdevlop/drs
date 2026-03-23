@extends('layouts.app')
@section('title', __('messages.new_on_call'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.new_on_call') }}</h1>
    <a href="{{ route('oncall.index') }}" class="btn btn-outline">{{ __('messages.oncall_back') }}</a>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <form method="POST" action="{{ route('oncall.store') }}">
        @csrf
        <div class="form-group">
            <label for="date">{{ __('messages.date') }}</label>
            <input type="date" id="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
            @error('date') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>{{ __('messages.oncall_select_users') }}</label>
            <div class="oncall-user-selector">
                @foreach($users as $user)
                    <label class="oncall-user-checkbox">
                        <input type="checkbox" name="users[]" value="{{ $user->id }}"
                            {{ in_array($user->id, old('users', [])) ? 'checked' : '' }}>
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
            <label for="notes">{{ __('messages.oncall_notes') }} <small>({{ __('messages.optional') }})</small></label>
            <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="{{ __('messages.oncall_notes_placeholder') }}">{{ old('notes') }}</textarea>
            @error('notes') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.oncall_save') }}</button>
            <a href="{{ route('oncall.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
