@extends('layouts.app')
@section('title', $user->name . ' — Performance Report')
@section('content')
<style>
.report-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
.report-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px; }
.report-card .label { color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
.report-card .value { font-size: 28px; font-weight: 700; color: #111827; margin-top: 4px; }
.report-card .value small { font-size: 13px; font-weight: 500; color: #9ca3af; }
.report-card .grade-S { color: #15803d; }
.report-card .grade-A { color: #16a34a; }
.report-card .grade-B { color: #2563eb; }
.report-card .grade-C { color: #d97706; }
.report-card .grade-D { color: #dc2626; }
.eval-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 16px; overflow: hidden; }
.eval-section h3 { margin: 0; padding: 16px 22px; border-bottom: 1px solid #e5e7eb; font-size: 16px; }
.eval-section-body { padding: 0; }
.eval-section table { width: 100%; border-collapse: collapse; }
.eval-section th, .eval-section td { padding: 12px 22px; text-align: left; }
.eval-section thead th { background: #f9fafb; color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #e5e7eb; }
.eval-section tbody tr + tr td { border-top: 1px solid #f3f4f6; }
@media (max-width: 720px) {
    .report-summary { grid-template-columns: 1fr 1fr; }
}
</style>

<div class="page-header">
    <h1>{{ $user->name }} — Performance Report</h1>
    <a href="{{ url()->previous() }}" class="btn btn-outline">Back</a>
</div>

<div class="card" style="margin-bottom:16px;">
    <div><strong>{{ $user->name }}</strong></div>
    <div class="text-muted">{{ $user->email }} · {{ $user->teamRoleLabel() }}@if($user->internRoleLabel()) · {{ $user->internRoleLabel() }}@endif</div>
</div>

<div class="report-summary">
    <div class="report-card">
        <div class="label">Manager (50%)</div>
        <div class="value">{{ $managerScore ?? '—' }} <small>/ 5</small></div>
    </div>
    <div class="report-card">
        <div class="label">Peer Avg (30%)</div>
        <div class="value">{{ $peerAvg ?? '—' }} <small>/ 5</small></div>
    </div>
    <div class="report-card">
        <div class="label">Self (20%)</div>
        <div class="value">{{ $selfScore ?? '—' }} <small>/ 5</small></div>
    </div>
    <div class="report-card">
        <div class="label">Composite</div>
        <div class="value @if($grade) grade-{{ $grade }} @endif">
            {{ $composite ?? '—' }}
            @if($grade)<small style="margin-left:6px;">Grade {{ $grade }}</small>@endif
        </div>
    </div>
</div>

<div class="eval-section">
    <h3>Self Assessment</h3>
    <div class="eval-section-body">
        <table>
            <thead><tr><th>Submitted By</th><th>When</th><th>Score</th><th></th></tr></thead>
            <tbody>
                @if($self)
                    <tr>
                        <td>{{ $self->evaluator->name }}@if($self->isConfirmed()) <span class="badge" style="background:#dbeafe;color:#1e40af;margin-left:6px;">Confirmed</span>@endif</td>
                        <td>{{ $self->submitted_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $self->self_score }} / 5</td>
                        <td><a href="{{ route('evaluations.show', $self) }}" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                @else
                    <tr><td colspan="4" class="text-muted">Self-assessment not yet submitted</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div class="eval-section">
    <h3>Peer Reviews</h3>
    <div class="eval-section-body">
        <table>
            <thead><tr><th>Reviewer</th><th>When</th><th>Avg Score</th><th></th></tr></thead>
            <tbody>
                @forelse($peers as $peer)
                    <tr>
                        <td>{{ $peer->evaluator->name }}@if($peer->isConfirmed()) <span class="badge" style="background:#dbeafe;color:#1e40af;margin-left:6px;">Confirmed</span>@endif</td>
                        <td>{{ $peer->submitted_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $peer->averageRating() }} / 5</td>
                        <td><a href="{{ route('evaluations.show', $peer) }}" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No peer reviews yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="eval-section">
    <h3>Superior Evaluations</h3>
    <div class="eval-section-body">
        <table>
            <thead><tr><th>Reviewer</th><th>Role</th><th>When</th><th>Weighted Score</th><th></th></tr></thead>
            <tbody>
                @forelse($managers as $manager)
                    <tr>
                        <td>{{ $manager->evaluator->name }}@if($manager->isConfirmed()) <span class="badge" style="background:#dbeafe;color:#1e40af;margin-left:6px;">Confirmed</span>@endif</td>
                        <td>{{ $manager->evaluator->teamRoleLabel() ?? 'Superior' }}</td>
                        <td>{{ $manager->submitted_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $manager->weightedScore() }} / 5</td>
                        <td><a href="{{ route('evaluations.show', $manager) }}" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No superior evaluations yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
