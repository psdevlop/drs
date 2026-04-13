@extends('layouts.app')
@section('title', __('messages.schedule_management'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.schedule_management') }}</h1>
    <div class="actions">
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">{{ __('messages.new_task') }}</a>
        <a href="{{ route('reports.create') }}" class="btn btn-success">{{ __('messages.new_report') }}</a>
    </div>
</div>

<div class="card">
    <div class="calendar-legend">
        <span class="legend-item"><span class="legend-dot legend-pending"></span> {{ __('messages.pending') }}</span>
        <span class="legend-item"><span class="legend-dot legend-in-progress"></span> {{ __('messages.in_progress') }}</span>
        <span class="legend-item"><span class="legend-dot legend-completed"></span> {{ __('messages.completed') }}</span>
        <span class="legend-item"><span class="legend-dot legend-report"></span> {{ __('messages.reports') }}</span>
        <span class="legend-item"><span class="legend-dot legend-oncall"></span> {{ __('messages.on_call') }}</span>
        <span class="legend-item"><span class="legend-dot legend-holiday"></span> {{ __('messages.holiday') }}</span>
    </div>
    <div id="scheduleCalendar"></div>
</div>

<div id="scheduleTooltip" class="calendar-tooltip" style="display:none;">
    <div class="tooltip-title"></div>
    <div class="tooltip-task-meta tooltip-meta" style="display:none;">
        <div><strong>{{ __('messages.status') }}:</strong> <span class="tooltip-status"></span></div>
        <div><strong>{{ __('messages.priority') }}:</strong> <span class="tooltip-priority"></span></div>
        <div><strong>{{ __('messages.progress') }}:</strong> <span class="tooltip-progress"></span></div>
        <div><strong>{{ __('messages.assigned_to') }}:</strong> <span class="tooltip-assignee"></span></div>
        <div><strong>{{ __('messages.start_date') }}:</strong> <span class="tooltip-start-date"></span></div>
        <div><strong>{{ __('messages.expected_end_date') }}:</strong> <span class="tooltip-end-date"></span></div>
    </div>
    <div class="tooltip-report-meta tooltip-meta" style="display:none;">
        <div><strong>{{ __('messages.report_date') }}:</strong> <span class="tooltip-report-date"></span></div>
        <div><strong>{{ __('messages.summary') }}:</strong> <span class="tooltip-summary"></span></div>
        <div><strong>{{ __('messages.related_task') }}:</strong> <span class="tooltip-task"></span></div>
        <div><strong>{{ __('messages.challenges') }}:</strong> <span class="tooltip-challenges"></span></div>
    </div>
    <div class="tooltip-oncall-meta tooltip-meta" style="display:none;">
        <div><strong>{{ __('messages.date') }}:</strong> <span class="tooltip-oncall-date"></span></div>
        <div><strong>{{ __('messages.oncall_duty_users') }}:</strong> <span class="tooltip-oncall-users"></span></div>
        <div><strong>{{ __('messages.person_in_charge') }}:</strong> <span class="tooltip-oncall-pic"></span></div>
    </div>
    <div class="tooltip-holiday-meta tooltip-meta" style="display:none;">
        <div><strong>{{ __('messages.date') }}:</strong> <span class="tooltip-holiday-date"></span></div>
        <div><strong>{{ __('messages.holiday_reason') }}:</strong> <span class="tooltip-holiday-reason"></span></div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
@if(app()->getLocale() === 'ko')
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.17/locales/ko.global.min.js"></script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('scheduleCalendar');
    var tooltip = document.getElementById('scheduleTooltip');
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
            url: '{{ route("schedule.data") }}',
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
            var taskMeta = tooltip.querySelector('.tooltip-task-meta');
            var reportMeta = tooltip.querySelector('.tooltip-report-meta');
            var oncallMeta = tooltip.querySelector('.tooltip-oncall-meta');
            var holidayMeta = tooltip.querySelector('.tooltip-holiday-meta');

            tooltip.querySelector('.tooltip-title').textContent = info.event.title;

            taskMeta.style.display = 'none';
            reportMeta.style.display = 'none';
            oncallMeta.style.display = 'none';
            holidayMeta.style.display = 'none';

            if (props.type === 'holiday') {
                holidayMeta.style.display = 'block';
                tooltip.querySelector('.tooltip-holiday-date').textContent = props.date;
                tooltip.querySelector('.tooltip-holiday-reason').textContent = props.reason;
            } else if (props.type === 'oncall') {
                oncallMeta.style.display = 'block';
                tooltip.querySelector('.tooltip-oncall-date').textContent = props.date;
                tooltip.querySelector('.tooltip-oncall-users').textContent = props.users;
                tooltip.querySelector('.tooltip-oncall-pic').textContent = props.pic;
            } else if (props.type === 'report') {
                reportMeta.style.display = 'block';
                tooltip.querySelector('.tooltip-report-date').textContent = props.report_date;
                tooltip.querySelector('.tooltip-summary').textContent = props.summary;
                tooltip.querySelector('.tooltip-task').textContent = props.task;
                tooltip.querySelector('.tooltip-challenges').textContent = props.challenges;
            } else {
                taskMeta.style.display = 'block';
                tooltip.querySelector('.tooltip-status').textContent = props.status.replace('_', ' ');
                tooltip.querySelector('.tooltip-priority').textContent = props.priority;
                tooltip.querySelector('.tooltip-progress').textContent = props.progress + '%';
                tooltip.querySelector('.tooltip-assignee').textContent = props.assignee;
                tooltip.querySelector('.tooltip-start-date').textContent = props.start_date;
                tooltip.querySelector('.tooltip-end-date').textContent = props.end_date;
            }

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
        dayMaxEvents: 4
    });

    calendar.render();
});
</script>
@endsection
