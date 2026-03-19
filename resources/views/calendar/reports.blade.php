@extends('layouts.app')
@section('title', __('messages.report_calendar'))
@section('content')
<div class="page-header">
    <h1>{{ __('messages.report_calendar') }}</h1>
    <div class="actions">
        <a href="{{ route('reports.create') }}" class="btn btn-success">{{ __('messages.new_report') }}</a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline">{{ __('messages.back_to_reports') }}</a>
    </div>
</div>

<div class="card">
    <div id="reportCalendar"></div>
</div>

<div id="reportTooltip" class="calendar-tooltip" style="display:none;">
    <div class="tooltip-title"></div>
    <div class="tooltip-meta">
        <div><strong>{{ __('messages.report_date') }}:</strong> <span class="tooltip-report-date"></span></div>
        <div><strong>{{ __('messages.summary') }}:</strong> <span class="tooltip-summary"></span></div>
        <div><strong>{{ __('messages.related_task') }}:</strong> <span class="tooltip-task"></span></div>
        <div><strong>{{ __('messages.challenges') }}:</strong> <span class="tooltip-challenges"></span></div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
@if(app()->getLocale() === 'ko')
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.17/locales/ko.global.min.js"></script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('reportCalendar');
    var tooltip = document.getElementById('reportTooltip');
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
            url: '{{ route("calendar.reports.data") }}',
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
            tooltip.querySelector('.tooltip-report-date').textContent = props.report_date;
            tooltip.querySelector('.tooltip-summary').textContent = props.summary;
            tooltip.querySelector('.tooltip-task').textContent = props.task;
            tooltip.querySelector('.tooltip-challenges').textContent = props.challenges;

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
