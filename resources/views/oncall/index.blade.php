@extends('layouts.app')
@section('title', __('messages.on_call'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.on_call') }}</h1>
    <div class="actions">
        <a href="{{ route('oncall.rotations') }}" class="btn btn-outline">{{ __('messages.rotation_schedules') }}</a>
        <a href="{{ route('oncall.rotations.create') }}" class="btn btn-primary">{{ __('messages.rotation_new') }}</a>
    </div>
</div>


{{-- On-Call Schedule (auto-generated from rotations) --}}
<div class="card">
    <div class="card-title">{{ __('messages.oncall_schedule') }}</div>

    <form method="GET" action="{{ route('oncall.index') }}" class="filter-bar">
        <div class="form-group">
            <label>{{ __('messages.start_date') }}</label>
            <input type="date" name="start_date" class="form-control filter-date" value="{{ $startDate->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label>{{ __('messages.rotation_days_to_show') }}</label>
            <select name="days" class="form-control filter-select">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 {{ __('messages.days') }}</option>
                <option value="14" {{ $days == 14 ? 'selected' : '' }}>14 {{ __('messages.days') }}</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 {{ __('messages.days') }}</option>
                <option value="60" {{ $days == 60 ? 'selected' : '' }}>60 {{ __('messages.days') }}</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 {{ __('messages.days') }}</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">{{ __('messages.filter') }}</button>
    </form>

    @if(count($schedule))
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.day') }}</th>
                        <th>{{ __('messages.oncall_duty_users') }}</th>
                        <th>{{ __('messages.person_in_charge') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule as $day)
                        <tr class="{{ $day['date']->isToday() ? 'preview-today' : '' }} {{ $day['is_holiday'] ? 'preview-weekend' : '' }}">
                            <td class="text-bold">{{ $day['date']->format('M d, Y') }}</td>
                            <td>{{ $day['date']->format('l') }}</td>
                            <td>
                                @if(count($day['users']))
                                    @php $picId = $day['pic_user_id'] ?? null; @endphp
                                    <div class="oncall-users-list">
                                        @foreach($day['users'] as $u)
                                            <span class="badge {{ $picId !== null && (int)$picId === (int)$u['id'] ? 'badge-pic' : 'badge-oncall' }}">{{ $u['name'] }}{{ $picId !== null && (int)$picId === (int)$u['id'] ? ' (PIC)' : '' }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted text-sm">{{ __('messages.holiday') }}</span>
                                @endif
                            </td>
                            <td>
                                @if(count($day['users']) > 0 && $day['date']->gte($today))
                                    <form action="{{ route('oncall.update-pic-date') }}" method="POST" style="display:inline;">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="date" value="{{ $day['date']->format('Y-m-d') }}">
                                        <select name="pic_user_id" onchange="this.form.submit()" class="status-select {{ $day['pic'] ? 'status-in_progress' : '' }}">
                                            <option value="">-- {{ __('messages.select') }} --</option>
                                            @php $currentPicId = $day['pic_user_id'] ?? null; @endphp
                                            @foreach($day['users'] as $u)
                                                <option value="{{ $u['id'] }}" {{ $currentPicId !== null && (int)$currentPicId === (int)$u['id'] ? 'selected' : '' }}>{{ $u['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @elseif($day['pic'])
                                    <span class="badge badge-pic">{{ $day['pic'] }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <p>{{ __('messages.rotation_none_active') }}</p>
        </div>
    @endif
</div>
@endsection
