@extends('layouts.app')
@section('title', __('messages.manage_users'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.manage_users') }}</h1>
    <div class="actions">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">{{ __('messages.create_user') }}</a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">{{ __('messages.back_to_admin') }}</a>
    </div>
</div>

<div class="card">
    @if($users->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.name') }}</th>
                        <th>{{ __('messages.email') }}</th>
                        <th>{{ __('messages.role') }}</th>
                        <th>{{ __('messages.tasks') }}</th>
                        <th>{{ __('messages.reports') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="text-bold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge badge-{{ $user->role }}">{{ __('messages.role_' . $user->role) }}</span></td>
                            <td>{{ $user->tasks_count }}</td>
                            <td>{{ $user->daily_reports_count }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline">{{ __('messages.edit') }}</a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_user_confirm') }}')">
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
        <div class="pagination-wrapper">{{ $users->links() }}</div>
    @else
        <div class="empty-state">
            <p>{{ __('messages.no_users_found') }}</p>
        </div>
    @endif
</div>
@endsection
