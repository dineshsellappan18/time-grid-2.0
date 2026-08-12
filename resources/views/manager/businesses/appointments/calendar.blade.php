@extends('layouts.app')

@section('title', trans('manager.businesses.agenda.title', ['default' => 'Calendar']))
@section('subtitle', $business->name)

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-12">

            {{-- Calendar Header --}}
            <div class="tg-cal-header">
                <div class="tg-cal-header__left">
                    <h2 class="tg-cal-header__title">
                        <i class="fa fa-calendar"></i> Scheduling Calendar
                    </h2>
                </div>
                <div class="tg-cal-header__center">
                    <div class="tg-cal-view-toggle" id="calViewToggle">
                        <button type="button" class="tg-cal-view-toggle__btn" data-view="timeGridDay">
                            <i class="fa fa-calendar-o"></i> Day
                        </button>
                        <button type="button" class="tg-cal-view-toggle__btn is-active" data-view="timeGridWeek">
                            <i class="fa fa-columns"></i> Week
                        </button>
                        <button type="button" class="tg-cal-view-toggle__btn" data-view="dayGridMonth">
                            <i class="fa fa-th"></i> Month
                        </button>
                    </div>
                </div>
                <div class="tg-cal-header__right">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#eventCreateModal">
                        <i class="fa fa-plus"></i> New Event
                    </button>
                    <a href="{{ route('manager.business.agenda.index', $business) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-list"></i> List View
                    </a>
                </div>
            </div>

            {{-- Calendar Container --}}
            <div class="tg-cal-container">
                <div id="calendar" class="tg-cal-body"></div>

                {{-- Empty State Overlay (shown/hidden by JS) --}}
                <div class="tg-cal-empty" id="calEmptyState" style="display:none;">
                    <i class="fa fa-calendar-times-o tg-cal-empty__icon"></i>
                    <h4 class="tg-cal-empty__title">No events scheduled</h4>
                    <p class="tg-cal-empty__text">This time period has no appointments. Click on a time slot or use the button above to create one.</p>
                </div>
            </div>

            {{-- iCal Sharing Bar --}}
            <div class="tg-cal-sharing mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="fa fa-rss"></i> Calendar feed available</span>
                    <a href="{{ route('manager.business.agenda.sharing', [$business]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-cog"></i> Manage Sharing
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Event Create Modal --}}
<div class="modal fade" id="eventCreateModal" tabindex="-1" aria-labelledby="eventCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="eventCreateForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="eventCreateModalLabel"><i class="fa fa-plus-circle"></i> Create Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="eventService" class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
                        <select class="form-select" id="eventService" name="service_id" required>
                            <option value="">Select a service...</option>
                            @foreach($services as $service)
                            <option value="{{ $service->id }}" data-color="{{ $service->color ?? '#6366f1' }}" data-duration="{{ $service->duration ?? 30 }}">
                                {{ $service->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a service.</div>
                    </div>
                    <div class="mb-3">
                        <label for="eventContact" class="form-label fw-semibold">Contact <span class="text-danger">*</span></label>
                        <select class="form-select" id="eventContact" name="contact_id" required>
                            <option value="">Select a contact...</option>
                            @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->firstname }} {{ $contact->lastname }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a contact.</div>
                    </div>
                    <div class="mb-3">
                        <label for="eventAssignee" class="form-label fw-semibold">Assigned Staff</label>
                        <select class="form-select" id="eventAssignee" name="humanresource_id">
                            <option value="">Unassigned</option>
                            @foreach($humanresources as $hr)
                            <option value="{{ $hr->id }}">{{ $hr->name }} (capacity: {{ $hr->capacity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="eventDate" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="eventDate" name="date" required>
                                <div class="invalid-feedback">Date is required.</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="eventTime" class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="eventTime" name="time" required>
                                <div class="invalid-feedback">Time is required.</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="eventComments" class="form-label fw-semibold">Comments</label>
                        <textarea class="form-control" id="eventComments" name="comments" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Event Detail/Edit Modal --}}
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailModalLabel"><i class="fa fa-calendar-check-o"></i> Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="tg-event-detail">
                    <div class="tg-event-detail__color-bar" id="eventDetailColor"></div>
                    <div class="tg-event-detail__field">
                        <span class="tg-event-detail__label"><i class="fa fa-tag"></i> Service</span>
                        <span class="tg-event-detail__value" id="eventDetailService">—</span>
                    </div>
                    <div class="tg-event-detail__field">
                        <span class="tg-event-detail__label"><i class="fa fa-user"></i> Contact</span>
                        <span class="tg-event-detail__value" id="eventDetailContact">—</span>
                    </div>
                    <div class="tg-event-detail__field">
                        <span class="tg-event-detail__label"><i class="fa fa-id-badge"></i> Assigned Staff</span>
                        <span class="tg-event-detail__value" id="eventDetailAssignee">—</span>
                    </div>
                    <div class="tg-event-detail__field">
                        <span class="tg-event-detail__label"><i class="fa fa-clock-o"></i> Time</span>
                        <span class="tg-event-detail__value" id="eventDetailTime">—</span>
                    </div>
                    <div class="tg-event-detail__field">
                        <span class="tg-event-detail__label"><i class="fa fa-info-circle"></i> Status</span>
                        <span class="tg-event-detail__value" id="eventDetailStatus">—</span>
                    </div>
                    <div class="tg-event-detail__field" id="eventDetailCommentsRow" style="display:none;">
                        <span class="tg-event-detail__label"><i class="fa fa-comment"></i> Comments</span>
                        <span class="tg-event-detail__value" id="eventDetailComments">—</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="eventDetailViewBtn"><i class="fa fa-external-link"></i> View Full Details</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/calendar.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    var rescheduleUrl = '{{ route("manager.business.agenda.reschedule", [$business, "__ID__"]) }}';
    var csrfToken = '{{ csrf_token() }}';

    var calendar = tgInitCalendar('#calendar', {
        defaultDate: new Date(),
        locale: timegrid.lang,
        defaultView: 'timeGridWeek',
        headerToolbar: false,
        minTime: timegrid.minTime,
        maxTime: timegrid.maxTime,
        slotDuration: timegrid.slotDuration,
        businessHours: {
            startTime: timegrid.minTime,
            endTime: timegrid.maxTime,
            daysOfWeek: [1, 2, 3, 4, 5, 6]
        },
        events: timegrid.events,
        editable: true,
        droppable: true,
        selectable: true,

        dateClick: function(info) {
            document.getElementById('eventDate').value = info.dateStr.substring(0, 10);
            if (info.dateStr.length > 10) {
                document.getElementById('eventTime').value = info.dateStr.substring(11, 16);
            }
            var modal = new bootstrap.Modal(document.getElementById('eventCreateModal'));
            modal.show();
        },

        eventClick: function(info) {
            var props = info.event.extendedProps;
            document.getElementById('eventDetailService').textContent = props.service || '—';
            document.getElementById('eventDetailContact').textContent = props.contact || '—';
            document.getElementById('eventDetailAssignee').textContent = props.assignee || 'Unassigned';
            document.getElementById('eventDetailStatus').textContent = props.status || '—';
            document.getElementById('eventDetailColor').style.backgroundColor = info.event.backgroundColor || '#6366f1';
            document.getElementById('eventDetailViewBtn').href = props.detailUrl || '#';

            var start = info.event.start;
            var end = info.event.end;
            var timeStr = '';
            if (start) {
                timeStr = start.toLocaleDateString() + ' ' + start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                if (end) timeStr += ' — ' + end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }
            document.getElementById('eventDetailTime').textContent = timeStr;

            var commentsRow = document.getElementById('eventDetailCommentsRow');
            if (props.comments) {
                commentsRow.style.display = '';
                document.getElementById('eventDetailComments').textContent = props.comments;
            } else {
                commentsRow.style.display = 'none';
            }

            var modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
            modal.show();
        },

        eventDrop: function(info) {
            var appointmentId = info.event.extendedProps.appointmentId;
            var url = rescheduleUrl.replace('__ID__', appointmentId);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    start: info.event.start.toISOString(),
                    end: (info.event.end || info.event.start).toISOString(),
                })
            }).then(function(resp) {
                if (!resp.ok) {
                    info.revert();
                    alert('Failed to reschedule. Please try again.');
                }
            }).catch(function() {
                info.revert();
                alert('Network error. Event reverted.');
            });
        },

        eventResize: function(info) {
            var appointmentId = info.event.extendedProps.appointmentId;
            var url = rescheduleUrl.replace('__ID__', appointmentId);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    start: info.event.start.toISOString(),
                    end: (info.event.end || info.event.start).toISOString(),
                })
            }).then(function(resp) {
                if (!resp.ok) {
                    info.revert();
                    alert('Failed to update. Please try again.');
                }
            }).catch(function() {
                info.revert();
            });
        },

        eventsSet: function(events) {
            var emptyEl = document.getElementById('calEmptyState');
            if (emptyEl) {
                emptyEl.style.display = events.length === 0 ? '' : 'none';
            }
        }
    });

    // View toggle buttons
    var toggleBtns = document.querySelectorAll('#calViewToggle .tg-cal-view-toggle__btn');
    toggleBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(function(b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            if (calendar) {
                calendar.changeView(btn.getAttribute('data-view'));
            }
        });
    });

    // Create form validation
    var createForm = document.getElementById('eventCreateForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!createForm.checkValidity()) {
                createForm.classList.add('was-validated');
                return;
            }
            createForm.classList.add('was-validated');

            var serviceSelect = document.getElementById('eventService');
            var contactSelect = document.getElementById('eventContact');
            var selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            var duration = parseInt(selectedOption.getAttribute('data-duration')) || 30;
            var color = selectedOption.getAttribute('data-color') || '#6366f1';

            var date = document.getElementById('eventDate').value;
            var time = document.getElementById('eventTime').value;
            var startDt = new Date(date + 'T' + time);
            var endDt = new Date(startDt.getTime() + duration * 60000);

            if (calendar) {
                calendar.addEvent({
                    title: contactSelect.options[contactSelect.selectedIndex].text + ' / ' + serviceSelect.options[serviceSelect.selectedIndex].text,
                    start: startDt.toISOString(),
                    end: endDt.toISOString(),
                    color: color,
                    extendedProps: {
                        service: serviceSelect.options[serviceSelect.selectedIndex].text,
                        contact: contactSelect.options[contactSelect.selectedIndex].text,
                        assignee: document.getElementById('eventAssignee').options[document.getElementById('eventAssignee').selectedIndex].text || 'Unassigned',
                        status: 'Reserved',
                        comments: document.getElementById('eventComments').value,
                    }
                });
            }

            var modal = bootstrap.Modal.getInstance(document.getElementById('eventCreateModal'));
            if (modal) modal.hide();
            createForm.reset();
            createForm.classList.remove('was-validated');
        });
    }
});
</script>
@endpush
