/**
 * Calendar module — framework-agnostic FullCalendar replacement.
 * Uses @fullcalendar/core with dayGrid and timeGrid plugins.
 */
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';

export function initCalendar(selector, options) {
    const el = document.querySelector(selector);
    if (!el) return null;

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin],
        initialView: options.defaultView || 'timeGridWeek',
        initialDate: options.defaultDate || new Date(),
        locale: options.locale || 'en',
        headerToolbar: options.headerToolbar || {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        allDaySlot: false,
        businessHours: options.businessHours || false,
        slotMinTime: options.minTime || '06:00:00',
        slotMaxTime: options.maxTime || '22:00:00',
        slotDuration: options.slotDuration || '00:30:00',
        events: options.events || [],
    });

    calendar.render();
    return calendar;
}

window.tgInitCalendar = initCalendar;
