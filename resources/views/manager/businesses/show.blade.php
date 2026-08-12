@extends('layouts.app')

@section('title', trans('manager.businesses.dashboard.title'))
@section('subtitle', $business->name)

@section('content')
<div class="container-fluid px-0">

    {{-- Setup Alerts --}}
    @if ($business->services()->count() == 0)
    <div class="tg-auth-alert tg-auth-alert--warning mb-3" role="alert">
        <i class="fa fa-exclamation-triangle"></i>
        <div>
            <a href="{{ route('manager.business.service.create', $business) }}" class="fw-semibold text-decoration-none">
                <i class="fa fa-tag"></i> {{ trans('manager.businesses.dashboard.alert.no_services_set') }}
            </a>
        </div>
    </div>
    @endif

    @if ($business->vacancies()->future()->count() == 0)
    <div class="tg-auth-alert tg-auth-alert--warning mb-3" role="alert">
        <i class="fa fa-exclamation-triangle"></i>
        <div>
            <a href="{{ route('manager.business.vacancy.create', $business) }}" class="fw-semibold text-decoration-none">
                <i class="fa fa-clock-o"></i> {{ trans('manager.businesses.dashboard.alert.no_vacancies_set') }}
            </a>
        </div>
    </div>
    @endif

    {{-- KPI Stat Cards --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3 mb-4">
        @foreach ($boxes as $box)
        <div class="col">
            <a href="{{ $box['link'] }}" class="tg-stat-card tg-stat-card--link" title="{{ trans($box['title']) }}">
                <div class="tg-stat-card__icon bg-{{ $box['color'] }}">
                    <i class="fa fa-{{ $box['icon'] }}"></i>
                </div>
                <div class="tg-stat-card__body">
                    <span class="tg-stat-card__value">{{ $box['number'] }}</span>
                    <span class="tg-stat-card__label">{{ trans($box['title']) }}</span>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <div class="row g-4">
        {{-- Schedule View with Daily/Weekly Toggle --}}
        <div class="col-lg-8">
            <div class="tg-dash-panel">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-calendar-check-o"></i> Schedule
                    </h2>
                    <div class="tg-schedule-toggle" role="tablist">
                        <button class="tg-schedule-toggle__btn is-active" data-schedule-view="daily" role="tab" aria-selected="true">
                            <i class="fa fa-sun-o"></i> Today
                        </button>
                        <button class="tg-schedule-toggle__btn" data-schedule-view="weekly" role="tab" aria-selected="false">
                            <i class="fa fa-calendar"></i> This Week
                        </button>
                    </div>
                </div>
                <div class="tg-dash-panel__body p-0">
                    {{-- Daily Schedule --}}
                    <div class="tg-schedule-panel" id="schedule-daily">
                        @if($todayAppointments->isNotEmpty())
                        <div class="tg-schedule-timeline">
                            @foreach($todayAppointments as $appointment)
                            <div class="tg-schedule-slot">
                                <div class="tg-schedule-slot__time">
                                    @if($appointment->start_at)
                                        {{ $appointment->start_at->timezone($business->timezone)->format('g:i A') }}
                                    @endif
                                </div>
                                <div class="tg-schedule-slot__indicator
                                    tg-schedule-slot__indicator--{{ $appointment->status === 'C' ? 'confirmed' : ($appointment->status === 'A' ? 'active' : ($appointment->status === 'X' ? 'canceled' : 'default')) }}">
                                </div>
                                <div class="tg-schedule-slot__content">
                                    <div class="tg-schedule-slot__title">
                                        {{ $appointment->contact->firstname ?? '' }} {{ $appointment->contact->lastname ?? '' }}
                                    </div>
                                    <div class="tg-schedule-slot__meta">
                                        @if($appointment->service)
                                        <span class="tg-schedule-slot__service">
                                            <i class="fa fa-tag"></i> {{ $appointment->service->name }}
                                        </span>
                                        @endif
                                        <span class="badge
                                            {{ $appointment->status === 'C' ? 'bg-success' : '' }}
                                            {{ $appointment->status === 'A' ? 'bg-info' : '' }}
                                            {{ $appointment->status === 'X' ? 'bg-danger' : '' }}
                                            {{ $appointment->status === 'R' ? 'bg-warning' : '' }}">
                                            {{ $appointment->status === 'C' ? 'Confirmed' : ($appointment->status === 'A' ? 'Active' : ($appointment->status === 'X' ? 'Canceled' : ($appointment->status === 'R' ? 'Reserved' : $appointment->status))) }}
                                        </span>
                                        @if($appointment->start_at && $appointment->finish_at)
                                        <span class="text-muted">
                                            <i class="fa fa-clock-o"></i>
                                            {{ $appointment->start_at->timezone($business->timezone)->format('g:i A') }}
                                            – {{ $appointment->finish_at->timezone($business->timezone)->format('g:i A') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="tg-empty-state">
                            <i class="fa fa-calendar-o tg-empty-state__icon"></i>
                            <h3 class="tg-empty-state__title">No appointments today</h3>
                            <p class="tg-empty-state__text">Your schedule is clear. Use the quick actions to set availability or manage services.</p>
                        </div>
                        @endif
                    </div>

                    {{-- Weekly Schedule --}}
                    <div class="tg-schedule-panel" id="schedule-weekly" style="display:none;">
                        @if($weekAppointments->isNotEmpty())
                        <div class="tg-schedule-timeline">
                            @php
                                $grouped = $weekAppointments->groupBy(function($a) use ($business) {
                                    return $a->start_at ? $a->start_at->timezone($business->timezone)->format('l, M d') : 'Unscheduled';
                                });
                            @endphp
                            @foreach($grouped as $dayLabel => $dayAppointments)
                            <div class="tg-schedule-day-header">{{ $dayLabel }}</div>
                            @foreach($dayAppointments as $appointment)
                            <div class="tg-schedule-slot">
                                <div class="tg-schedule-slot__time">
                                    @if($appointment->start_at)
                                        {{ $appointment->start_at->timezone($business->timezone)->format('g:i A') }}
                                    @endif
                                </div>
                                <div class="tg-schedule-slot__indicator
                                    tg-schedule-slot__indicator--{{ $appointment->status === 'C' ? 'confirmed' : ($appointment->status === 'A' ? 'active' : ($appointment->status === 'X' ? 'canceled' : 'default')) }}">
                                </div>
                                <div class="tg-schedule-slot__content">
                                    <div class="tg-schedule-slot__title">
                                        {{ $appointment->contact->firstname ?? '' }} {{ $appointment->contact->lastname ?? '' }}
                                    </div>
                                    <div class="tg-schedule-slot__meta">
                                        @if($appointment->service)
                                        <span class="tg-schedule-slot__service">
                                            <i class="fa fa-tag"></i> {{ $appointment->service->name }}
                                        </span>
                                        @endif
                                        <span class="badge
                                            {{ $appointment->status === 'C' ? 'bg-success' : '' }}
                                            {{ $appointment->status === 'A' ? 'bg-info' : '' }}
                                            {{ $appointment->status === 'X' ? 'bg-danger' : '' }}
                                            {{ $appointment->status === 'R' ? 'bg-warning' : '' }}">
                                            {{ $appointment->status === 'C' ? 'Confirmed' : ($appointment->status === 'A' ? 'Active' : ($appointment->status === 'X' ? 'Canceled' : ($appointment->status === 'R' ? 'Reserved' : $appointment->status))) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endforeach
                        </div>
                        @else
                        <div class="tg-empty-state">
                            <i class="fa fa-calendar-o tg-empty-state__icon"></i>
                            <h3 class="tg-empty-state__title">No appointments this week</h3>
                            <p class="tg-empty-state__text">No bookings scheduled for this week.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Task Priority Cards --}}
            <div class="tg-dash-panel mt-4">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-flag"></i> Task Priorities
                    </h2>
                </div>
                <div class="tg-dash-panel__body">
                    @php
                        $reserved  = $todayAppointments->where('status', 'R');
                        $confirmed = $todayAppointments->where('status', 'C');
                        $active    = $todayAppointments->where('status', 'A');
                        $served    = $todayAppointments->where('status', 'S');
                    @endphp
                    @if($todayAppointments->isNotEmpty())
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <div class="col">
                            <div class="tg-priority-card tg-priority-card--urgent">
                                <div class="tg-priority-card__icon"><i class="fa fa-exclamation-circle"></i></div>
                                <div class="tg-priority-card__body">
                                    <span class="tg-priority-card__count">{{ $reserved->count() }}</span>
                                    <span class="tg-priority-card__label">Pending Confirmation</span>
                                </div>
                                <div class="tg-priority-card__badge">Urgent</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="tg-priority-card tg-priority-card--high">
                                <div class="tg-priority-card__icon"><i class="fa fa-check-circle"></i></div>
                                <div class="tg-priority-card__body">
                                    <span class="tg-priority-card__count">{{ $confirmed->count() }}</span>
                                    <span class="tg-priority-card__label">Confirmed Today</span>
                                </div>
                                <div class="tg-priority-card__badge">High</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="tg-priority-card tg-priority-card--medium">
                                <div class="tg-priority-card__icon"><i class="fa fa-play-circle"></i></div>
                                <div class="tg-priority-card__body">
                                    <span class="tg-priority-card__count">{{ $active->count() }}</span>
                                    <span class="tg-priority-card__label">In Progress</span>
                                </div>
                                <div class="tg-priority-card__badge">Medium</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="tg-priority-card tg-priority-card--low">
                                <div class="tg-priority-card__icon"><i class="fa fa-check-square-o"></i></div>
                                <div class="tg-priority-card__body">
                                    <span class="tg-priority-card__count">{{ $served->count() }}</span>
                                    <span class="tg-priority-card__label">Completed</span>
                                </div>
                                <div class="tg-priority-card__badge">Done</div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="tg-empty-state tg-empty-state--compact">
                        <i class="fa fa-flag-o tg-empty-state__icon"></i>
                        <p class="tg-empty-state__text">No tasks to prioritize today.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Team Member Status --}}
            <div class="tg-dash-panel mb-4">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-users"></i> Team Status
                    </h2>
                    <a href="{{ route('manager.business.humanresource.index', $business) }}" class="btn btn-sm btn-outline-secondary">
                        Manage
                    </a>
                </div>
                <div class="tg-dash-panel__body">
                    @if($teamWithLoad->isNotEmpty())
                    <div class="tg-team-status">
                        @foreach($teamWithLoad as $member)
                        <div class="tg-team-member">
                            <div class="tg-team-member__avatar">
                                <span class="tg-team-member__initials">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                                <span class="tg-team-member__dot {{ $member->is_available ? 'tg-team-member__dot--available' : 'tg-team-member__dot--busy' }}"></span>
                            </div>
                            <div class="tg-team-member__info">
                                <span class="tg-team-member__name">{{ $member->name }}</span>
                                <span class="tg-team-member__role">
                                    {{ $member->is_available ? 'Available' : 'Unavailable' }}
                                    · Capacity: {{ $member->capacity }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="tg-empty-state tg-empty-state--compact">
                        <i class="fa fa-user-plus tg-empty-state__icon"></i>
                        <p class="tg-empty-state__text">No team members yet.</p>
                        <a href="{{ route('manager.business.humanresource.create', $business) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> Add Team Member
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions (Spec: assign, schedule, escalate) --}}
            <div class="tg-dash-panel mb-4">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-bolt"></i> Quick Actions
                    </h2>
                </div>
                <div class="tg-dash-panel__body">
                    <div class="tg-quick-actions">
                        <a href="{{ route('manager.business.agenda.index', $business) }}" class="tg-quick-action">
                            <i class="fa fa-calendar-check-o"></i>
                            <span>View Agenda</span>
                        </a>
                        <a href="{{ route('manager.business.vacancy.create', $business) }}" class="tg-quick-action">
                            <i class="fa fa-calendar-plus-o"></i>
                            <span>Schedule Availability</span>
                        </a>
                        <a href="{{ route('manager.addressbook.index', $business) }}" class="tg-quick-action">
                            <i class="fa fa-user-plus"></i>
                            <span>Assign Contact</span>
                        </a>
                        <a href="{{ route('manager.business.agenda.calendar', $business) }}" class="tg-quick-action">
                            <i class="fa fa-calendar"></i>
                            <span>Calendar View</span>
                        </a>
                        <a href="{{ route('manager.business.service.index', $business) }}" class="tg-quick-action">
                            <i class="fa fa-arrow-circle-up"></i>
                            <span>Manage Services</span>
                        </a>
                        <a href="{{ route('manager.business.preferences', $business) }}" class="tg-quick-action">
                            <i class="fa fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Business Info Card --}}
            <div class="tg-dash-panel">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-info-circle"></i> Business Info
                    </h2>
                </div>
                <div class="tg-dash-panel__body">
                    <div class="tg-biz-info">
                        <div class="tg-biz-info__row">
                            <span class="tg-biz-info__label"><i class="fa fa-globe"></i> Timezone</span>
                            <span class="tg-biz-info__value">{{ $business->timezone }}</span>
                        </div>
                        @if($business->postal_address)
                        <div class="tg-biz-info__row">
                            <span class="tg-biz-info__label"><i class="fa fa-map-marker"></i> Address</span>
                            <span class="tg-biz-info__value">{{ $business->postal_address }}</span>
                        </div>
                        @endif
                        @if($business->phone)
                        <div class="tg-biz-info__row">
                            <span class="tg-biz-info__label"><i class="fa fa-phone"></i> Phone</span>
                            <span class="tg-biz-info__value">{{ $business->phone }}</span>
                        </div>
                        @endif
                        <div class="tg-biz-info__row">
                            <span class="tg-biz-info__label"><i class="fa fa-clock-o"></i> Current Time</span>
                            <span class="tg-biz-info__value">{{ $time }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtns = document.querySelectorAll('[data-schedule-view]');
    var dailyPanel = document.getElementById('schedule-daily');
    var weeklyPanel = document.getElementById('schedule-weekly');

    toggleBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(function(b) {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            this.classList.add('is-active');
            this.setAttribute('aria-selected', 'true');

            var view = this.getAttribute('data-schedule-view');
            if (view === 'daily') {
                dailyPanel.style.display = '';
                weeklyPanel.style.display = 'none';
            } else {
                dailyPanel.style.display = 'none';
                weeklyPanel.style.display = '';
            }
        });
    });
});
</script>
@endpush
