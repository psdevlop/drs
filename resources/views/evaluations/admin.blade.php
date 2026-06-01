@extends('layouts.app')
@section('title', __('messages.evaluations_overview'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.evaluations_admin_overview') }}</h1>
    <a href="{{ route('evaluations.index') }}" class="btn btn-outline">{{ __('messages.my_evaluations') }}</a>
</div>

<div class="card">
    <div class="card-title">{{ __('messages.calibration_matrix') }}</div>
    <p class="text-muted text-xs">{{ __('messages.calibration_desc') }}</p>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.intern') }}</th>
                    <th>{{ __('messages.role') }}</th>
                    <th>{{ __('messages.self_20') }}</th>
                    <th>{{ __('messages.peer_avg_30') }}</th>
                    <th>{{ __('messages.manager_50') }}</th>
                    <th>{{ __('messages.composite') }}</th>
                    <th>{{ __('messages.grade') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($matrix as $row)
                    <tr>
                        <td>
                            <strong>{{ $row['intern']->name }}</strong>
                            <div class="text-muted text-xs">{{ $row['intern']->email }}</div>
                        </td>
                        <td>{{ $row['intern']->internRoleLabel() }}</td>
                        <td>{{ $row['self_score'] ?? '—' }}</td>
                        <td>{{ $row['peer_avg'] ?? '—' }} <span class="text-muted text-xs">({{ __('messages.reviews_count', ['count' => $row['peers']->count()]) }})</span></td>
                        <td>{{ $row['manager_score'] ?? '—' }}</td>
                        <td><strong>{{ $row['composite'] ?? '—' }}</strong></td>
                        <td>
                            @if($row['grade'])
                                <span class="badge badge-{{ in_array($row['grade'], ['S','A']) ? 'success' : (in_array($row['grade'], ['B','C']) ? 'warning' : 'danger') }}">{{ $row['grade'] }}</span>
                            @else
                                <span class="text-muted">{{ __('messages.eval_status_incomplete') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@foreach($matrix as $row)
    <div class="card" style="margin-top:1rem;">
        <div class="card-title">{{ __('messages.all_submissions', ['name' => $row['intern']->name]) }}</div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.type') }}</th>
                        <th>{{ __('messages.submitted_by') }}</th>
                        <th>{{ __('messages.when') }}</th>
                        <th>{{ __('messages.score') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $actionsCell = function ($eval) {
                            return view('evaluations.partials.admin-actions', ['eval' => $eval])->render();
                        };
                    @endphp

                    @if($row['self'])
                        <tr>
                            <td><span class="badge">{{ __('messages.self') }}</span></td>
                            <td>
                                {{ $row['self']->evaluator->name }}
                                @if($row['self']->isConfirmed())
                                    <span class="badge" style="background:#dbeafe;color:#1e40af;margin-left:6px;">{{ __('messages.eval_status_confirmed') }}</span>
                                @endif
                            </td>
                            <td>{{ $row['self']->submitted_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $row['self']->self_score }} / 5</td>
                            <td>{!! $actionsCell($row['self']) !!}</td>
                        </tr>
                    @else
                        <tr><td colspan="5" class="text-muted">{{ __('messages.self_assessment_not_submitted') }}</td></tr>
                    @endif

                    @forelse($row['peers'] as $peer)
                        <tr>
                            <td><span class="badge">{{ __('messages.peer') }}</span></td>
                            <td>
                                {{ $peer->evaluator->name }}
                                @if($peer->isConfirmed())
                                    <span class="badge" style="background:#dbeafe;color:#1e40af;margin-left:6px;">{{ __('messages.eval_status_confirmed') }}</span>
                                @endif
                            </td>
                            <td>{{ $peer->submitted_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $peer->averageRating() }} / 5</td>
                            <td>{!! $actionsCell($peer) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">{{ __('messages.no_peer_reviews_yet') }}</td></tr>
                    @endforelse

                    @forelse($row['managers'] as $manager)
                        <tr>
                            <td><span class="badge" style="background:#fef3c7;color:#92400e;">{{ $manager->evaluator->teamRoleLabel() ?? __('messages.superior') }}</span></td>
                            <td>
                                {{ $manager->evaluator->name }}
                                @if($manager->isConfirmed())
                                    <span class="badge" style="background:#dbeafe;color:#1e40af;margin-left:6px;">{{ __('messages.eval_status_confirmed') }}</span>
                                @endif
                            </td>
                            <td>{{ $manager->submitted_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $manager->weightedScore() }} / 5</td>
                            <td>{!! $actionsCell($manager) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">{{ __('messages.no_superior_evaluations_yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection
