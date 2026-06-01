@extends('layouts.app')
@section('title', __('messages.performance_evaluations'))
@section('content')
@php
    $isInCohort = in_array($me->team_role, ['director', 'team_manager', 'team_member'], true);
@endphp
<style>
.eval-banner {
    display: flex; align-items: center; justify-content: space-between;
    background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px;
    padding: 14px 20px; margin: 12px 0 20px;
}
.eval-banner .label { color: #9a3412; }
.eval-banner .badge-days { background: #fff; border: 1px solid #fed7aa; padding: 4px 12px; border-radius: 999px; color: #9a3412; font-size: 13px; }
.eval-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 16px; overflow: hidden; }
.eval-section-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; }
.eval-section-header .title-block { display: flex; align-items: center; gap: 14px; }
.eval-section-header .num {
    width: 32px; height: 32px; border-radius: 8px; display: inline-flex;
    align-items: center; justify-content: center; font-weight: 700; font-size: 14px;
}
.eval-section-header .num.s1 { background: #fee2e2; color: #b91c1c; }
.eval-section-header .num.s2 { background: #dbeafe; color: #1e40af; }
.eval-section-header .num.s3 { background: #ede9fe; color: #6d28d9; }
.eval-section-header .num.s4 { background: #d1fae5; color: #065f46; }
.eval-section-header h3 { margin: 0; font-size: 17px; }
.eval-section-header .desc { color: #6b7280; font-size: 13px; margin-top: 2px; }
.eval-section-header .progress-pill {
    background: #f3f4f6; padding: 4px 14px; border-radius: 999px; font-size: 13px; color: #4b5563;
}
.eval-table { width: 100%; border-collapse: collapse; }
.eval-table th, .eval-table td { padding: 14px 22px; text-align: left; }
.eval-table thead th { background: #f9fafb; color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
.eval-table tbody tr + tr td { border-top: 1px solid #f3f4f6; }
.eval-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 12px; }
.eval-status .dot { width: 6px; height: 6px; border-radius: 50%; }
.eval-status.pending { background: #f3f4f6; color: #4b5563; }
.eval-status.pending .dot { background: #9ca3af; }
.eval-status.done { background: #dcfce7; color: #15803d; }
.eval-status.done .dot { background: #22c55e; }
.eval-status.confirmed { background: #dbeafe; color: #1e40af; }
.eval-status.confirmed .dot { background: #3b82f6; }
.stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; padding: 18px 22px; border-top: 1px solid #f3f4f6; }
.stat-grid .stat { padding: 6px 0; }
.stat-grid .stat .label { color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
.stat-grid .stat .value { font-size: 28px; font-weight: 700; color: #111827; }
.stat-grid .stat .value small { font-size: 14px; font-weight: 500; color: #9ca3af; }
.stat-grid .stat .sub { color: #6b7280; font-size: 12px; margin-top: 2px; }
.eval-table tr.group-start td { border-top: 1px solid #e5e7eb; }
.eval-table tr:first-child.group-start td { border-top: 0; }
@media (max-width: 720px) {
    .eval-section-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .stat-grid { grid-template-columns: 1fr; gap: 14px; }
}
</style>

<div class="page-header">
    <h1>{{ __('messages.performance_evaluations') }}</h1>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.evaluations.index') }}" class="btn btn-primary">{{ __('messages.view_all_results') }}</a>
    @endif
</div>

@if($isInCohort)
    <div class="eval-banner">
        <div class="label">⚠ {{ __('messages.evaluation_deadline_notice', ['date' => $deadline->translatedFormat(__('messages.date_format_medium'))]) }}</div>
        <span class="badge-days">{{ __('messages.days_left', ['count' => $daysRemaining]) }}</span>
    </div>
@endif

@if(!$isInCohort && !auth()->user()->isAdmin())
    <div class="card">
        <p>{{ __('messages.not_current_eval_cohort') }}</p>
    </div>
@endif

@php
    $renderStatus = function ($slot) {
        if ($slot['completed'] && $slot['evaluation']->isConfirmed()) {
            return '<span class="eval-status confirmed"><span class="dot"></span>' . e(__('messages.eval_status_confirmed')) . '</span>';
        }
        if ($slot['completed']) {
            return '<span class="eval-status done"><span class="dot"></span>' . e(__('messages.eval_status_submitted')) . '</span>';
        }
        return '<span class="eval-status pending"><span class="dot"></span>' . e(__('messages.eval_status_pending')) . '</span>';
    };
@endphp

@php
    $superiorMine = collect($superior)->where('is_mine', true);
    $superiorDone = $superiorMine->where('completed', true)->count();
    $superiorSorted = collect($superior)->sortBy(fn($s) => ($s['is_mine'] && !$s['completed']) ? 0 : 1)->values();
@endphp
<div class="eval-section">
    <div class="eval-section-header">
        <div class="title-block">
            <span class="num s1">1</span>
            <div>
                <h3>{{ __('messages.superior_reviews') }}</h3>
                <div class="desc">{{ __('messages.superior_reviews_desc') }}</div>
            </div>
        </div>
        <div class="progress-pill">@if($superiorMine->count()){{ $superiorDone }} / {{ $superiorMine->count() }} {{ __('messages.complete') }} @else {{ __('messages.view_only') }} @endif</div>
    </div>
    <table class="eval-table">
        <thead><tr><th>{{ __('messages.reviewer') }}</th><th>{{ __('messages.subject') }}</th><th>{{ __('messages.role') }}</th><th>{{ __('messages.status') }}</th><th></th></tr></thead>
        <tbody>
            @forelse($superiorSorted->groupBy(fn($s) => $s['evaluator']->id) as $group)
                @php $reviewer = $group->first()['evaluator']; $count = $group->count(); @endphp
                @foreach($group as $i => $slot)
                    <tr class="{{ $i === 0 ? 'group-start' : '' }}">
                        @if($i === 0)
                            <td rowspan="{{ $count }}" style="width:22%;vertical-align:top;">
                                <strong>{{ $reviewer->name }}</strong>
                                <div class="text-muted text-xs">{{ $reviewer->teamRoleLabel() }}</div>
                            </td>
                        @endif
                        <td><strong>{{ $slot['evaluee']->name }}</strong></td>
                        <td class="text-muted text-xs">{{ $slot['evaluee']->internRoleLabel() ?? $slot['evaluee']->teamRoleLabel() }}</td>
                        <td>{!! $renderStatus($slot) !!}</td>
                        <td>
                            @if($slot['is_mine'] && !$slot['completed'])
                                <a href="{{ route('evaluations.create', ['manager', $slot['evaluee']]) }}" class="btn btn-sm btn-primary">{{ __('messages.fill_out') }}</a>
                            @elseif($slot['completed'] && ($slot['is_mine'] || auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isTeamManager()))
                                <a href="{{ route('evaluations.show', $slot['evaluation']) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="5" class="text-muted" style="padding:18px 22px;">{{ __('messages.no_superior_evaluations_defined') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    $selfMine = collect($self)->where('is_mine', true);
    $selfDone = $selfMine->where('completed', true)->count();
    $selfSorted = collect($self)->sortBy(fn($s) => ($s['is_mine'] && !$s['completed']) ? 0 : 1)->values();
@endphp
<div class="eval-section">
    <div class="eval-section-header">
        <div class="title-block">
            <span class="num s2">2</span>
            <div>
                <h3>{{ __('messages.self_assessment') }}</h3>
                <div class="desc">{{ __('messages.self_assessment_desc') }}</div>
            </div>
        </div>
        <div class="progress-pill">@if($selfMine->count()){{ $selfDone }} / {{ $selfMine->count() }} {{ __('messages.complete') }} @else {{ __('messages.view_only') }} @endif</div>
    </div>
    <table class="eval-table">
        <thead><tr><th>{{ __('messages.person') }}</th><th>{{ __('messages.role') }}</th><th>{{ __('messages.status') }}</th><th></th></tr></thead>
        <tbody>
            @forelse($selfSorted as $slot)
                <tr>
                    <td><strong>{{ $slot['evaluator']->name }}</strong></td>
                    <td>{{ __('messages.self') }} · {{ $slot['evaluator']->teamRoleLabel() }} @if($slot['evaluator']->internRoleLabel())<span class="text-muted text-xs"> · {{ $slot['evaluator']->internRoleLabel() }}</span>@endif</td>
                    <td>{!! $renderStatus($slot) !!}</td>
                    <td>
                        @if($slot['is_mine'] && !$slot['completed'])
                            <a href="{{ route('evaluations.create', ['self', $slot['evaluator']]) }}" class="btn btn-sm btn-primary">{{ __('messages.fill_out') }}</a>
                        @elseif($slot['completed'] && ($slot['is_mine'] || auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isTeamManager()))
                            <a href="{{ route('evaluations.show', $slot['evaluation']) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted" style="padding:18px 22px;">{{ __('messages.no_self_assessments_defined') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    $peerMine = collect($peer)->where('is_mine', true);
    $peerDone = $peerMine->where('completed', true)->count();
    $peerSorted = collect($peer)->sortBy(fn($s) => ($s['is_mine'] && !$s['completed']) ? 0 : 1)->values();
@endphp
<div class="eval-section">
    <div class="eval-section-header">
        <div class="title-block">
            <span class="num s3">3</span>
            <div>
                <h3>{{ __('messages.peer_reviews') }}</h3>
                <div class="desc">{{ __('messages.peer_reviews_desc') }}</div>
            </div>
        </div>
        <div class="progress-pill">@if($peerMine->count()){{ $peerDone }} / {{ $peerMine->count() }} {{ __('messages.complete') }} @else {{ __('messages.view_only') }} @endif</div>
    </div>
    <table class="eval-table">
        <thead><tr><th>{{ __('messages.reviewer') }}</th><th>{{ __('messages.subject') }}</th><th>{{ __('messages.role') }}</th><th>{{ __('messages.status') }}</th><th></th></tr></thead>
        <tbody>
            @forelse($peerSorted->groupBy(fn($s) => $s['evaluator']->id) as $group)
                @php $reviewer = $group->first()['evaluator']; $count = $group->count(); @endphp
                @foreach($group as $i => $slot)
                    <tr class="{{ $i === 0 ? 'group-start' : '' }}">
                        @if($i === 0)
                            <td rowspan="{{ $count }}" style="width:22%;vertical-align:top;">
                                <strong>{{ $reviewer->name }}</strong>
                                <div class="text-muted text-xs">{{ $reviewer->teamRoleLabel() }}</div>
                            </td>
                        @endif
                        <td><strong>{{ $slot['evaluee']->name }}</strong></td>
                        <td class="text-muted text-xs">{{ $slot['evaluee']->internRoleLabel() ?? $slot['evaluee']->teamRoleLabel() }}</td>
                        <td>{!! $renderStatus($slot) !!}</td>
                        <td>
                            @if($slot['is_mine'] && !$slot['completed'])
                                <a href="{{ route('evaluations.create', ['peer', $slot['evaluee']]) }}" class="btn btn-sm btn-primary">{{ __('messages.fill_out') }}</a>
                            @elseif($slot['completed'] && ($slot['is_mine'] || auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isTeamManager()))
                                <a href="{{ route('evaluations.show', $slot['evaluation']) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="5" class="text-muted" style="padding:18px 22px;">{{ __('messages.no_peer_reviews_defined') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    // Section 4 is always rendered; results access depends on role
@endphp
@if(true)
    <div class="eval-section">
        <div class="eval-section-header">
            <div class="title-block">
                <span class="num s4">4</span>
                <div>
                    <h3>{{ __('messages.results_statistics') }}</h3>
                    <div class="desc">{{ __('messages.results_statistics_desc') }}</div>
                </div>
            </div>
            <div class="progress-pill">{{ __('messages.view_only') }}</div>
        </div>
        <div class="stat-grid">
            <div class="stat">
                <div class="label">{{ __('messages.total_forms') }}</div>
                <div class="value">{{ $completedForms }} <small>/ {{ $totalForms }}</small></div>
                <div class="sub">{{ __('messages.forms_remaining', ['count' => max(0, $totalForms - $completedForms)]) }}</div>
            </div>
            <div class="stat">
                <div class="label">{{ __('messages.days_remaining') }}</div>
                <div class="value">{{ $daysRemaining }}</div>
                <div class="sub">{{ __('messages.until_date', ['date' => $deadline->translatedFormat(__('messages.date_format_medium'))]) }}</div>
            </div>
            <div class="stat">
                <div class="label">{{ __('messages.results_access') }}</div>
                <div class="value" style="color:{{ $canViewResults ? '#22c55e' : '#9ca3af' }}">{{ $canViewResults ? '✓' : '—' }}</div>
                <div class="sub">{{ $canViewResults ? __('messages.reports_available', ['count' => count($resultsSummary)]) : __('messages.eval_status_restricted') }}</div>
            </div>
        </div>
        @if(!empty($resultsSummary))
            <table class="eval-table">
                <thead><tr><th>{{ __('messages.intern') }}</th><th>{{ __('messages.role') }}</th><th>{{ __('messages.status') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach($resultsSummary as $row)
                        <tr>
                            <td><strong>{{ $row['user']->name }}</strong></td>
                            <td>{{ $row['user']->teamRoleLabel() }} @if($row['user']->internRoleLabel())<span class="text-muted text-xs"> · {{ $row['user']->internRoleLabel() }}</span>@endif</td>
                            <td>
                                @if($row['has_data'])
                                    <span class="eval-status done"><span class="dot"></span>{{ __('messages.eval_status_in_progress') }}</span>
                                @else
                                    <span class="eval-status pending"><span class="dot"></span>{{ __('messages.eval_status_no_data') }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $canSee = auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isTeamManager() || auth()->id() === $row['user']->id;
                                @endphp
                                @if($canSee)
                                    <a href="{{ route('evaluations.intern-report', $row['user']) }}" class="btn btn-sm btn-outline">{{ __('messages.view_report') }}</a>
                                @else
                                    <span class="btn btn-sm btn-outline" style="opacity:.5;cursor:not-allowed">{{ __('messages.view_report') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endif
@endsection
