@extends('layouts.app')
@section('title', ($mode ?? 'create') === 'edit' ? 'Edit Manager Evaluation' : 'Manager Evaluation')
@section('content')
@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $action = $isEdit ? route('evaluations.update', $evaluation) : route('evaluations.store', ['manager', $user]);
    $r = $evaluation?->responses ?? [];
    $ratings = $evaluation?->ratings ?? [];
@endphp
<div class="page-header">
    <h1>{{ $isEdit ? 'Edit Manager Evaluation' : 'Manager Evaluation' }} — {{ $user->name }}</h1>
    <a href="{{ route('evaluations.index') }}" class="btn btn-outline">Back</a>
</div>

<div class="card">
    <p class="text-muted">This form is the primary basis for compensation and rehire decisions. Use the 5-point scale and always include <strong>specific behavioral examples</strong>.</p>
    <p class="text-muted text-xs">5 = Outstanding · 4 = Exceeds · 3 = Meets · 2 = Below · 1 = Poor</p>

    @include('evaluations.partials.score-picker-assets')

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="form-group">
            <label>Intern Name</label>
            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
        </div>
        <div class="form-group">
            <label>Role</label>
            <input type="text" class="form-control" value="{{ $user->internRoleLabel() }}" disabled>
        </div>
        <div class="form-group">
            <label>Evaluator (Manager)</label>
            <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
        </div>
        <div class="form-group">
            <label>Evaluation Date</label>
            <input type="text" class="form-control" value="{{ now()->format('Y-m-d') }}" disabled>
        </div>

        <h3>Evaluation Items (Total: 100%)</h3>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Item</th><th>Weight</th><th>Score (1-5)</th></tr></thead>
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
            <label for="key_achievements">Key Achievements</label>
            <div class="text-muted text-xs">Describe 2-3 specific accomplishments during the internship.</div>
            <textarea id="key_achievements" name="key_achievements" class="form-control" rows="4" required>{{ old('key_achievements', $r['key_achievements'] ?? '') }}</textarea>
            @error('key_achievements') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="areas_for_improvement">Areas for Improvement</label>
            <div class="text-muted text-xs">Describe areas needing improvement, with specific supporting examples.</div>
            <textarea id="areas_for_improvement" name="areas_for_improvement" class="form-control" rows="4" required>{{ old('areas_for_improvement', $r['areas_for_improvement'] ?? '') }}</textarea>
            @error('areas_for_improvement') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Rehire Recommendation</label>
            @foreach([
                'strongly_recommend' => 'Strongly Recommend',
                'recommend' => 'Recommend',
                'conditional' => 'Conditional',
                'do_not_recommend' => 'Do Not Recommend',
            ] as $val => $label)
                <label style="display:block;font-weight:normal;">
                    <input type="radio" name="rehire_recommendation" value="{{ $val }}" {{ old('rehire_recommendation', $evaluation?->rehire_recommendation) === $val ? 'checked' : '' }} required> {{ $label }}
                </label>
            @endforeach
            @error('rehire_recommendation') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Recommended Salary Increase</label>
            @foreach([
                '0' => '0%',
                'under_5' => 'Under 5%',
                '5_10' => '5-10%',
                '10_15' => '10-15%',
                'over_15' => 'Over 15%',
            ] as $val => $label)
                <label style="margin-right:1rem;font-weight:normal;">
                    <input type="radio" name="salary_increase" value="{{ $val }}" {{ old('salary_increase', $evaluation?->salary_increase) === $val ? 'checked' : '' }} required> {{ $label }}
                </label>
            @endforeach
            @error('salary_increase') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">{{ $isEdit ? 'Save Changes' : 'Submit Manager Evaluation' }}</button>
            <a href="{{ route('evaluations.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
