@extends('layouts.app')
@section('title', __('messages.tasks'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.my_tasks') }}</h1>
    <div class="actions">
        <a href="{{ route('calendar') }}" class="btn btn-outline">{{ __('messages.calendar') }}</a>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">{{ __('messages.new_task') }}</a>
    </div>
</div>

<form method="GET" action="{{ route('tasks.index') }}" class="filter-bar">
    <div class="form-group">
        <label>{{ __('messages.status') }}</label>
        <select name="status" class="form-control filter-select">
            <option value="">{{ __('messages.all_statuses') }}</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
        </select>
    </div>
    <div class="form-group">
        <label>{{ __('messages.priority') }}</label>
        <select name="priority" class="form-control filter-select">
            <option value="">{{ __('messages.all_priorities') }}</option>
            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>{{ __('messages.low') }}</option>
            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>{{ __('messages.medium') }}</option>
            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>{{ __('messages.high') }}</option>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">{{ __('messages.filter') }}</button>
    @if(request()->hasAny(['status', 'priority']))
        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-secondary">{{ __('messages.clear') }}</a>
    @endif
</form>

<div class="card">
    @if($tasks->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.title') }}</th>
                        <th>{{ __('messages.created_by') }}</th>
                        <th>{{ __('messages.assigned_to') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.progress') }}</th>
                        <th>{{ __('messages.priority') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr>
                            <td>
                                <div class="text-bold">{{ $task->title }}</div>
                                @if($task->description)
                                    <div class="text-muted text-sm">{!! Str::limit(strip_tags($task->description), 60) !!}</div>
                                @endif
                            </td>
                            <td>{{ $task->user->name }}</td>
                            <td>{{ $task->assignees->pluck('name')->join(', ') ?: '-' }}</td>
                            <td>
                                <form action="{{ route('tasks.update-status', $task) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="status-select status-{{ $task->status }}">
                                        <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                                        <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                                        <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="progress-wrapper">
                                    <div class="progress-track">
                                        <div class="progress-fill {{ $task->progress == 100 ? 'progress-fill-complete' : '' }}" style="width:{{ $task->progress }}%"></div>
                                    </div>
                                    <span class="progress-text">{{ $task->progress }}%</span>
                                </div>
                            </td>
                            <td><span class="badge badge-{{ $task->priority }}">{{ __('messages.' . $task->priority) }}</span></td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline">{{ __('messages.edit') }}</a>
                                    @if($task->user_id === auth()->id())
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_task_confirm') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">{{ $tasks->withQueryString()->links() }}</div>
    @else
        <div class="empty-state">
            <p>{{ __('messages.no_tasks_found') }}</p>
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">{{ __('messages.create_first_task') }}</a>
        </div>
    @endif
</div>
@endsection
