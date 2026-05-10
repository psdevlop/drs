@extends('layouts.app')
@section('title', 'Performance Evaluations')
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
    <h1>Performance Evaluations</h1>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.evaluations.index') }}" class="btn btn-primary">View All Results</a>
    @endif
</div>

@if($isInCohort)
    <div class="eval-banner">
        <div class="label">⚠ Please complete your evaluation forms by <strong>{{ $deadline->format('M j, Y') }}</strong>.</div>
        <span class="badge-days">{{ $daysRemaining }} {{ $daysRemaining === 1 ? 'day' : 'days' }} left</span>
    </div>
@endif

@if(!$isInCohort && !auth()->user()->isAdmin())
    <div class="card">
        <p>You are not part of the current evaluation cohort. Please contact your administrator.</p>
    </div>
@endif

@php
    $renderStatus = function ($slot) {
        if ($slot['completed'] && $slot['evaluation']->isConfirmed()) {
            return '<span class="eval-status confirmed"><span class="dot"></span>Confirmed</span>';
        }
        if ($slot['completed']) {
            return '<span class="eval-status done"><span class="dot"></span>Submitted</span>';
        }
        return '<span class="eval-status pending"><span class="dot"></span>Pending</span>';
    };
@endphp

@php
    $superiorMine = collect($superior)->where('is_mine', true);
    $superiorDone = $superiorMine->where('completed', true)->count();
@endphp
<div class="eval-section">
    <div class="eval-section-header">
        <div class="title-block">
            <span class="num s1">1</span>
            <div>
                <h3>Superior Reviews — Subordinate Evaluations</h3>
                <div class="desc">As a superior, evaluate the team members who report to you. This carries 50% weight in the composite score.</div>
            </div>
        </div>
        <div class="progress-pill">@if($superiorMine->count()){{ $superiorDone }} / {{ $superiorMine->count() }} complete @else view only @endif</div>
    </div>
    <table class="eval-table">
        <thead><tr><th>Reviewer</th><th>Subject</th><th>Role</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse(collect($superior)->groupBy(fn($s) => $s['evaluator']->id) as $group)
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
                                <a href="{{ route('evaluations.create', ['manager', $slot['evaluee']]) }}" class="btn btn-sm btn-primary">Fill Out</a>
                            @elseif($slot['completed'] && ($slot['is_mine'] || auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isTeamManager()))
                                <a href="{{ route('evaluations.show', $slot['evaluation']) }}" class="btn btn-sm btn-outline">View</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="5" class="text-muted" style="padding:18px 22px;">No superior evaluations defined.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    $selfMine = collect($self)->where('is_mine', true);
    $selfDone = $selfMine->where('completed', true)->count();
@endphp
<div class="eval-section">
    <div class="eval-section-header">
        <div class="title-block">
            <span class="num s2">2</span>
            <div>
                <h3>Self Assessment</h3>
                <div class="desc">Reflect on your own work over the two-month internship period. This carries 20% weight in the composite score.</div>
            </div>
        </div>
        <div class="progress-pill">@if($selfMine->count()){{ $selfDone }} / {{ $selfMine->count() }} complete @else view only @endif</div>
    </div>
    <table class="eval-table">
        <thead><tr><th>Person</th><th>Role</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse($self as $slot)
                <tr>
                    <td><strong>{{ $slot['evaluator']->name }}</strong></td>
                    <td>Self · {{ $slot['evaluator']->teamRoleLabel() }} @if($slot['evaluator']->internRoleLabel())<span class="text-muted text-xs"> · {{ $slot['evaluator']->internRoleLabel() }}</span>@endif</td>
                    <td>{!! $renderStatus($slot) !!}</td>
                    <td>
                        @if($slot['is_mine'] && !$slot['completed'])
                            <a href="{{ route('evaluations.create', ['self', $slot['evaluator']]) }}" class="btn btn-sm btn-primary">Fill Out</a>
                        @elseif($slot['completed'] && ($slot['is_mine'] || auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isTeamManager()))
                            <a href="{{ route('evaluations.show', $slot['evaluation']) }}" class="btn btn-sm btn-outline">View</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted" style="padding:18px 22px;">No self assessments defined.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    $peerMine = collect($peer)->where('is_mine', true);
    $peerDone = $peerMine->where('completed', true)->count();
@endphp
<div class="eval-section">
    <div class="eval-section-header">
        <div class="title-block">
            <span class="num s3">3</span>
            <div>
                <h3>Peer Reviews</h3>
                <div class="desc">Evaluate the colleagues you work alongside. This carries 30% weight in the composite score. Behavioral examples matter more than scores.</div>
            </div>
        </div>
        <div class="progress-pill">@if($peerMine->count()){{ $peerDone }} / {{ $peerMine->count() }} complete @else view only @endif</div>
    </div>
    <table class="eval-table">
        <thead><tr><th>Reviewer</th><th>Subject</th><th>Role</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse(collect($peer)->groupBy(fn($s) => $s['evaluator']->id) as $group)
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
                                <a href="{{ route('evaluations.create', ['peer', $slot['evaluee']]) }}" class="btn btn-sm btn-primary">Fill Out</a>
                            @elseif($slot['completed'] && ($slot['is_mine'] || auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isTeamManager()))
                                <a href="{{ route('evaluations.show', $slot['evaluation']) }}" class="btn btn-sm btn-outline">View</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="5" class="text-muted" style="padding:18px 22px;">No peer reviews defined.</td></tr>
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
                    <h3>Results &amp; Statistics</h3>
                    <div class="desc">Composite scores and grades become available once enough reviews are submitted. Restricted to managers and team leads.</div>
                </div>
            </div>
            <div class="progress-pill">View only</div>
        </div>
        <div class="stat-grid">
            <div class="stat">
                <div class="label">Total Forms</div>
                <div class="value">{{ $completedForms }} <small>/ {{ $totalForms }}</small></div>
                <div class="sub">{{ max(0, $totalForms - $completedForms) }} forms remaining</div>
            </div>
            <div class="stat">
                <div class="label">Days Remaining</div>
                <div class="value">{{ $daysRemaining }}</div>
                <div class="sub">Until {{ $deadline->format('M j, Y') }}</div>
            </div>
            <div class="stat">
                <div class="label">Results Access</div>
                <div class="value" style="color:{{ $canViewResults ? '#22c55e' : '#9ca3af' }}">{{ $canViewResults ? '✓' : '—' }}</div>
                <div class="sub">{{ $canViewResults ? count($resultsSummary) . ' reports available' : 'Restricted' }}</div>
            </div>
        </div>
        @if(!empty($resultsSummary))
            <table class="eval-table">
                <thead><tr><th>Intern</th><th>Role</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($resultsSummary as $row)
                        <tr>
                            <td><strong>{{ $row['user']->name }}</strong></td>
                            <td>{{ $row['user']->teamRoleLabel() }} @if($row['user']->internRoleLabel())<span class="text-muted text-xs"> · {{ $row['user']->internRoleLabel() }}</span>@endif</td>
                            <td>
                                @if($row['has_data'])
                                    <span class="eval-status done"><span class="dot"></span>In progress</span>
                                @else
                                    <span class="eval-status pending"><span class="dot"></span>No data</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $canSee = auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isTeamManager() || auth()->id() === $row['user']->id;
                                @endphp
                                @if($canSee)
                                    <a href="{{ route('evaluations.intern-report', $row['user']) }}" class="btn btn-sm btn-outline">View Report</a>
                                @else
                                    <span class="btn btn-sm btn-outline" style="opacity:.5;cursor:not-allowed">View Report</span>
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
