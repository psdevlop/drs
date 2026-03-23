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
                <li><a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}">{{ __('messages.announcement_board') }}</a></li>
                <li><a href="{{ route('oncall.index') }}" class="{{ request()->routeIs('oncall.*') ? 'active' : '' }}">{{ __('messages.on_call') }}</a></li>
                <li><a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') || request()->routeIs('admin.services.*') ? 'active' : '' }}">{{ __('messages.services') }}</a></li>
                @if(auth()->user()->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') && !request()->routeIs('admin.services.*') ? 'active' : '' }}">{{ __('messages.admin') }}</a></li>
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
    @hasSection('ckeditor')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
        function SimpleUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = function(loader) {
                return {
                    upload: function() {
                        return loader.file.then(function(file) {
                            return new Promise(function(resolve, reject) {
                                var data = new FormData();
                                data.append('upload', file);
                                var xhr = new XMLHttpRequest();
                                xhr.open('POST', '{{ route("editor.upload") }}', true);
                                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                                xhr.onload = function() {
                                    if (xhr.status >= 200 && xhr.status < 300) {
                                        var res = JSON.parse(xhr.responseText);
                                        resolve({ default: res.url });
                                    } else { reject('Upload failed'); }
                                };
                                xhr.onerror = function() { reject('Upload failed'); };
                                xhr.send(data);
                            });
                        });
                    }
                };
            };
        }
        document.querySelectorAll('.ckeditor-field').forEach(function(el) {
            ClassicEditor.create(el, {
                extraPlugins: [SimpleUploadAdapterPlugin],
                toolbar: ['heading', '|', 'bold', 'italic', 'underline', 'strikethrough', '|', 'bulletedList', 'numberedList', '|', 'link', 'imageUpload', 'blockQuote', 'insertTable', '|', 'undo', 'redo'],
                image: {
                    toolbar: ['imageTextAlternative', '|', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side']
                }
            }).catch(function(err) { console.error(err); });
        });
    </script>
    @endif
</body>
</html>
