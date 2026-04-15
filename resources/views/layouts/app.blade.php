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
                <li class="navbar-dropdown">
                    <a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks.*') || request()->routeIs('calendar') ? 'active' : '' }}">{{ __('messages.tasks') }} <span class="caret">&#9662;</span></a>
                    <ul class="navbar-submenu">
                        <li><a href="{{ route('tasks.index') }}">{{ __('messages.my_tasks') }}</a></li>
                        <li><a href="{{ route('tasks.create') }}">{{ __('messages.new_task') }}</a></li>
                        <li><a href="{{ route('calendar') }}">{{ __('messages.task_calendar') }}</a></li>
                    </ul>
                </li>
                <li class="navbar-dropdown">
                    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">{{ __('messages.reports') }} <span class="caret">&#9662;</span></a>
                    <ul class="navbar-submenu">
                        <li><a href="{{ route('reports.index') }}">{{ __('messages.my_daily_reports') }}</a></li>
                        <li><a href="{{ route('reports.create') }}">{{ __('messages.new_report') }}</a></li>
                    </ul>
                </li>
                <li><a href="{{ route('schedule') }}" class="{{ request()->routeIs('schedule*') ? 'active' : '' }}">{{ __('messages.schedule_management') }}</a></li>
                <li class="navbar-dropdown">
                    <a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}">{{ __('messages.announcement_board') }} <span class="caret">&#9662;</span></a>
                    <ul class="navbar-submenu">
                        <li><a href="{{ route('announcements.index') }}">{{ __('messages.view_all_announcements') }}</a></li>
                        @if(auth()->user()->isAdmin())
                            <li><a href="{{ route('announcements.create') }}">{{ __('messages.new_announcement') }}</a></li>
                        @endif
                    </ul>
                </li>
                <li><a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}">{{ __('messages.attendance') }}</a></li>
                <li class="navbar-dropdown">
                    <a href="{{ route('oncall.index') }}" class="{{ request()->routeIs('oncall.*') || request()->routeIs('oncall.rotations*') ? 'active' : '' }}">{{ __('messages.on_call') }} <span class="caret">&#9662;</span></a>
                    <ul class="navbar-submenu">
                        <li><a href="{{ route('oncall.index') }}">{{ __('messages.oncall_schedule') }}</a></li>
                        <li><a href="{{ route('oncall.rotations') }}">{{ __('messages.rotation_schedules') }}</a></li>
                        <li><a href="{{ route('oncall.rotations.create') }}">{{ __('messages.rotation_new') }}</a></li>
                    </ul>
                </li>
                <li class="navbar-dropdown">
                    <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') || request()->routeIs('admin.services.*') ? 'active' : '' }}">{{ __('messages.services') }} <span class="caret">&#9662;</span></a>
                    <ul class="navbar-submenu">
                        <li><a href="{{ route('services.index') }}">{{ __('messages.services') }}</a></li>
                        @if(auth()->user()->isAdmin())
                            <li><a href="{{ route('admin.services.create') }}">{{ __('messages.new_service') }}</a></li>
                        @endif
                    </ul>
                </li>
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

        (function() {
            if (!('Notification' in window)) return;

            var POLL_URL = '{{ route("notifications.poll") }}';
            var NOTIF_INDEX_URL = '{{ route("notifications.index") }}';
            var STORAGE_KEY = 'drs_last_notif_id_{{ auth()->id() }}';
            var POLL_INTERVAL = 30000;

            function requestPermissionOnce() {
                if (Notification.permission === 'default') {
                    var ask = function() {
                        Notification.requestPermission();
                        document.removeEventListener('click', ask);
                    };
                    document.addEventListener('click', ask);
                }
            }

            function updateBadge(count) {
                var bell = document.querySelector('.notification-bell');
                if (!bell) return;
                var badge = bell.querySelector('.notification-badge');
                if (count > 0) {
                    var display = count > 99 ? '99+' : count;
                    if (badge) {
                        badge.textContent = display;
                    } else {
                        var newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = display;
                        bell.querySelector('.notification-icon').appendChild(newBadge);
                    }
                } else if (badge) {
                    badge.remove();
                }
            }

            function showBrowserNotification(n) {
                if (Notification.permission !== 'granted') return;
                var notif = new Notification(n.title || 'Notification', {
                    body: n.message || '',
                    tag: 'drs-notif-' + n.id,
                });
                notif.onclick = function() {
                    window.focus();
                    window.location.href = n.link || NOTIF_INDEX_URL;
                    notif.close();
                };
                setTimeout(function() { notif.close(); }, 8000);
            }

            function poll() {
                var sinceId = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
                fetch(POLL_URL + '?since_id=' + sinceId, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.ok ? r.json() : null; })
                    .then(function(data) {
                        if (!data) return;
                        updateBadge(data.unread_count || 0);
                        var list = data.notifications || [];
                        if (list.length === 0) return;
                        var maxId = sinceId;
                        list.forEach(function(n) {
                            if (sinceId > 0) showBrowserNotification(n);
                            if (n.id > maxId) maxId = n.id;
                        });
                        localStorage.setItem(STORAGE_KEY, String(maxId));
                    })
                    .catch(function() {});
            }

            requestPermissionOnce();
            // Seed last-seen id on very first visit so user doesn't get flooded with old notifications
            if (localStorage.getItem(STORAGE_KEY) === null) {
                fetch(POLL_URL + '?since_id=0', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.ok ? r.json() : null; })
                    .then(function(data) {
                        if (!data || !data.notifications) return;
                        var maxId = 0;
                        data.notifications.forEach(function(n) { if (n.id > maxId) maxId = n.id; });
                        localStorage.setItem(STORAGE_KEY, String(maxId));
                    });
            }
            setInterval(poll, POLL_INTERVAL);
        })();
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
