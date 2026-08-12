@extends('layouts.app')

@section('title', trans('manager.agenda.title'))
@section('subtitle', $appointment->code)

@section('content')
<div class="container-fluid px-0">

    {{-- Back Navigation --}}
    <div class="mb-3">
        <a href="{{ route('manager.business.agenda.index', $business) }}" class="text-decoration-none text-muted">
            <i class="fa fa-arrow-left"></i> Back to Agenda
        </a>
    </div>

    {{-- Detail Header --}}
    <div class="tg-detail-header mb-4">
        <div class="tg-detail-header__left">
            <h1 class="tg-detail-header__title">
                <code class="tg-code tg-code--lg">{{ $appointment->code }}</code>
            </h1>
            <div class="tg-detail-header__meta">
                <span class="badge bg-{{ $appointment->statusToCssClass() }} tg-detail-header__badge">
                    {{ $appointment->status() }}
                </span>
                @if($appointment->service)
                <span class="tg-detail-header__tag">
                    <i class="fa fa-tag"></i> {{ $appointment->service->name }}
                </span>
                @endif
                @if($appointment->start_at)
                <span class="tg-detail-header__tag">
                    <i class="fa fa-calendar"></i> {{ $appointment->start_at->timezone($business->timezone)->format('l, M d, Y') }}
                </span>
                @endif
            </div>
        </div>
        <div class="tg-detail-header__actions">
            @if($appointment->isConfirmableBy(auth()->id()))
            <button type="button" class="btn btn-success action"
                data-action="confirm" data-business="{{ $business->id }}"
                data-appointment="{{ $appointment->id }}" data-code="{{ $appointment->code }}">
                <i class="fa fa-check"></i> Confirm
            </button>
            @endif
            @if($appointment->isServeableBy(auth()->id()))
            <button type="button" class="btn btn-primary action"
                data-action="serve" data-business="{{ $business->id }}"
                data-appointment="{{ $appointment->id }}" data-code="{{ $appointment->code }}">
                <i class="fa fa-check-circle"></i> Mark Served
            </button>
            @endif
            @if($appointment->isCancelableBy(auth()->id()))
            <button type="button" class="btn btn-outline-danger action"
                data-action="cancel" data-business="{{ $business->id }}"
                data-appointment="{{ $appointment->id }}" data-code="{{ $appointment->code }}">
                <i class="fa fa-times"></i> Cancel
            </button>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- Appointment Details --}}
            <div class="tg-detail-section mb-4">
                <div class="tg-detail-section__header">
                    <h2 class="tg-detail-section__title"><i class="fa fa-info-circle"></i> Appointment Details</h2>
                </div>
                <div class="tg-detail-section__body">
                    <div class="tg-detail-fields">
                        <div class="tg-detail-field">
                            <span class="tg-detail-field__label">Status</span>
                            <span class="tg-detail-field__value">
                                <span class="badge bg-{{ $appointment->statusToCssClass() }}">{{ $appointment->status() }}</span>
                            </span>
                        </div>
                        <div class="tg-detail-field">
                            <span class="tg-detail-field__label">Service</span>
                            <span class="tg-detail-field__value">{{ $appointment->service ? $appointment->service->name : '—' }}</span>
                        </div>
                        <div class="tg-detail-field">
                            <span class="tg-detail-field__label">Date</span>
                            <span class="tg-detail-field__value">
                                @if($appointment->start_at)
                                    {{ $appointment->start_at->timezone($business->timezone)->format('l, M d, Y') }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="tg-detail-field">
                            <span class="tg-detail-field__label">Time</span>
                            <span class="tg-detail-field__value">
                                @if($appointment->start_at && $appointment->finish_at)
                                    {{ $appointment->start_at->timezone($business->timezone)->format('g:i A') }}
                                    – {{ $appointment->finish_at->timezone($business->timezone)->format('g:i A') }}
                                    <span class="text-muted">({{ trans_duration($appointment->duration()) }})</span>
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="tg-detail-field">
                            <span class="tg-detail-field__label">Timezone</span>
                            <span class="tg-detail-field__value">{{ $business->timezone }}</span>
                        </div>
                        @if($appointment->humanresource)
                        <div class="tg-detail-field">
                            <span class="tg-detail-field__label">Assigned Staff</span>
                            <span class="tg-detail-field__value">{{ $appointment->humanresource->name }}</span>
                        </div>
                        @endif
                        @if($appointment->comments)
                        <div class="tg-detail-field tg-detail-field--full">
                            <span class="tg-detail-field__label">Comments</span>
                            <span class="tg-detail-field__value">{{ $appointment->comments }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Task Breakdown --}}
            <div class="tg-detail-section mb-4">
                <div class="tg-detail-section__header">
                    <h2 class="tg-detail-section__title"><i class="fa fa-tasks"></i> Task Breakdown</h2>
                </div>
                <div class="tg-detail-section__body">
                    <div class="tg-task-breakdown">
                        <div class="tg-task-item {{ $appointment->status !== 'R' ? 'tg-task-item--done' : 'tg-task-item--current' }}">
                            <div class="tg-task-item__check">
                                <i class="fa {{ $appointment->status !== 'R' ? 'fa-check-circle text-success' : 'fa-circle-o text-warning' }}"></i>
                            </div>
                            <div class="tg-task-item__content">
                                <span class="tg-task-item__title">Booking Reserved</span>
                                <span class="tg-task-item__meta">Customer booked the appointment</span>
                            </div>
                        </div>
                        <div class="tg-task-item {{ $appointment->status === 'C' || $appointment->status === 'S' ? 'tg-task-item--done' : ($appointment->status === 'R' ? '' : '') }}">
                            <div class="tg-task-item__check">
                                <i class="fa {{ $appointment->status === 'C' || $appointment->status === 'S' ? 'fa-check-circle text-success' : 'fa-circle-o text-muted' }}"></i>
                            </div>
                            <div class="tg-task-item__content">
                                <span class="tg-task-item__title">Appointment Confirmed</span>
                                <span class="tg-task-item__meta">Business confirmed the booking</span>
                            </div>
                        </div>
                        <div class="tg-task-item {{ $appointment->status === 'S' ? 'tg-task-item--done' : '' }}">
                            <div class="tg-task-item__check">
                                <i class="fa {{ $appointment->status === 'S' ? 'fa-check-circle text-success' : 'fa-circle-o text-muted' }}"></i>
                            </div>
                            <div class="tg-task-item__content">
                                <span class="tg-task-item__title">Service Completed</span>
                                <span class="tg-task-item__meta">Appointment marked as served</span>
                            </div>
                        </div>
                        @if($appointment->status === 'A')
                        <div class="tg-task-item tg-task-item--canceled">
                            <div class="tg-task-item__check">
                                <i class="fa fa-times-circle text-danger"></i>
                            </div>
                            <div class="tg-task-item__content">
                                <span class="tg-task-item__title">Appointment Canceled</span>
                                <span class="tg-task-item__meta">This appointment was canceled</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- History Log --}}
            <div class="tg-detail-section">
                <div class="tg-detail-section__header">
                    <h2 class="tg-detail-section__title"><i class="fa fa-history"></i> History</h2>
                </div>
                <div class="tg-detail-section__body">
                    <div class="tg-history-log">
                        <div class="tg-history-entry">
                            <div class="tg-history-entry__dot tg-history-entry__dot--info"></div>
                            <div class="tg-history-entry__content">
                                <span class="tg-history-entry__title">Appointment Created</span>
                                <span class="tg-history-entry__meta">
                                    @if($appointment->created_at)
                                        {{ $appointment->created_at->timezone($business->timezone)->format('M d, Y \a\t g:i A') }}
                                        · {{ $appointment->created_at->diffForHumans() }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @if($appointment->status === 'C' || $appointment->status === 'S')
                        <div class="tg-history-entry">
                            <div class="tg-history-entry__dot tg-history-entry__dot--success"></div>
                            <div class="tg-history-entry__content">
                                <span class="tg-history-entry__title">Appointment Confirmed</span>
                                <span class="tg-history-entry__meta">
                                    @if($appointment->updated_at)
                                        {{ $appointment->updated_at->timezone($business->timezone)->format('M d, Y \a\t g:i A') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($appointment->status === 'S')
                        <div class="tg-history-entry">
                            <div class="tg-history-entry__dot tg-history-entry__dot--primary"></div>
                            <div class="tg-history-entry__content">
                                <span class="tg-history-entry__title">Service Completed</span>
                                <span class="tg-history-entry__meta">
                                    @if($appointment->updated_at)
                                        {{ $appointment->updated_at->timezone($business->timezone)->format('M d, Y \a\t g:i A') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($appointment->status === 'A')
                        <div class="tg-history-entry">
                            <div class="tg-history-entry__dot tg-history-entry__dot--danger"></div>
                            <div class="tg-history-entry__content">
                                <span class="tg-history-entry__title">Appointment Canceled</span>
                                <span class="tg-history-entry__meta">
                                    @if($appointment->updated_at)
                                        {{ $appointment->updated_at->timezone($business->timezone)->format('M d, Y \a\t g:i A') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Contact Card --}}
            <div class="tg-detail-section mb-4">
                <div class="tg-detail-section__header">
                    <h2 class="tg-detail-section__title"><i class="fa fa-user"></i> Contact</h2>
                </div>
                <div class="tg-detail-section__body">
                    @if($appointment->contact)
                    <div class="tg-contact-card">
                        <div class="tg-contact-card__avatar">
                            <span class="tg-team-member__initials">{{ strtoupper(substr($appointment->contact->firstname ?? '', 0, 1) . substr($appointment->contact->lastname ?? '', 0, 1)) }}</span>
                        </div>
                        <div class="tg-contact-card__info">
                            <span class="tg-contact-card__name">{{ $appointment->contact->fullname }}</span>
                            @if($appointment->contact->email)
                            <span class="tg-contact-card__email"><i class="fa fa-envelope-o"></i> {{ $appointment->contact->email }}</span>
                            @endif
                            @if($appointment->contact->mobile)
                            <span class="tg-contact-card__phone"><i class="fa fa-phone"></i> {{ $appointment->contact->mobile }}</span>
                            @endif
                        </div>
                        <a href="{{ route('manager.addressbook.show', [$business, $appointment->contact->id]) }}"
                           class="btn btn-sm btn-outline-primary mt-2 w-100">
                            <i class="fa fa-address-book-o"></i> View Full Profile
                        </a>
                    </div>
                    @else
                    <div class="tg-empty-state tg-empty-state--compact">
                        <p class="tg-empty-state__text">No contact information available.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Service Info --}}
            @if($appointment->service)
            <div class="tg-detail-section mb-4">
                <div class="tg-detail-section__header">
                    <h2 class="tg-detail-section__title"><i class="fa fa-tag"></i> Service</h2>
                </div>
                <div class="tg-detail-section__body">
                    <div class="tg-detail-fields">
                        <div class="tg-detail-field">
                            <span class="tg-detail-field__label">Name</span>
                            <span class="tg-detail-field__value">{{ $appointment->service->name }}</span>
                        </div>
                        @if($appointment->service->duration)
                        <div class="tg-detail-field">
                            <span class="tg-detail-field__label">Duration</span>
                            <span class="tg-detail-field__value">{{ $appointment->service->duration }} min</span>
                        </div>
                        @endif
                        @if($appointment->service->color)
                        <div class="tg-detail-field">
                            <span class="tg-detail-field__label">Color</span>
                            <span class="tg-detail-field__value">
                                <span class="d-inline-block rounded-circle me-1" style="width:12px;height:12px;background:{{ $appointment->service->color }}"></span>
                                {{ $appointment->service->color }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Quick Actions --}}
            <div class="tg-detail-section">
                <div class="tg-detail-section__header">
                    <h2 class="tg-detail-section__title"><i class="fa fa-bolt"></i> Quick Actions</h2>
                </div>
                <div class="tg-detail-section__body">
                    <div class="tg-quick-actions">
                        <a href="{{ route('manager.business.agenda.index', $business) }}" class="tg-quick-action">
                            <i class="fa fa-list"></i>
                            <span>All Appointments</span>
                        </a>
                        <a href="{{ route('manager.business.agenda.calendar', $business) }}" class="tg-quick-action">
                            <i class="fa fa-calendar"></i>
                            <span>Calendar View</span>
                        </a>
                        @if($appointment->contact)
                        <a href="{{ route('manager.addressbook.show', [$business, $appointment->contact->id]) }}" class="tg-quick-action">
                            <i class="fa fa-address-book-o"></i>
                            <span>Contact Profile</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/ajax.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    var token = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = token ? token.getAttribute('content') : '';

    document.querySelectorAll('.action').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            var action = this.dataset.action;
            var msg = action === 'cancel'
                ? 'Are you sure you want to cancel this appointment?'
                : 'Proceed with this action?';
            if (confirm(msg)) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("api.booking.action") }}';
                var fields = {
                    _token: csrfToken,
                    business: this.dataset.business,
                    appointment: this.dataset.appointment,
                    action: action,
                    widget: 'row'
                };
                for (var key in fields) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
