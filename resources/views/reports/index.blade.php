@extends('layouts.app')
@section('title', __('messages.reports'))
@section('content')
<div class="page-header">
    <h1>{{ auth()->user()->isAdmin() ? __('messages.all_daily_reports') : __('messages.my_daily_reports') }}</h1>
    <div class="actions">
        <a href="{{ route('calendar.reports') }}" class="btn btn-outline">{{ __('messages.calendar') }}</a>
        <a href="{{ route('reports.create') }}" class="btn btn-success">{{ __('messages.new_report') }}</a>
    </div>
</div>

<form method="GET" action="{{ route('reports.index') }}" class="filter-bar">
    <div class="form-group">
        <label>{{ __('messages.date') }}</label>
        <input type="date" name="date" class="form-control filter-date" value="{{ request('date') }}">
    </div>
    <button type="submit" class="btn btn-outline">{{ __('messages.filter') }}</button>
    @if(request('date'))
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">{{ __('messages.clear') }}</a>
    @endif
</form>

<div class="card">
    @if($reports->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        @if(auth()->user()->isAdmin())
                            <th>{{ __('messages.submitted_by') }}</th>
                        @endif
                        <th>{{ __('messages.summary') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr>
                            <td class="nowrap text-bold">{{ $report->report_date->format('M d, Y (D)') }}</td>
                            @if(auth()->user()->isAdmin())
                                <td>{{ $report->user->name }}</td>
                            @endif
                            <td>{{ Str::limit($report->summary, 80) }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
                                    <a href="{{ route('reports.edit', $report) }}" class="btn btn-sm btn-outline">{{ __('messages.edit') }}</a>
                                    <form action="{{ route('reports.destroy', $report) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_report_confirm') }}')">
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
        <div class="pagination-wrapper">{{ $reports->withQueryString()->links() }}</div>
    @else
        <div class="empty-state">
            <p>{{ __('messages.no_reports_found') }}</p>
            <a href="{{ route('reports.create') }}" class="btn btn-success">{{ __('messages.submit_first_report') }}</a>
        </div>
    @endif
</div>
@endsection
