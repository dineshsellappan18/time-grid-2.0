@extends('layouts.app')

@section('title', $service->name)

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Detail Header --}}
            <div class="tg-detail-header">
                <div class="tg-detail-header__avatar">
                    <div class="tg-detail-header__initials tg-detail-header__initials--service">
                        <i class="fa fa-briefcase"></i>
                    </div>
                </div>
                <div class="tg-detail-header__info">
                    <h1 class="tg-detail-header__title">{{ $service->name }}</h1>
                    <div class="tg-detail-header__meta">
                        @if($service->type)
                            <span class="badge bg-secondary">{{ $service->type->name }}</span>
                        @endif
                        <span class="tg-detail-header__meta-item">
                            <i class="fa fa-tag"></i> {{ $service->slug }}
                        </span>
                        <span class="tg-detail-header__meta-item">
                            <i class="fa fa-hourglass-half"></i> {{ $service->duration }}&prime;
                        </span>
                    </div>
                </div>
                <div class="tg-detail-header__actions">
                    <a href="{{ route('manager.business.service.edit', [$service->business, $service->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-pencil"></i> {{ trans('manager.service.btn.edit', ['default' => 'Edit']) }}
                    </a>
                    <form method="POST" action="{{ route('manager.business.service.destroy', [$service->business, $service]) }}" class="d-inline"
                          onsubmit="return confirm('{{ trans('manager.service.btn.delete', ['default' => 'Delete this service']) }}?')">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fa fa-trash"></i> {{ trans('manager.service.btn.delete', ['default' => 'Delete']) }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="row mt-3">
                {{-- Main Content with Tabs --}}
                <div class="col-lg-8">
                    <div class="tg-detail-tabs">
                        <ul class="tg-detail-tabs__nav" role="tablist">
                            <li><button class="tg-detail-tabs__btn is-active" data-tab="details" role="tab" aria-selected="true">
                                <i class="fa fa-info-circle"></i> Details
                            </button></li>
                            <li><button class="tg-detail-tabs__btn" data-tab="availability" role="tab" aria-selected="false">
                                <i class="fa fa-clock-o"></i> Availability
                            </button></li>
                        </ul>

                        {{-- Details Tab --}}
                        <div class="tg-detail-tabs__panel is-active" data-tab-panel="details" role="tabpanel">
                            <div class="tg-detail-fields">
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">Service Name</span>
                                    <span class="tg-detail-field__value">{{ $service->name }}</span>
                                </div>
                                @if($service->type)
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">Type</span>
                                    <span class="tg-detail-field__value"><span class="badge bg-secondary">{{ $service->type->name }}</span></span>
                                </div>
                                @endif
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">Slug</span>
                                    <span class="tg-detail-field__value"><code>{{ $service->slug }}</code></span>
                                </div>
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">Duration</span>
                                    <span class="tg-detail-field__value">{{ $service->duration }} minutes</span>
                                </div>
                                @if($service->description)
                                <div class="tg-detail-field tg-detail-field--block">
                                    <span class="tg-detail-field__label">Description</span>
                                    <span class="tg-detail-field__value">{{ $service->description }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Availability Tab --}}
                        <div class="tg-detail-tabs__panel" data-tab-panel="availability" role="tabpanel" style="display:none;">
                            @include('manager.businesses.services._availability', ['service' => $service])
                        </div>
                    </div>
                </div>

                {{-- Related Items Sidebar --}}
                <div class="col-lg-4">
                    <div class="tg-detail-sidebar">
                        <div class="tg-detail-sidebar__section">
                            <h3 class="tg-detail-sidebar__title"><i class="fa fa-building-o"></i> Business</h3>
                            <div class="tg-detail-sidebar__item">
                                <a href="{{ route('manager.business.show', $service->business) }}">{{ $service->business->name }}</a>
                            </div>
                        </div>
                        <div class="tg-detail-sidebar__section">
                            <h3 class="tg-detail-sidebar__title"><i class="fa fa-list"></i> Quick Actions</h3>
                            <a href="{{ route('manager.business.service.edit', [$service->business, $service->id]) }}"
                               class="btn btn-outline-secondary btn-sm w-100 mb-2">
                                <i class="fa fa-pencil"></i> Edit Service
                            </a>
                            <a href="{{ route('manager.business.vacancy.create', $service->business) }}"
                               class="btn btn-outline-primary btn-sm w-100">
                                <i class="fa fa-clock-o"></i> Set Availability
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/detail-view.js'])
@endpush
