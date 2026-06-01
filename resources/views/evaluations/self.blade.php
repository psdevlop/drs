@extends('layouts.app')
@section('title', ($mode ?? 'create') === 'edit' ? __('messages.edit_self_assessment') : __('messages.self_assessment'))
@section('content')
@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $action = $isEdit ? route('evaluations.update', $evaluation) : route('evaluations.store', ['self', $user]);
    $r = $evaluation?->responses ?? [];
@endphp
<div class="page-header">
    <h1>{{ $isEdit ? __('messages.edit_self_assessment') : __('messages.self_assessment') }}</h1>
    <a href="{{ route('evaluations.index') }}" class="btn btn-outline">{{ __('messages.back') }}</a>
</div>

<div class="card">
    <p class="text-muted">{{ __('messages.eval_self_desc') }}</p>

    @include('evaluations.partials.score-picker-assets')

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="form-group">
            <label>{{ __('messages.name') }}</label>
            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
        </div>
        <div class="form-group">
            <label>{{ __('messages.role') }}</label>
            <input type="text" class="form-control" value="{{ $user->internRoleLabel() }}" disabled>
        </div>
        <div class="form-group">
            <label>{{ __('messages.date') }}</label>
            <input type="text" class="form-control" value="{{ now()->format('Y-m-d') }}" disabled>
        </div>

        <div class="form-group">
            <label for="self_score">{{ __('messages.self_score') }} (1.0 - 5.0)</label>
            <div class="text-muted text-xs">{{ __('messages.score_scale_hint') }}</div>
            @include('evaluations.partials.score-picker', [
                'name' => 'self_score',
                'value' => old('self_score', $evaluation?->self_score),
                'width' => '140px',
            ])
            @error('self_score') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="accomplishments">{{ __('messages.eval_q_accomplishments') }}</label>
            <div class="text-muted text-xs">{{ __('messages.eval_q_accomplishments_hint') }}</div>
            <textarea id="accomplishments" name="accomplishments" class="form-control" rows="4" required>{{ old('accomplishments', $r['accomplishments'] ?? '') }}</textarea>
            @error('accomplishments') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="challenge">{{ __('messages.eval_q_challenge') }}</label>
            <textarea id="challenge" name="challenge" class="form-control" rows="4" required>{{ old('challenge', $r['challenge'] ?? '') }}</textarea>
            @error('challenge') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="growth">{{ __('messages.eval_q_growth') }}</label>
            <textarea id="growth" name="growth" class="form-control" rows="3" required>{{ old('growth', $r['growth'] ?? '') }}</textarea>
            @error('growth') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="improvement_plan">{{ __('messages.eval_q_improvement_plan') }}</label>
            <textarea id="improvement_plan" name="improvement_plan" class="form-control" rows="3" required>{{ old('improvement_plan', $r['improvement_plan'] ?? '') }}</textarea>
            @error('improvement_plan') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="future_contribution">{{ __('messages.eval_q_future_contribution') }}</label>
            <textarea id="future_contribution" name="future_contribution" class="form-control" rows="3" required>{{ old('future_contribution', $r['future_contribution'] ?? '') }}</textarea>
            @error('future_contribution') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">{{ $isEdit ? __('messages.save_changes') : __('messages.submit_self_assessment') }}</button>
            <a href="{{ route('evaluations.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
