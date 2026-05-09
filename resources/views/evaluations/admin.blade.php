@extends('layouts.app')
@section('title', 'Evaluations Overview')
@section('content')
<div class="page-header">
    <h1>Evaluations — Admin Overview</h1>
    <a href="{{ route('evaluations.index') }}" class="btn btn-outline">My Evaluations</a>
</div>

<div class="card">
    <div class="card-title">Calibration Matrix</div>
    <p class="text-muted text-xs">Composite = Manager 50% + Peer Avg 30% + Self 20%. Grades: S 4.5–5.0 · A 4.0–4.4 · B 3.5–3.9 · C 3.0–3.4 · D &lt; 3.0</p>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Intern</th>
                    <th>Role</th>
                    <th>Self (20%)</th>
                    <th>Peer Avg (30%)</th>
                    <th>Manager (50%)</th>
                    <th>Composite</th>
                    <th>Grade</th>
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
                        <td>{{ $row['peer_avg'] ?? '—' }} <span class="text-muted text-xs">({{ $row['peers']->count() }} reviews)</span></td>
                        <td>{{ $row['manager_score'] ?? '—' }}</td>
                        <td><strong>{{ $row['composite'] ?? '—' }}</strong></td>
                        <td>
                            @if($row['grade'])
                                <span class="badge badge-{{ in_array($row['grade'], ['S','A']) ? 'success' : (in_array($row['grade'], ['B','C']) ? 'warning' : 'danger') }}">{{ $row['grade'] }}</span>
                            @else
                                <span class="text-muted">incomplete</span>
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
        <div class="card-title">{{ $row['intern']->name }} — All Submissions</div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Submitted By</th>
                        <th>When</th>
                        <th>Score</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if($row['self'])
                        <tr>
                            <td><span class="badge">Self</span></td>
                            <td>{{ $row['self']->evaluator->name }}</td>
                            <td>{{ $row['self']->submitted_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $row['self']->self_score }} / 5</td>
                            <td>
                                <a href="{{ route('evaluations.show', $row['self']) }}" class="btn btn-sm btn-outline">View</a>
                                <form method="POST" action="{{ route('evaluations.destroy', $row['self']) }}" style="display:inline" onsubmit="return confirm('Delete this evaluation?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @else
                        <tr><td colspan="5" class="text-muted">Self-assessment not yet submitted</td></tr>
                    @endif

                    @forelse($row['peers'] as $peer)
                        <tr>
                            <td><span class="badge">Peer</span></td>
                            <td>{{ $peer->evaluator->name }}</td>
                            <td>{{ $peer->submitted_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $peer->averageRating() }} / 5</td>
                            <td>
                                <a href="{{ route('evaluations.show', $peer) }}" class="btn btn-sm btn-outline">View</a>
                                <form method="POST" action="{{ route('evaluations.destroy', $peer) }}" style="display:inline" onsubmit="return confirm('Delete this evaluation?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No peer reviews yet</td></tr>
                    @endforelse

                    @if($row['manager'])
                        <tr>
                            <td><span class="badge">Manager</span></td>
                            <td>{{ $row['manager']->evaluator->name }}</td>
                            <td>{{ $row['manager']->submitted_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $row['manager']->weightedScore() }} / 5</td>
                            <td>
                                <a href="{{ route('evaluations.show', $row['manager']) }}" class="btn btn-sm btn-outline">View</a>
                                @if($row['manager']->evaluator_id === auth()->id())
                                    <a href="{{ route('evaluations.edit', $row['manager']) }}" class="btn btn-sm btn-primary">Edit</a>
                                @endif
                                <form method="POST" action="{{ route('evaluations.destroy', $row['manager']) }}" style="display:inline" onsubmit="return confirm('Delete this evaluation?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @else
                        <tr><td colspan="5" class="text-muted">Manager evaluation not yet submitted</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection
