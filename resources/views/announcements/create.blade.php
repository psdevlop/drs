@extends('layouts.app')
@section('title', __('messages.new_announcement'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.new_announcement') }}</h1>
    <a href="{{ route('announcements.index') }}" class="btn btn-outline">{{ __('messages.back_to_announcements') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('announcements.store') }}">
        @csrf
        <div class="form-group">
            <label for="title">{{ __('messages.title') }}</label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required>
            @error('title') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="content">{{ __('messages.content') }}</label>
            <textarea id="content" name="content" class="form-control" rows="8" required>{{ old('content') }}</textarea>
            @error('content') <div class="error-text">{{ $message }}</div> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="priority">{{ __('messages.priority') }}</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>{{ __('messages.priority_normal') }}</option>
                    <option value="important" {{ old('priority') == 'important' ? 'selected' : '' }}>{{ __('messages.priority_important') }}</option>
                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>{{ __('messages.priority_urgent') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }}>
                    {{ __('messages.pin_announcement') }}
                </label>
            </div>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.post_announcement') }}</button>
            <a href="{{ route('announcements.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
