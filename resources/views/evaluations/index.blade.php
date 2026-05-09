@extends('layouts.app')
@section('title', 'Performance Evaluations')
@section('content')
<div class="page-header">
    <h1>Performance Evaluations</h1>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.evaluations.index') }}" class="btn btn-primary">View All Results</a>
    @endif
</div>

@if(!$me->intern_role && !auth()->user()->isAdmin())
    <div class="card">
        <p>You are not part of the current intern review cohort. Please contact your administrator.</p>
    </div>
@endif

@if($me->intern_role)
    <div class="card">
        <div class="card-title">My Evaluation Forms ({{ $me->internRoleLabel() }})</div>
        <p class="text-muted">Complete one self-assessment and one peer review for each of your two teammates — 3 forms total.</p>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>About</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr>
                            <td>
                                @if($task['type'] === 'self')
                                    <strong>Self-Assessment</strong>
                                @else
                                    <strong>Peer Review</strong>
                                @endif
                            </td>
                            <td>
                                {{ $task['evaluee']->name }}
                                <div class="text-muted text-xs">{{ $task['evaluee']->internRoleLabel() }}</div>
                            </td>
                            <td>
                                @if($task['completed'])
                                    <span class="badge badge-success">Submitted</span>
                                    <div class="text-muted text-xs">{{ $task['evaluation']->submitted_at?->format('Y-m-d H:i') }}</div>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($task['completed'])
                                    <a href="{{ route('evaluations.show', $task['evaluation']) }}" class="btn btn-sm btn-outline">View</a>
                                    <a href="{{ route('evaluations.edit', $task['evaluation']) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form method="POST" action="{{ route('evaluations.destroy', $task['evaluation']) }}" style="display:inline" onsubmit="return confirm('Delete this evaluation? This cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @else
                                    <a href="{{ route('evaluations.create', [$task['type'], $task['evaluee']]) }}" class="btn btn-sm btn-primary">Fill Out</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if(auth()->user()->isAdmin() && !empty($managerTasks))
    <div class="card" style="margin-top:1rem;">
        <div class="card-title">Manager Evaluations</div>
        <p class="text-muted">Fill the manager evaluation for each intern (50% weight in the composite score).</p>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Intern</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($managerTasks as $task)
                        <tr>
                            <td>{{ $task['evaluee']->name }}</td>
                            <td>{{ $task['evaluee']->internRoleLabel() }}</td>
                            <td>
                                @if($task['completed'])
                                    <span class="badge badge-success">Submitted</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($task['completed'])
                                    <a href="{{ route('evaluations.show', $task['evaluation']) }}" class="btn btn-sm btn-outline">View</a>
                                    <a href="{{ route('evaluations.edit', $task['evaluation']) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form method="POST" action="{{ route('evaluations.destroy', $task['evaluation']) }}" style="display:inline" onsubmit="return confirm('Delete this evaluation? This cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @else
                                    <a href="{{ route('evaluations.create', ['manager', $task['evaluee']]) }}" class="btn btn-sm btn-primary">Fill Out</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
