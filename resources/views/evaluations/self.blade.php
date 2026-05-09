@extends('layouts.app')
@section('title', ($mode ?? 'create') === 'edit' ? 'Edit Self-Assessment' : 'Self-Assessment')
@section('content')
@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $action = $isEdit ? route('evaluations.update', $evaluation) : route('evaluations.store', ['self', $user]);
    $r = $evaluation?->responses ?? [];
@endphp
<div class="page-header">
    <h1>{{ $isEdit ? 'Edit Self-Assessment' : 'Self-Assessment' }}</h1>
    <a href="{{ route('evaluations.index') }}" class="btn btn-outline">Back</a>
</div>

<div class="card">
    <p class="text-muted">This assessment is meant to gauge your self-awareness and growth mindset, not to grade you. Honest answers will lead to more useful feedback.</p>

    @include('evaluations.partials.score-picker-assets')

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="form-group">
            <label>Name</label>
            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
        </div>
        <div class="form-group">
            <label>Role</label>
            <input type="text" class="form-control" value="{{ $user->internRoleLabel() }}" disabled>
        </div>
        <div class="form-group">
            <label>Date</label>
            <input type="text" class="form-control" value="{{ now()->format('Y-m-d') }}" disabled>
        </div>

        <div class="form-group">
            <label for="self_score">Self-Score (1.0 - 5.0)</label>
            <div class="text-muted text-xs">5 = Outstanding · 4 = Exceeds · 3 = Meets · 2 = Below · 1 = Poor</div>
            @include('evaluations.partials.score-picker', [
                'name' => 'self_score',
                'value' => old('self_score', $evaluation?->self_score),
                'width' => '140px',
            ])
            @error('self_score') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="accomplishments">Q1. What are 1-3 accomplishments you are most proud of?</label>
            <div class="text-muted text-xs">What did you do, how did you do it, and what changed as a result?</div>
            <textarea id="accomplishments" name="accomplishments" class="form-control" rows="4" required>{{ old('accomplishments', $r['accomplishments'] ?? '') }}</textarea>
            @error('accomplishments') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="challenge">Q2. What was the most difficult challenge, and how did you handle it?</label>
            <textarea id="challenge" name="challenge" class="form-control" rows="4" required>{{ old('challenge', $r['challenge'] ?? '') }}</textarea>
            @error('challenge') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="growth">Q3. Growth — which skill grew the most compared to your start?</label>
            <textarea id="growth" name="growth" class="form-control" rows="3" required>{{ old('growth', $r['growth'] ?? '') }}</textarea>
            @error('growth') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="improvement_plan">Q4. What areas do you most want to improve, with a specific plan?</label>
            <textarea id="improvement_plan" name="improvement_plan" class="form-control" rows="3" required>{{ old('improvement_plan', $r['improvement_plan'] ?? '') }}</textarea>
            @error('improvement_plan') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="future_contribution">Q5. If converted to full-time, what value would you bring to the team?</label>
            <textarea id="future_contribution" name="future_contribution" class="form-control" rows="3" required>{{ old('future_contribution', $r['future_contribution'] ?? '') }}</textarea>
            @error('future_contribution') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">{{ $isEdit ? 'Save Changes' : 'Submit Self-Assessment' }}</button>
            <a href="{{ route('evaluations.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
