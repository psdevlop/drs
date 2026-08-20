@extends('layouts.app')
@section('title', __('messages.seo_performance'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.seo_performance') }}</h1>
    <a href="{{ route('seo.performance') }}" class="btn btn-primary">{{ __('messages.seo_view_performance') }}</a>
</div>

<p class="text-muted text-sm" style="margin-top:-0.5rem;margin-bottom:1rem;">
    {{ __('messages.seo_keywords_hint') }}
</p>

<div class="card">
    <div class="card-title">{{ __('messages.seo_keywords') }}</div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.seo_keyword') }}</th>
                    <th style="text-align:right;">{{ __('messages.seo_website_rank') }}</th>
                    <th style="text-align:right;">{{ __('messages.seo_best_rank') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($keywords as $row)
                    <tr>
                        <td><div class="text-bold">{{ $row['keyword'] }}</div></td>
                        <td style="text-align:right;">
                            @if($row['website_rank'] !== null)
                                {{ $row['website_rank'] }}
                            @else
                                <span class="text-muted">{{ __('messages.seo_not_ranked') }}</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if($row['best_rank'] !== null)
                                {{ $row['best_rank'] }}
                                <span class="text-muted text-sm">({{ $row['best_property'] }})</span>
                            @else
                                <span class="text-muted">{{ __('messages.seo_not_ranked') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><div class="empty-state"><p>{{ __('messages.no_seo_data') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
