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
        {{-- Recent Notifications / Activity Feed --}}
        <div class="col-lg-8">
            <div class="tg-dash-panel">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-bell-o"></i>
                        {{ trans('manager.businesses.dashboard.activity.title', ['default' => 'Recent Activity']) }}
                    </h2>
                    <a href="{{ route('manager.business.agenda.index', $business) }}" class="btn btn-sm btn-outline-primary">
                        {{ trans('manager.businesses.dashboard.activity.view_agenda', ['default' => 'View Agenda']) }}
                    </a>
                </div>
                <div class="tg-dash-panel__body">
                    @if($notifications->isNotEmpty())
                        <div class="tg-activity-feed">
                            @foreach($notifications->take(10) as $notification)
                            <div class="tg-activity-item">
                                <div class="tg-activity-item__indicator tg-activity-item__indicator--active"></div>
                                <div class="tg-activity-item__content">
                                    <div class="tg-activity-item__title">
                                        {{ $notification->text }}
                                    </div>
                                    <div class="tg-activity-item__meta">
                                        @if($notification->created_at)
                                            <span><i class="fa fa-clock-o"></i> {{ $notification->created_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="tg-empty-state">
                            <i class="fa fa-bell-slash-o tg-empty-state__icon"></i>
                            <h3 class="tg-empty-state__title">{{ trans('manager.businesses.dashboard.empty.no_activity_title', ['default' => 'No recent activity']) }}</h3>
                            <p class="tg-empty-state__text">{{ trans('manager.businesses.dashboard.empty.no_activity_text', ['default' => 'New bookings and events will appear here.']) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar: Quick Actions --}}
        <div class="col-lg-4">
            <div class="tg-dash-panel mb-4">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-bolt"></i>
                        {{ trans('manager.businesses.dashboard.quick_actions.title', ['default' => 'Quick Actions']) }}
                    </h2>
                </div>
                <div class="tg-dash-panel__body">
                    <div class="tg-quick-actions">
                        <a href="{{ route('manager.business.agenda.index', $business) }}" class="tg-quick-action">
                            <i class="fa fa-calendar-check-o"></i>
                            <span>{{ trans('manager.businesses.dashboard.quick_actions.agenda', ['default' => 'Today\'s Agenda']) }}</span>
                        </a>
                        <a href="{{ route('manager.business.agenda.calendar', $business) }}" class="tg-quick-action">
                            <i class="fa fa-calendar"></i>
                            <span>{{ trans('manager.businesses.dashboard.quick_actions.calendar', ['default' => 'Calendar View']) }}</span>
                        </a>
                        <a href="{{ route('manager.addressbook.index', $business) }}" class="tg-quick-action">
                            <i class="fa fa-users"></i>
                            <span>{{ trans('manager.businesses.dashboard.quick_actions.addressbook', ['default' => 'Address Book']) }}</span>
                        </a>
                        <a href="{{ route('manager.business.service.index', $business) }}" class="tg-quick-action">
                            <i class="fa fa-tags"></i>
                            <span>{{ trans('manager.businesses.dashboard.quick_actions.services', ['default' => 'Manage Services']) }}</span>
                        </a>
                        <a href="{{ route('manager.business.vacancy.create', $business) }}" class="tg-quick-action">
                            <i class="fa fa-calendar-o"></i>
                            <span>{{ trans('manager.businesses.dashboard.quick_actions.availability', ['default' => 'Set Availability']) }}</span>
                        </a>
                        <a href="{{ route('manager.business.preferences', $business) }}" class="tg-quick-action">
                            <i class="fa fa-cog"></i>
                            <span>{{ trans('manager.businesses.dashboard.quick_actions.settings', ['default' => 'Settings']) }}</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Business Info Card --}}
            <div class="tg-dash-panel">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-info-circle"></i>
                        {{ trans('manager.businesses.dashboard.info.title', ['default' => 'Business Info']) }}
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
