@extends('layouts.app')
@section('title', ($mode ?? 'create') === 'edit' ? __('messages.edit_manager_evaluation') : __('messages.manager_evaluation'))
@section('content')
@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $action = $isEdit ? route('evaluations.update', $evaluation) : route('evaluations.store', ['manager', $user]);
    $r = $evaluation?->responses ?? [];
    $ratings = $evaluation?->ratings ?? [];
@endphp
<div class="page-header">
    <h1>{{ $isEdit ? __('messages.edit_manager_evaluation') : __('messages.manager_evaluation') }} — {{ $user->name }}</h1>
    <a href="{{ route('evaluations.index') }}" class="btn btn-outline">{{ __('messages.back') }}</a>
</div>

<div class="card">
    <p class="text-muted">{{ __('messages.eval_manager_desc') }}</p>
    <p class="text-muted text-xs">{{ __('messages.score_scale_hint') }}</p>

    @include('evaluations.partials.score-picker-assets')

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="form-group">
            <label>{{ __('messages.intern_name') }}</label>
            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
        </div>
        <div class="form-group">
            <label>{{ __('messages.role') }}</label>
            <input type="text" class="form-control" value="{{ $user->internRoleLabel() }}" disabled>
        </div>
        <div class="form-group">
            <label>{{ __('messages.evaluator') }} ({{ __('messages.manager_evaluation') }})</label>
            <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
        </div>
        <div class="form-group">
            <label>{{ __('messages.evaluation_date') }}</label>
            <input type="text" class="form-control" value="{{ now()->format('Y-m-d') }}" disabled>
        </div>

        <h3>{{ __('messages.evaluation_items_total') }}</h3>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>{{ __('messages.item') }}</th><th>{{ __('messages.weight') }}</th><th>{{ __('messages.score_1_5') }}</th></tr></thead>
                <tbody>
                    @foreach($ratingItems as $key => [$label, $weight])
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $weight }}%</td>
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

        <div class="form-group">
            <label for="key_achievements">{{ __('messages.key_achievements') }}</label>
            <div class="text-muted text-xs">{{ __('messages.key_achievements_hint') }}</div>
            <textarea id="key_achievements" name="key_achievements" class="form-control" rows="4" required>{{ old('key_achievements', $r['key_achievements'] ?? '') }}</textarea>
            @error('key_achievements') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="areas_for_improvement">{{ __('messages.areas_for_improvement') }}</label>
            <div class="text-muted text-xs">{{ __('messages.areas_for_improvement_hint') }}</div>
            <textarea id="areas_for_improvement" name="areas_for_improvement" class="form-control" rows="4" required>{{ old('areas_for_improvement', $r['areas_for_improvement'] ?? '') }}</textarea>
            @error('areas_for_improvement') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>{{ __('messages.rehire_recommendation') }}</label>
            @foreach(\App\Models\Evaluation::rehireRecommendationLabels() as $val => $label)
                <label style="display:block;font-weight:normal;">
                    <input type="radio" name="rehire_recommendation" value="{{ $val }}" {{ old('rehire_recommendation', $evaluation?->rehire_recommendation) === $val ? 'checked' : '' }} required> {{ $label }}
                </label>
            @endforeach
            @error('rehire_recommendation') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>{{ __('messages.recommended_salary_increase') }}</label>
            @foreach(\App\Models\Evaluation::salaryIncreaseLabels() as $val => $label)
                <label style="margin-right:1rem;font-weight:normal;">
                    <input type="radio" name="salary_increase" value="{{ $val }}" {{ old('salary_increase', $evaluation?->salary_increase) === $val ? 'checked' : '' }} required> {{ $label }}
                </label>
            @endforeach
            @error('salary_increase') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">{{ $isEdit ? __('messages.save_changes') : __('messages.submit_manager_evaluation') }}</button>
            <a href="{{ route('evaluations.index') }}" class="btn btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
