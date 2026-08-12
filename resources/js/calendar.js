/**
 * Calendar module — FullCalendar wrapper with day/week/month views,
 * event CRUD, drag-and-drop rescheduling, and resize support.
 */
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

export function initCalendar(selector, options) {
    var el = document.querySelector(selector);
    if (!el) return null;

    var calendarOptions = {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: options.defaultView || 'timeGridWeek',
        initialDate: options.defaultDate || new Date(),
        locale: options.locale || 'en',
        allDaySlot: false,
        businessHours: options.businessHours || false,
        slotMinTime: options.minTime || '06:00:00',
        slotMaxTime: options.maxTime || '22:00:00',
        slotDuration: options.slotDuration || '00:30:00',
        events: options.events || [],
        editable: options.editable || false,
        droppable: options.droppable || false,
        selectable: options.selectable || false,
        eventResizableFromStart: true,
        nowIndicator: true,
        dayMaxEvents: true,
        eventDisplay: 'block',
    };

    if (options.headerToolbar !== false) {
        calendarOptions.headerToolbar = options.headerToolbar || {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        };
    } else {
        calendarOptions.headerToolbar = {
            left: 'prev,next today',
            center: 'title',
            right: '',
        };
    }

    if (options.dateClick) calendarOptions.dateClick = options.dateClick;
    if (options.eventClick) calendarOptions.eventClick = options.eventClick;
    if (options.eventDrop) calendarOptions.eventDrop = options.eventDrop;
    if (options.eventResize) calendarOptions.eventResize = options.eventResize;
    if (options.eventsSet) calendarOptions.eventsSet = options.eventsSet;

    var calendar = new Calendar(el, calendarOptions);
    calendar.render();
    return calendar;
}

window.tgInitCalendar = initCalendar;
