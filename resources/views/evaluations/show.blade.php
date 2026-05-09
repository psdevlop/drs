@extends('layouts.app')
@section('title', 'Evaluation')
@section('content')
@php
    $typeLabel = match($evaluation->type) {
        'self' => 'Self-Assessment',
        'peer' => 'Peer Review',
        'manager' => 'Manager Evaluation',
    };
@endphp
<div class="page-header">
    <h1>{{ $typeLabel }} — {{ $evaluation->evaluee->name }}</h1>
    <div class="actions">
        <a href="{{ url()->previous() }}" class="btn btn-outline">Back</a>
        @if($evaluation->evaluator_id === auth()->id())
            <a href="{{ route('evaluations.edit', $evaluation) }}" class="btn btn-primary">Edit</a>
        @endif
        @if($evaluation->evaluator_id === auth()->id() || auth()->user()->isAdmin())
            <form method="POST" action="{{ route('evaluations.destroy', $evaluation) }}" style="display:inline" onsubmit="return confirm('Delete this evaluation? This cannot be undone.');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <tr><th style="width:200px;">Evaluator</th><td>{{ $evaluation->evaluator->name }}</td></tr>
            <tr><th>Evaluee</th><td>{{ $evaluation->evaluee->name }} ({{ $evaluation->evaluee->internRoleLabel() }})</td></tr>
            <tr><th>Submitted</th><td>{{ $evaluation->submitted_at?->format('Y-m-d H:i') }}</td></tr>
            @if($evaluation->type === 'self')
                <tr><th>Self-Score</th><td><strong>{{ $evaluation->self_score }}</strong> / 5</td></tr>
            @endif
            @if($evaluation->type === 'peer')
                <tr><th>Frequency of Collaboration</th><td>{{ str_replace('_', ' ', $evaluation->frequency) }}</td></tr>
            @endif
            @if($evaluation->type === 'manager')
                <tr><th>Rehire Recommendation</th><td>{{ str_replace('_', ' ', ucwords($evaluation->rehire_recommendation, '_')) }}</td></tr>
                <tr><th>Salary Increase</th><td>{{ str_replace('_', '-', $evaluation->salary_increase) }}%</td></tr>
                <tr><th>Weighted Score</th><td><strong>{{ $evaluation->weightedScore() }}</strong> / 5</td></tr>
            @endif
            @if($evaluation->type === 'peer' && $evaluation->averageRating())
                <tr><th>Average Rating</th><td><strong>{{ $evaluation->averageRating() }}</strong> / 5</td></tr>
            @endif
        </table>
    </div>
</div>

@if(!empty($ratingItems) && is_array($evaluation->ratings))
    <div class="card" style="margin-top:1rem;">
        <div class="card-title">Ratings</div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        @if($evaluation->type === 'manager')<th>Weight</th>@endif
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ratingItems as $key => $value)
                        @php
                            $label = $evaluation->type === 'manager' ? $value[0] : $value;
                            $weight = $evaluation->type === 'manager' ? $value[1] : null;
                        @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            @if($weight !== null)<td>{{ $weight }}%</td>@endif
                            <td><strong>{{ $evaluation->ratings[$key] ?? '—' }}</strong> / 5</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if(is_array($evaluation->responses) && !empty($evaluation->responses))
    <div class="card" style="margin-top:1rem;">
        <div class="card-title">Written Responses</div>
        @foreach($evaluation->responses as $qkey => $answer)
            @if(!empty($answer))
                <div style="margin-bottom:1rem;">
                    <strong>{{ ucwords(str_replace('_', ' ', $qkey)) }}</strong>
                    <div style="white-space:pre-wrap;margin-top:.25rem;">{{ $answer }}</div>
                </div>
            @endif
        @endforeach
    </div>
@endif
@endsection
