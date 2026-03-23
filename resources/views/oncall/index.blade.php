@extends('layouts.app')
@section('title', __('messages.on_call'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.on_call') }}</h1>
    <div class="actions">
        <a href="{{ route('oncall.create') }}" class="btn btn-primary">{{ __('messages.new_on_call') }}</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    @if($onCalls->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.oncall_duty_users') }}</th>
                        <th>{{ __('messages.oncall_notes') }}</th>
                        <th>{{ __('messages.created_by') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($onCalls as $onCall)
                        <tr>
                            <td class="text-bold">{{ $onCall->date->format('M d, Y') }}</td>
                            <td>
                                <div class="oncall-users-list">
                                    @foreach($onCall->users as $user)
                                        <span class="badge badge-oncall">{{ $user->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $onCall->notes ?? '-' }}</td>
                            <td>{{ $onCall->creator->name }}</td>
                            <td>
                                <div class="actions">
                                    @if($onCall->date->gte($today))
                                        <a href="{{ route('oncall.edit', $onCall) }}" class="btn btn-sm btn-outline">{{ __('messages.edit') }}</a>
                                    @else
                                        <span class="badge badge-completed">{{ __('messages.oncall_completed') }}</span>
                                    @endif
                                    <form action="{{ route('oncall.destroy', $onCall) }}" method="POST" onsubmit="return confirm('{{ __('messages.oncall_delete_confirm') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">{{ $onCalls->links() }}</div>
    @else
        <div class="empty-state">
            <p>{{ __('messages.no_oncall_found') }}</p>
            <a href="{{ route('oncall.create') }}" class="btn btn-primary">{{ __('messages.new_on_call') }}</a>
        </div>
    @endif
</div>
@endsection
