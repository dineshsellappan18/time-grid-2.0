@extends('layouts.user')

@section('title', trans('user.dashboard.title', ['default' => 'Dashboard']))

@section('content')
<div class="container-fluid px-0">

    {{-- KPI Stat Cards --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
        <div class="col">
            <div class="tg-stat-card">
                <div class="tg-stat-card__icon bg-green">
                    <i class="fa fa-calendar-check-o"></i>
                </div>
                <div class="tg-stat-card__body">
                    <span class="tg-stat-card__value">{{ $appointmentsCount }}</span>
                    <span class="tg-stat-card__label">{{ trans('user.dashboard.stats.upcoming_appointments', ['default' => 'Upcoming Appointments']) }}</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="tg-stat-card">
                <div class="tg-stat-card__icon bg-blue">
                    <i class="fa fa-users"></i>
                </div>
                <div class="tg-stat-card__body">
                    <span class="tg-stat-card__value">{{ $subscriptionsCount }}</span>
                    <span class="tg-stat-card__label">{{ trans('user.dashboard.stats.subscriptions', ['default' => 'Subscriptions']) }}</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="tg-stat-card">
                <div class="tg-stat-card__icon bg-aqua">
                    <i class="fa fa-clock-o"></i>
                </div>
                <div class="tg-stat-card__body">
                    <span class="tg-stat-card__value">
                        @if($appointments->isNotEmpty())
                            {{ $appointments->first()->start_at?->diffForHumans() ?? '—' }}
                        @else
                            —
                        @endif
                    </span>
                    <span class="tg-stat-card__label">{{ trans('user.dashboard.stats.next_appointment', ['default' => 'Next Appointment']) }}</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="tg-stat-card">
                <div class="tg-stat-card__icon bg-green">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div class="tg-stat-card__body">
                    <span class="tg-stat-card__value">{{ $appointments->where('status', 'C')->count() }}</span>
                    <span class="tg-stat-card__label">{{ trans('user.dashboard.stats.confirmed', ['default' => 'Confirmed']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Recent Activity / Upcoming Appointments Feed --}}
        <div class="col-lg-8">
            <div class="tg-dash-panel">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-list-ul"></i>
                        {{ trans('user.dashboard.activity.title', ['default' => 'Upcoming Appointments']) }}
                    </h2>
                    @if($appointmentsCount > 0)
                    <a href="{{ route('user.agenda') }}" class="btn btn-sm btn-outline-primary">
                        {{ trans('user.dashboard.activity.view_all', ['default' => 'View All']) }}
                    </a>
                    @endif
                </div>
                <div class="tg-dash-panel__body">
                    @if($appointments->isNotEmpty())
                        <div class="tg-activity-feed">
                            @foreach($appointments->take(8) as $appointment)
                            <div class="tg-activity-item">
                                <div class="tg-activity-item__indicator
                                    {{ $appointment->status === 'C' ? 'tg-activity-item__indicator--confirmed' : '' }}
                                    {{ $appointment->status === 'A' ? 'tg-activity-item__indicator--active' : '' }}
                                    {{ $appointment->status === 'X' ? 'tg-activity-item__indicator--canceled' : '' }}">
                                </div>
                                <div class="tg-activity-item__content">
                                    <div class="tg-activity-item__title">
                                        {{ $appointment->service?->name ?? trans('user.dashboard.activity.appointment', ['default' => 'Appointment']) }}
                                        <span class="tg-activity-item__badge badge
                                            {{ $appointment->status === 'C' ? 'bg-success' : '' }}
                                            {{ $appointment->status === 'A' ? 'bg-info' : '' }}
                                            {{ $appointment->status === 'X' ? 'bg-danger' : '' }}">
                                            {{ $appointment->statusLabel ?? $appointment->status }}
                                        </span>
                                    </div>
                                    <div class="tg-activity-item__meta">
                                        @if($appointment->business)
                                            <span><i class="fa fa-building-o"></i> {{ $appointment->business->name }}</span>
                                        @endif
                                        @if($appointment->start_at)
                                            <span><i class="fa fa-clock-o"></i> {{ $appointment->start_at->format('M d, Y \a\t g:i A') }}</span>
                                        @endif
                                        @if($appointment->code)
                                            <span class="text-muted"><i class="fa fa-hashtag"></i> {{ $appointment->code }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="tg-empty-state">
                            <i class="fa fa-calendar-o tg-empty-state__icon"></i>
                            <h3 class="tg-empty-state__title">{{ trans('user.dashboard.empty.no_appointments_title', ['default' => 'No upcoming appointments']) }}</h3>
                            <p class="tg-empty-state__text">{{ trans('user.dashboard.empty.no_appointments_text', ['default' => 'Browse the business directory to book your first appointment.']) }}</p>
                            <a href="{{ route('user.directory.list') }}" class="btn btn-primary">
                                <i class="fa fa-search"></i> {{ trans('user.dashboard.empty.browse_directory', ['default' => 'Browse Directory']) }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar: Quick Actions & Subscriptions --}}
        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="tg-dash-panel mb-4">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-bolt"></i>
                        {{ trans('user.dashboard.quick_actions.title', ['default' => 'Quick Actions']) }}
                    </h2>
                </div>
                <div class="tg-dash-panel__body">
                    <div class="tg-quick-actions">
                        <a href="{{ route('user.directory.list') }}" class="tg-quick-action">
                            <i class="fa fa-search"></i>
                            <span>{{ trans('user.dashboard.quick_actions.find_business', ['default' => 'Find a Business']) }}</span>
                        </a>
                        <a href="{{ route('user.agenda') }}" class="tg-quick-action">
                            <i class="fa fa-calendar"></i>
                            <span>{{ trans('user.dashboard.quick_actions.my_agenda', ['default' => 'My Agenda']) }}</span>
                        </a>
                        @if($subscriptionsCount > 0)
                        <a href="{{ route('user.subscriptions') }}" class="tg-quick-action">
                            <i class="fa fa-star"></i>
                            <span>{{ trans('user.dashboard.quick_actions.subscriptions', ['default' => 'My Subscriptions']) }}</span>
                        </a>
                        @endif
                        <a href="{{ route('user.preferences') }}" class="tg-quick-action">
                            <i class="fa fa-cog"></i>
                            <span>{{ trans('user.dashboard.quick_actions.preferences', ['default' => 'Preferences']) }}</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Booking Activity Chart Placeholder --}}
            <div class="tg-dash-panel">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-bar-chart"></i>
                        {{ trans('user.dashboard.chart.title', ['default' => 'Booking Activity']) }}
                    </h2>
                </div>
                <div class="tg-dash-panel__body">
                    @if($appointmentsCount > 0)
                    <div class="tg-chart-placeholder">
                        <div class="tg-chart-bars">
                            @php
                                $statusCounts = [
                                    ['label' => 'Confirmed', 'count' => $appointments->where('status', 'C')->count(), 'color' => 'bg-success'],
                                    ['label' => 'Active', 'count' => $appointments->where('status', 'A')->count(), 'color' => 'bg-info'],
                                    ['label' => 'Served', 'count' => $appointments->where('status', 'S')->count(), 'color' => 'bg-primary'],
                                    ['label' => 'Canceled', 'count' => $appointments->where('status', 'X')->count(), 'color' => 'bg-danger'],
                                ];
                                $maxCount = max(array_column($statusCounts, 'count')) ?: 1;
                            @endphp
                            @foreach($statusCounts as $s)
                            <div class="tg-chart-bar-group">
                                <div class="tg-chart-bar {{ $s['color'] }}" style="height: {{ max(($s['count'] / $maxCount) * 100, 4) }}%"></div>
                                <span class="tg-chart-bar__label">{{ $s['label'] }}</span>
                                <span class="tg-chart-bar__value">{{ $s['count'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="tg-empty-state tg-empty-state--compact">
                        <i class="fa fa-bar-chart tg-empty-state__icon"></i>
                        <p class="tg-empty-state__text">{{ trans('user.dashboard.empty.no_chart_data', ['default' => 'No booking data yet.']) }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
