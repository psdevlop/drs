<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DRS') }} - @yield('title', __('messages.daily_report_system'))</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    @auth
    <nav class="navbar">
        <div class="navbar-top">
            <a href="{{ route('dashboard') }}" class="navbar-brand"><span class="brand-daily">Daily</span><span class="brand-pulse">Pulse</span></a>
            <button class="navbar-toggler" onclick="document.getElementById('navbarCollapse').classList.toggle('open')" aria-label="Toggle navigation">&#9776;</button>
        </div>
        <div class="navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav">
                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">{{ __('messages.dashboard') }}</a></li>
                <li><a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks.*') || request()->routeIs('calendar') ? 'active' : '' }}">{{ __('messages.tasks') }}</a></li>
                <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">{{ __('messages.reports') }}</a></li>
                <li><a href="{{ route('schedule') }}" class="{{ request()->routeIs('schedule*') ? 'active' : '' }}">{{ __('messages.schedule_management') }}</a></li>
                @if(auth()->user()->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">{{ __('messages.admin') }}</a></li>
                @endif
            </ul>
            <div class="navbar-right">
                <div class="lang-switcher">
                    <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active-lang' : '' }}">EN</a>
                    <span class="lang-switcher-divider">|</span>
                    <a href="{{ route('locale.switch', 'ko') }}" class="{{ app()->getLocale() === 'ko' ? 'active-lang' : '' }}">KO</a>
                </div>
                @php $unreadCount = auth()->user()->unreadNotificationsCount(); @endphp
                <div class="notification-bell" id="notificationBell">
                    <a href="{{ route('notifications.index') }}" class="notification-icon" title="{{ __('messages.notifications') }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </a>
                </div>
                <div class="user-dropdown" id="userDropdown">
                    <button type="button" class="user-dropdown-toggle" onclick="document.getElementById('userDropdown').classList.toggle('open')">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="user-avatar">
                        @else
                            <div class="user-avatar-placeholder">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        @endif
                        <span class="caret">&#9660;</span>
                    </button>
                    <div class="user-dropdown-menu">
                        <div class="dropdown-header">
                            <div class="name">{{ auth()->user()->name }}</div>
                            <div class="email">{{ auth()->user()->email }}</div>
                        </div>
                        <a href="{{ route('profile.edit') }}">{{ __('messages.edit_profile') }}</a>
                        <a href="{{ route('password.change') }}">{{ __('messages.change_password') }}</a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-danger">{{ __('messages.logout') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
    @auth
    <script>
        document.addEventListener('click', function(e) {
            var dd = document.getElementById('userDropdown');
            if (dd && !dd.contains(e.target)) dd.classList.remove('open');
        });
    </script>
    @endauth
</body>
</html>
