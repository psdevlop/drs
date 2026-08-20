@extends('layouts.app')
@section('title', __('messages.seo_performance'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.seo_performance') }}</h1>
    <a href="{{ route('seo.index') }}" class="btn btn-sm btn-secondary">&#8592; {{ __('messages.seo_all_links') }}</a>
</div>

<form method="GET" action="{{ route('seo.performance') }}" class="filter-bar">
    <div class="form-group">
        <label>{{ __('messages.seo_from') }}</label>
        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
    </div>
    <div class="form-group">
        <label>{{ __('messages.seo_to') }}</label>
        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
    </div>
    <div class="form-group">
        <label>{{ __('messages.seo_select_keyword') }}</label>
        <select name="keyword" class="form-control">
            @foreach($keywords as $kw)
                <option value="{{ $kw }}" @selected($kw === $selectedKeyword)>{{ $kw }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-outline">{{ __('messages.filter') }}</button>
    @if($isFiltered)
        <a href="{{ route('seo.performance') }}" class="btn btn-sm btn-secondary">{{ __('messages.clear') }}</a>
    @endif
</form>

@if(! $hasData)
    <div class="card">
        <div class="empty-state">
            <p>{{ __('messages.no_seo_data') }}</p>
            <p class="text-muted text-sm">{{ __('messages.no_seo_data_hint') }}</p>
        </div>
    </div>
@else
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-value">{{ number_format($kpis['keywords']) }}</div>
            <div class="stat-label">{{ __('messages.seo_keywords') }}</div>
        </div>
        <div class="stat-card green">
            <div class="stat-value">{{ number_format($kpis['in_top_10']) }}</div>
            <div class="stat-label">{{ __('messages.seo_in_top_10') }}</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-value">{{ $kpis['avg_rank'] ?? '-' }}</div>
            <div class="stat-label">{{ __('messages.seo_avg_rank') }}</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-value">{{ $kpis['best_rank'] ?? '-' }}</div>
            <div class="stat-label">{{ __('messages.seo_best_rank') }}</div>
        </div>
    </div>

    <div class="two-col-grid">
        <div class="card">
            <div class="card-title">{{ __('messages.seo_website_rank_by_keyword') }}</div>
            <div style="position:relative;height:280px;"><canvas id="seoWebsiteChart"></canvas></div>
        </div>
        <div class="card">
            <div class="card-title">{{ __('messages.seo_property_ranks') }} &mdash; {{ $selectedKeyword }}</div>
            <div style="position:relative;height:280px;"><canvas id="seoKeywordChart"></canvas></div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">{{ __('messages.seo_latest_breakdown') }}</div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.seo_keyword') }}</th>
                        @foreach($propertyLabels as $label)
                            <th style="text-align:right;">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($breakdown as $row)
                        <tr>
                            <td><div class="text-bold">{{ $row['keyword'] }}</div></td>
                            @foreach($row['cells'] as $position)
                                <td style="text-align:right;">
                                    @if($position !== null)
                                        {{ $position }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var website = @json($chartWebsite);
            var keyword = @json($chartKeyword);
            // One colour per series, assigned by dataset order.
            var PALETTE = ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#84cc16'];
            var common = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: true, position: 'bottom' } },
                // Lower rank is better -> invert the y axis.
                scales: { y: { reverse: true, beginAtZero: false } },
            };

            // Multi-line rank chart. spanGaps:false so "not ranked" reads as a break.
            function build(chart) {
                return {
                    type: 'line',
                    data: {
                        labels: chart.labels,
                        datasets: chart.datasets.map(function (d, i) {
                            var col = PALETTE[i % PALETTE.length];
                            return {
                                label: d.label,
                                data: d.position,
                                borderColor: col,
                                backgroundColor: col + '22',
                                tension: 0.3,
                                spanGaps: false,
                                fill: false,
                                pointRadius: 0,
                                borderWidth: 2,
                            };
                        }),
                    },
                    options: common,
                };
            }

            new Chart(document.getElementById('seoWebsiteChart'), build(website));
            new Chart(document.getElementById('seoKeywordChart'), build(keyword));
        })();
    </script>
@endif
@endsection
