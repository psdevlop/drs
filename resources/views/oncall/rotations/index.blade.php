@extends('layouts.app')
@section('title', __('messages.rotation_schedules'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.rotation_schedules') }}</h1>
    <div class="actions">
        <a href="{{ route('oncall.index') }}" class="btn btn-outline">{{ __('messages.oncall_schedule') }}</a>
        <a href="{{ route('oncall.rotations.preview') }}" class="btn btn-outline">{{ __('messages.rotation_preview') }}</a>
        <a href="{{ route('oncall.rotations.create') }}" class="btn btn-primary">{{ __('messages.rotation_new') }}</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    @if($rotations->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.name') }}</th>
                        <th>{{ __('messages.rotation_cycle') }}</th>
                        <th>{{ __('messages.rotation_users_order') }}</th>
                        <th>{{ __('messages.start_date') }}</th>
                        <th>{{ __('messages.end_date') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rotations as $rotation)
                        <tr>
                            <td class="text-bold">{{ $rotation->name }}</td>
                            <td>
                                {{ __('messages.rotation_every') }}
                                {{ $rotation->cycle_length > 1 ? $rotation->cycle_length . ' ' : '' }}{{ __('messages.rotation_' . $rotation->cycle_type) }}{{ $rotation->cycle_length > 1 ? 's' : '' }}
                            </td>
                            <td>
                                <div class="oncall-users-list">
                                    @foreach($rotation->users as $i => $user)
                                        <span class="badge badge-oncall">{{ $i + 1 }}. {{ $user->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $rotation->start_date->format('M d, Y') }}</td>
                            <td>{{ $rotation->end_date?->format('M d, Y') ?? __('messages.rotation_indefinite') }}</td>
                            <td>
                                @if($rotation->is_active)
                                    <span class="badge badge-completed">{{ __('messages.active') }}</span>
                                @else
                                    <span class="badge badge-pending">{{ __('messages.inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('oncall.rotations.edit', $rotation) }}" class="btn btn-sm btn-outline">{{ __('messages.edit') }}</a>
                                    <form action="{{ route('oncall.rotations.destroy', $rotation) }}" method="POST" onsubmit="return confirm('{{ __('messages.rotation_delete_confirm') }}')">
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
        <div class="pagination-wrapper">{{ $rotations->links() }}</div>
    @else
        <div class="empty-state">
            <p>{{ __('messages.rotation_none') }}</p>
            <a href="{{ route('oncall.rotations.create') }}" class="btn btn-primary">{{ __('messages.rotation_new') }}</a>
        </div>
    @endif
</div>
@endsection
