@extends('layouts.app')
@section('title', __('messages.evaluation'))
@section('content')
@php
    $typeLabel = match($evaluation->type) {
        'self' => __('messages.self_assessment'),
        'peer' => __('messages.peer_review'),
        'manager' => __('messages.manager_evaluation'),
    };
    $frequencyLabels = \App\Models\Evaluation::frequencyLabels();
    $rehireLabels = \App\Models\Evaluation::rehireRecommendationLabels();
    $salaryLabels = \App\Models\Evaluation::salaryIncreaseLabels();
@endphp
<div class="page-header">
    <h1>{{ $typeLabel }} — {{ $evaluation->evaluee->name }}</h1>
    <div class="actions">
        <a href="{{ url()->previous() }}" class="btn btn-outline">{{ __('messages.back') }}</a>
        @if($evaluation->evaluator_id === auth()->id() || auth()->user()->isSuperAdmin())
            <a href="{{ route('evaluations.edit', $evaluation) }}" class="btn btn-primary">{{ __('messages.edit') }}</a>
        @endif
        @if(auth()->user()->isAdmin() && !$evaluation->isConfirmed())
            <form method="POST" action="{{ route('evaluations.confirm', $evaluation) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-success">{{ __('messages.confirm') }}</button>
            </form>
        @endif
        @if($evaluation->evaluator_id === auth()->id() || auth()->user()->isAdmin())
            <form method="POST" action="{{ route('evaluations.destroy', $evaluation) }}" style="display:inline" onsubmit="return confirm('{{ __('messages.delete_evaluation_confirm') }}');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('messages.delete') }}</button>
            </form>
        @endif
    </div>
</div>

@if($evaluation->isConfirmed())
    <div class="alert alert-info" style="background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;padding:10px 14px;border-radius:8px;margin-bottom:14px;">
        ✓ {{ __('messages.confirmed_by_on', ['name' => $evaluation->confirmedBy->name ?? __('messages.admin'), 'date' => $evaluation->confirmed_at->format('Y-m-d H:i')]) }}
    </div>
@endif

<div class="card">
    <div class="table-wrapper">
        <table>
            <tr><th style="width:200px;">{{ __('messages.evaluator') }}</th><td>{{ $evaluation->evaluator->name }}</td></tr>
            <tr><th>{{ __('messages.evaluee') }}</th><td>{{ $evaluation->evaluee->name }} ({{ $evaluation->evaluee->internRoleLabel() }})</td></tr>
            <tr><th>{{ __('messages.submitted') }}</th><td>{{ $evaluation->submitted_at?->format('Y-m-d H:i') }}</td></tr>
            @if($evaluation->type === 'self')
                <tr><th>{{ __('messages.self_score') }}</th><td><strong>{{ $evaluation->self_score }}</strong> / 5</td></tr>
            @endif
            @if($evaluation->type === 'peer')
                <tr><th>{{ __('messages.frequency_of_collaboration') }}</th><td>{{ $frequencyLabels[$evaluation->frequency] ?? $evaluation->frequency }}</td></tr>
            @endif
            @if($evaluation->type === 'manager')
                <tr><th>{{ __('messages.rehire_recommendation') }}</th><td>{{ $rehireLabels[$evaluation->rehire_recommendation] ?? $evaluation->rehire_recommendation }}</td></tr>
                <tr><th>{{ __('messages.salary_increase') }}</th><td>{{ $salaryLabels[$evaluation->salary_increase] ?? $evaluation->salary_increase }}</td></tr>
                <tr><th>{{ __('messages.weighted_score') }}</th><td><strong>{{ $evaluation->weightedScore() }}</strong> / 5</td></tr>
            @endif
            @if($evaluation->type === 'peer' && $evaluation->averageRating())
                <tr><th>{{ __('messages.average_rating') }}</th><td><strong>{{ $evaluation->averageRating() }}</strong> / 5</td></tr>
            @endif
        </table>
    </div>
</div>

@if(!empty($ratingItems) && is_array($evaluation->ratings))
    <div class="card" style="margin-top:1rem;">
        <div class="card-title">{{ __('messages.ratings') }}</div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.item') }}</th>
                        @if($evaluation->type === 'manager')<th>{{ __('messages.weight') }}</th>@endif
                        <th>{{ __('messages.score') }}</th>
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
        <div class="card-title">{{ __('messages.written_responses') }}</div>
        @foreach($evaluation->responses as $qkey => $answer)
            @if(!empty($answer))
                <div style="margin-bottom:1rem;">
                    <strong>{{ __('messages.eval_response_' . $qkey) }}</strong>
                    <div style="white-space:pre-wrap;margin-top:.25rem;">{{ $answer }}</div>
                </div>
            @endif
        @endforeach
    </div>
@endif
@endsection
