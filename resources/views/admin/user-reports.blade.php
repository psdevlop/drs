@extends('layouts.app')
@section('title', __('messages.user_reports', ['name' => $user->name]))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.user_reports', ['name' => $user->name]) }}</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">{{ __('messages.back_to_admin') }}</a>
</div>

<div class="card">
    @if($reports->count())
        <div class="table-wrapper">
            <table>
                <thead><tr><th>{{ __('messages.date') }}</th><th>{{ __('messages.summary') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td class="nowrap text-bold">{{ $report->report_date->format('M d, Y (D)') }}</td>
                        <td>{{ Str::limit($report->summary, 80) }}</td>
                        <td><a href="{{ route('admin.show-report', $report) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">{{ $reports->links() }}</div>
    @else
        <div class="empty-state"><p>{{ __('messages.no_reports_from_user') }}</p></div>
    @endif
</div>
@endsection
