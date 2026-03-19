@extends('layouts.app')
@section('title', __('messages.calendar'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.task_calendar') }}</h1>
    <div class="actions">
        <a href="{{ route('reports.create') }}" class="btn btn-success">{{ __('messages.new_report') }}</a>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline">{{ __('messages.back_to_tasks') }}</a>
    </div>
</div>

<div class="card">
    <div class="calendar-legend">
        <span class="legend-item"><span class="legend-dot legend-pending"></span> {{ __('messages.pending') }}</span>
        <span class="legend-item"><span class="legend-dot legend-in-progress"></span> {{ __('messages.in_progress') }}</span>
        <span class="legend-item"><span class="legend-dot legend-completed"></span> {{ __('messages.completed') }}</span>
    </div>
    <div id="calendar"></div>
</div>

<div id="taskTooltip" class="calendar-tooltip" style="display:none;">
    <div class="tooltip-title"></div>
    <div class="tooltip-meta">
        <div><strong>{{ __('messages.status') }}:</strong> <span class="tooltip-status"></span></div>
        <div><strong>{{ __('messages.priority') }}:</strong> <span class="tooltip-priority"></span></div>
        <div><strong>{{ __('messages.progress') }}:</strong> <span class="tooltip-progress"></span></div>
        <div><strong>{{ __('messages.assigned_to') }}:</strong> <span class="tooltip-assignee"></span></div>
        <div><strong>{{ __('messages.start_date') }}:</strong> <span class="tooltip-start-date"></span></div>
        <div><strong>{{ __('messages.expected_end_date') }}:</strong> <span class="tooltip-end-date"></span></div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
@if(app()->getLocale() === 'ko')
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.17/locales/ko.global.min.js"></script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var tooltip = document.getElementById('taskTooltip');
    var locale = '{{ app()->getLocale() }}';

    var isMobile = window.innerWidth < 768;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: isMobile ? 'listWeek' : 'dayGridMonth',
        locale: locale,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: isMobile ? 'dayGridMonth,listWeek' : 'dayGridMonth,timeGridWeek'
        },
        buttonText: {
            today: locale === 'ko' ? '오늘' : 'Today',
            month: locale === 'ko' ? '월' : 'Month',
            week: locale === 'ko' ? '주' : 'Week',
            list: locale === 'ko' ? '목록' : 'List'
        },
        windowResize: function(view) {
            var mobile = window.innerWidth < 768;
            calendar.setOption('headerToolbar', {
                left: 'prev,next today',
                center: 'title',
                right: mobile ? 'dayGridMonth,listWeek' : 'dayGridMonth,timeGridWeek'
            });
        },
        events: {
            url: '{{ route("calendar.tasks") }}',
            method: 'GET',
            failure: function() {
                alert('{{ __("messages.calendar_load_error") }}');
            }
        },
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        },
        eventMouseEnter: function(info) {
            var props = info.event.extendedProps;
            tooltip.querySelector('.tooltip-title').textContent = info.event.title;
            tooltip.querySelector('.tooltip-status').textContent = props.status.replace('_', ' ');
            tooltip.querySelector('.tooltip-priority').textContent = props.priority;
            tooltip.querySelector('.tooltip-progress').textContent = props.progress + '%';
            tooltip.querySelector('.tooltip-assignee').textContent = props.assignee;
            tooltip.querySelector('.tooltip-start-date').textContent = props.start_date;
            tooltip.querySelector('.tooltip-end-date').textContent = props.end_date;

            tooltip.style.display = 'block';
            var rect = info.el.getBoundingClientRect();
            tooltip.style.top = (rect.bottom + window.scrollY + 5) + 'px';
            tooltip.style.left = (rect.left + window.scrollX) + 'px';
        },
        eventMouseLeave: function() {
            tooltip.style.display = 'none';
        },
        height: 'auto',
        eventDisplay: 'block',
        dayMaxEvents: 3
    });

    calendar.render();
});
</script>
@endsection
