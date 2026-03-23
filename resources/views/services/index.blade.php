@extends('layouts.app')
@section('title', __('messages.services'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.services_management') }}</h1>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">{{ __('messages.new_service') }}</a>
    @endif
</div>

<form method="GET" action="{{ route('services.index') }}" class="filter-bar">
    <div class="form-group">
        <label>{{ __('messages.type') }}</label>
        <select name="type" class="form-control filter-select">
            <option value="">{{ __('messages.all_types') }}</option>
            <option value="domain" {{ request('type') == 'domain' ? 'selected' : '' }}>{{ __('messages.service_domain') }}</option>
            <option value="hosting" {{ request('type') == 'hosting' ? 'selected' : '' }}>{{ __('messages.service_hosting') }}</option>
            <option value="cdn" {{ request('type') == 'cdn' ? 'selected' : '' }}>{{ __('messages.service_cdn') }}</option>
            <option value="website" {{ request('type') == 'website' ? 'selected' : '' }}>{{ __('messages.service_website') }}</option>
        </select>
    </div>
    <div class="form-group">
        <label>{{ __('messages.status') }}</label>
        <select name="status" class="form-control filter-select">
            <option value="">{{ __('messages.all_statuses') }}</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.service_active') }}</option>
            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>{{ __('messages.service_expired') }}</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('messages.service_suspended') }}</option>
        </select>
    </div>
    <div class="form-group">
        <label>{{ __('messages.search') }}</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('messages.search_services') }}">
    </div>
    <button type="submit" class="btn btn-outline">{{ __('messages.filter') }}</button>
    @if(request()->hasAny(['type', 'status', 'search']))
        <a href="{{ route('services.index') }}" class="btn btn-sm btn-secondary">{{ __('messages.clear') }}</a>
    @endif
</form>

<div class="card">
    @if($services->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.name') }}</th>
                        <th>{{ __('messages.type') }}</th>
                        <th>{{ __('messages.provider') }}</th>
                        <th>{{ __('messages.registrant') }}</th>
                        <th>{{ __('messages.expiration_date') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $service)
                        <tr>
                            <td>
                                <div class="text-bold">{{ $service->name }}</div>
                                @if($service->url)
                                    <div class="text-muted text-sm">{{ $service->url }}</div>
                                @endif
                            </td>
                            <td><span class="badge badge-service-{{ $service->type }}">{{ __('messages.service_' . $service->type) }}</span></td>
                            <td>{{ $service->provider ?? '-' }}</td>
                            <td>
                                <div>{{ $service->registrant ?? '-' }}</div>
                                @if($service->registrant_id)
                                    <div class="text-muted text-sm">{{ $service->registrant_id }}</div>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                @if($service->expiration_date)
                                    <span class="{{ $service->isExpired() ? 'text-danger' : ($service->isExpiringSoon() ? 'text-warning' : '') }}">
                                        {{ $service->expiration_date->format('M d, Y') }}
                                    </span>
                                    @if($service->isExpired())
                                        <div class="text-danger text-sm">{{ __('messages.service_expired') }}</div>
                                    @elseif($service->isExpiringSoon())
                                        <div class="text-warning text-sm">{{ __('messages.service_expiring_soon') }}</div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="badge badge-service-status-{{ $service->status }}">{{ __('messages.service_' . $service->status) }}</span></td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('services.show', $service) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline">{{ __('messages.edit') }}</a>
                                        <form action="{{ route('admin.services.destroy', $service) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_service_confirm') }}')">
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
        <div class="pagination-wrapper">{{ $services->withQueryString()->links() }}</div>
    @else
        <div class="empty-state">
            <p>{{ __('messages.no_services_found') }}</p>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.services.create') }}" class="btn btn-primary">{{ __('messages.create_first_service') }}</a>
            @endif
        </div>
    @endif
</div>
@endsection
