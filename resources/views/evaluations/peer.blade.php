@extends('layouts.app')
@section('title', ($mode ?? 'create') === 'edit' ? __('messages.edit_peer_review') : __('messages.peer_review'))
@section('content')
@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $action = $isEdit ? route('evaluations.update', $evaluation) : route('evaluations.store', ['peer', $user]);
    $r = $evaluation?->responses ?? [];
    $ratings = $evaluation?->ratings ?? [];
@endphp
<div class="page-header">
    <h1>{{ $isEdit ? __('messages.edit_peer_review') : __('messages.peer_review') }} — {{ $user->name }}</h1>
    <a href="{{ route('evaluations.index') }}" class="btn btn-outline">{{ __('messages.back') }}</a>
</div>

<div class="card">
    <p class="text-muted">{{ __('messages.eval_peer_desc') }}</p>
    <p class="text-muted text-xs">⚠ {{ __('messages.eval_peer_privacy_desc') }}</p>

    @include('evaluations.partials.score-picker-assets')

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="form-group">
            <label>{{ __('messages.reviewer_you') }}</label>
            <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
        </div>
        <div class="form-group">
            <label>{{ __('messages.reviewee') }}</label>
            <input type="text" class="form-control" value="{{ $user->name }} — {{ $user->internRoleLabel() }}" disabled>
        </div>

        <div class="form-group">
            <label>{{ __('messages.frequency_of_collaboration') }}</label>
            @foreach(\App\Models\Evaluation::frequencyLabels() as $val => $label)
                <label style="margin-right:1rem;font-weight:normal;">
                    <input type="radio" name="frequency" value="{{ $val }}" {{ old('frequency', $evaluation?->frequency) === $val ? 'checked' : '' }} required> {{ $label }}
                </label>
            @endforeach
            @error('frequency') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <h3>{{ __('messages.numerical_ratings') }}</h3>
        <div class="text-muted text-xs" style="margin-bottom:.5rem;">{{ __('messages.score_scale_hint') }}</div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>{{ __('messages.item') }}</th><th>{{ __('messages.score_1_5') }}</th></tr></thead>
                <tbody>
                    @foreach($ratingItems as $key => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>
                                @include('evaluations.partials.score-picker', [
                                    'name' => 'ratings[' . $key . ']',
                                    'value' => old('ratings.' . $key, $ratings[$key] ?? ''),
                                ])
                                @error('ratings.' . $key) <div class="error-text">{{ $message }}</div> @enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h3>{{ __('messages.written_evaluation_most_important') }}</h3>

        <div class="form-group">
            <label for="strengths">{{ __('messages.eval_q_strengths') }}</label>
            <textarea id="strengths" name="strengths" class="form-control" rows="4" required>{{ old('strengths', $r['strengths'] ?? '') }}</textarea>
            @error('strengths') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="growth_areas">{{ __('messages.eval_q_growth_areas') }}</label>
            <textarea id="growth_areas" name="growth_areas" class="form-control" rows="4" required>{{ old('growth_areas', $r['growth_areas'] ?? '') }}</textarea>
            @error('growth_areas') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="recollaborate">{{ __('messages.eval_q_recollaborate') }}</label>
            <textarea id="recollaborate" name="recollaborate" class="form-control" rows="3" required>{{ old('recollaborate', $r['recollaborate'] ?? '') }}</textarea>
            @error('recollaborate') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="manager_only_comments">{{ __('messages.eval_q_manager_only_comments') }}</label>
            <textarea id="manager_only_comments" name="manager_only_comments" class="form-control" rows="3">{{ old('manager_only_comments', $r['manager_only_comments'] ?? '') }}</textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">{{ $isEdit ? __('messages.save_changes') : __('messages.submit_peer_review') }}</button>
            <a href="{{ route('evaluations.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
