/**
 * Datetime helpers — native date/time inputs replace bootstrap-timepicker.
 * Calendar rendering lives in resources/js/calendar.js (FullCalendar v6).
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.timepicker, input[data-provide="timepicker"]').forEach(function (el) {
        if (el.type === 'text') {
            el.type = 'time';
        }
    });

    document.querySelectorAll('.datepicker, input[data-provide="datepicker"]').forEach(function (el) {
        if (el.type === 'text') {
            el.type = 'date';
        }
    });
});
