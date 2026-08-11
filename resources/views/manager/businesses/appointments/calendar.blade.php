@extends('layouts.app')

@section('css')
@parent
@endsection

@section('content')
<div id="calendar"></div>

<div class="p-3 bg-light rounded" data-ical-url>{{ $icalURL }}</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/calendar.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    tgInitCalendar('#calendar', {
        defaultDate: new Date(),
        locale: timegrid.lang,
        defaultView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        minTime: timegrid.minTime,
        maxTime: timegrid.maxTime,
        slotDuration: timegrid.slotDuration,
        businessHours: {
            startTime: timegrid.minTime,
            endTime: timegrid.maxTime,
            daysOfWeek: [1, 2, 3, 4, 5, 6]
        },
        events: timegrid.events
    });
});
</script>
@endpush
