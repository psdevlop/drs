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
    <div class="calendar-legend" id="scheduleLegend">
        <button type="button" class="legend-item" data-filter="pending"><span class="legend-dot legend-pending"></span> {{ __('messages.pending') }}</button>
        <button type="button" class="legend-item" data-filter="in_progress"><span class="legend-dot legend-in-progress"></span> {{ __('messages.in_progress') }}</button>
        <button type="button" class="legend-item" data-filter="completed"><span class="legend-dot legend-completed"></span> {{ __('messages.completed') }}</button>
        <button type="button" class="legend-item" data-filter="report"><span class="legend-dot legend-report"></span> {{ __('messages.reports') }}</button>
        <button type="button" class="legend-item" data-filter="onduty"><span class="legend-dot legend-onduty"></span> {{ __('messages.on_duty') }}</button>
        <button type="button" class="legend-item" data-filter="oncall"><span class="legend-dot legend-oncall"></span> {{ __('messages.on_call') }}</button>
        <button type="button" class="legend-item" data-filter="holiday"><span class="legend-dot legend-holiday"></span> {{ __('messages.holiday') }}</button>
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
<style>
    #scheduleCalendar .fc-day-sat .fc-daygrid-day-number,
    #scheduleCalendar .fc-day-sat .fc-col-header-cell-cushion {
        color: #dc2626 !important;
    }

    #scheduleLegend .legend-item {
        background: transparent;
        border: 1px solid transparent;
        border-radius: 9999px;
        padding: 4px 10px;
        cursor: pointer;
        font: inherit;
        color: inherit;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    #scheduleLegend .legend-item:hover {
        background: rgba(0, 0, 0, 0.04);
    }
    #scheduleLegend .legend-item.is-active {
        background: rgba(59, 130, 246, 0.12);
        border-color: rgba(59, 130, 246, 0.45);
    }
    #scheduleCalendar.filter-pending .fc-event:not(.fc-cat-pending),
    #scheduleCalendar.filter-in_progress .fc-event:not(.fc-cat-in_progress),
    #scheduleCalendar.filter-completed .fc-event:not(.fc-cat-completed),
    #scheduleCalendar.filter-report .fc-event:not(.fc-cat-report),
    #scheduleCalendar.filter-onduty .fc-event:not(.fc-cat-onduty),
    #scheduleCalendar.filter-oncall .fc-event:not(.fc-cat-oncall),
    #scheduleCalendar.filter-holiday .fc-event:not(.fc-cat-holiday),
    #scheduleCalendar.filter-pending .fc-list-event:not(.fc-cat-pending),
    #scheduleCalendar.filter-in_progress .fc-list-event:not(.fc-cat-in_progress),
    #scheduleCalendar.filter-completed .fc-list-event:not(.fc-cat-completed),
    #scheduleCalendar.filter-report .fc-list-event:not(.fc-cat-report),
    #scheduleCalendar.filter-onduty .fc-list-event:not(.fc-cat-onduty),
    #scheduleCalendar.filter-oncall .fc-list-event:not(.fc-cat-oncall),
    #scheduleCalendar.filter-holiday .fc-list-event:not(.fc-cat-holiday) {
        display: none !important;
    }
</style>
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
            today: @json(__('messages.calendar_today')),
            month: @json(__('messages.calendar_month')),
            week: @json(__('messages.calendar_week')),
            list: @json(__('messages.calendar_list'))
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
        eventClassNames: function(arg) {
            var props = arg.event.extendedProps;
            if (props.type === 'task') {
                return ['fc-cat-' + (props.status_key || 'pending')];
            }
            if (props.type === 'report') return ['fc-cat-report'];
            if (props.type === 'onduty') return ['fc-cat-onduty'];
            if (props.type === 'oncall') return ['fc-cat-oncall'];
            if (props.type === 'holiday') return ['fc-cat-holiday'];
            return [];
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
            } else if (props.type === 'oncall' || props.type === 'onduty') {
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

    var legend = document.getElementById('scheduleLegend');
    var filterClasses = ['filter-pending','filter-in_progress','filter-completed','filter-report','filter-onduty','filter-oncall','filter-holiday'];
    var defaultView = isMobile ? 'listWeek' : 'dayGridMonth';
    var activeFilter = null;

    legend.addEventListener('click', function(e) {
        var btn = e.target.closest('.legend-item');
        if (!btn) return;
        var filter = btn.dataset.filter;

        if (activeFilter === filter) {
            activeFilter = null;
        } else {
            activeFilter = filter;
        }

        legend.querySelectorAll('.legend-item').forEach(function(el) {
            el.classList.toggle('is-active', el.dataset.filter === activeFilter);
        });

        filterClasses.forEach(function(cls) { calendarEl.classList.remove(cls); });
        if (activeFilter) {
            calendarEl.classList.add('filter-' + activeFilter);
            calendar.changeView('listYear');
        } else {
            calendar.changeView(defaultView);
        }
    });
});
</script>
@endsection
