@extends('layouts.app')
@section('title', ($mode ?? 'create') === 'edit' ? 'Edit Peer Review' : 'Peer Review')
@section('content')
@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $action = $isEdit ? route('evaluations.update', $evaluation) : route('evaluations.store', ['peer', $user]);
    $r = $evaluation?->responses ?? [];
    $ratings = $evaluation?->ratings ?? [];
@endphp
<div class="page-header">
    <h1>{{ $isEdit ? 'Edit Peer Review' : 'Peer Review' }} — {{ $user->name }}</h1>
    <a href="{{ route('evaluations.index') }}" class="btn btn-outline">Back</a>
</div>

<div class="card">
    <p class="text-muted">This is a reference input for the manager's final decision. <strong>Specific behavioral examples</strong> matter much more than scores. Please base your review on actual observed behavior.</p>
    <p class="text-muted text-xs">⚠ Your name (the reviewer) is shared only with the manager. The intern being reviewed will only see consolidated, anonymized feedback.</p>

    @include('evaluations.partials.score-picker-assets')

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="form-group">
            <label>Reviewer (You)</label>
            <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
        </div>
        <div class="form-group">
            <label>Reviewee</label>
            <input type="text" class="form-control" value="{{ $user->name }} — {{ $user->internRoleLabel() }}" disabled>
        </div>

        <div class="form-group">
            <label>Frequency of Collaboration</label>
            @foreach(['daily' => 'Daily', '2-3x_week' => '2-3x/week', 'weekly' => 'Weekly', 'rarely' => 'Rarely'] as $val => $label)
                <label style="margin-right:1rem;font-weight:normal;">
                    <input type="radio" name="frequency" value="{{ $val }}" {{ old('frequency', $evaluation?->frequency) === $val ? 'checked' : '' }} required> {{ $label }}
                </label>
            @endforeach
            @error('frequency') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <h3>Numerical Ratings</h3>
        <div class="text-muted text-xs" style="margin-bottom:.5rem;">5 = Outstanding · 4 = Exceeds · 3 = Meets · 2 = Below · 1 = Poor</div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Item</th><th>Score (1-5)</th></tr></thead>
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

        <h3>Written Evaluation (Most Important)</h3>

        <div class="form-group">
            <label for="strengths">Q1. What is this teammate's greatest strength? Provide specific examples.</label>
            <textarea id="strengths" name="strengths" class="form-control" rows="4" required>{{ old('strengths', $r['strengths'] ?? '') }}</textarea>
            @error('strengths') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="growth_areas">Q2. Where does this teammate have the most room to grow? Provide specific examples.</label>
            <textarea id="growth_areas" name="growth_areas" class="form-control" rows="4" required>{{ old('growth_areas', $r['growth_areas'] ?? '') }}</textarea>
            @error('growth_areas') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="recollaborate">Q3. Would you want to work with this teammate again? Why or why not?</label>
            <textarea id="recollaborate" name="recollaborate" class="form-control" rows="3" required>{{ old('recollaborate', $r['recollaborate'] ?? '') }}</textarea>
            @error('recollaborate') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="manager_only_comments">Q4. (Optional) Additional comments for the manager only</label>
            <textarea id="manager_only_comments" name="manager_only_comments" class="form-control" rows="3">{{ old('manager_only_comments', $r['manager_only_comments'] ?? '') }}</textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">{{ $isEdit ? 'Save Changes' : 'Submit Peer Review' }}</button>
            <a href="{{ route('evaluations.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
