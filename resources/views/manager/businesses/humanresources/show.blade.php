@extends('layouts.app')

@section('title', $humanresource->name)

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Detail Header --}}
            <div class="tg-detail-header">
                <div class="tg-detail-header__avatar">
                    <div class="tg-detail-header__initials">{{ strtoupper(substr($humanresource->name, 0, 2)) }}</div>
                </div>
                <div class="tg-detail-header__info">
                    <h1 class="tg-detail-header__title">{{ $humanresource->name }}</h1>
                    <div class="tg-detail-header__meta">
                        <span class="tg-detail-header__meta-item">
                            <i class="fa fa-tag"></i> {{ $humanresource->slug }}
                        </span>
                        @if($humanresource->capacity)
                        <span class="tg-detail-header__meta-item">
                            <i class="fa fa-users"></i> Capacity: {{ $humanresource->capacity }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="tg-detail-header__actions">
                    <a href="{{ route('manager.business.humanresource.edit', [$humanresource->business, $humanresource->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-pencil"></i> {{ trans('manager.humanresource.btn.edit', ['default' => 'Edit']) }}
                    </a>
                    <form method="POST" action="{{ route('manager.business.humanresource.destroy', [$humanresource->business, $humanresource]) }}" class="d-inline"
                          onsubmit="return confirm('{{ trans('manager.humanresource.btn.delete', ['default' => 'Delete this staff member']) }}?')">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fa fa-trash"></i> {{ trans('manager.humanresource.btn.delete', ['default' => 'Delete']) }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="row mt-3">
                {{-- Main Content --}}
                <div class="col-lg-8">
                    <div class="tg-detail-tabs">
                        <ul class="tg-detail-tabs__nav" role="tablist">
                            <li><button class="tg-detail-tabs__btn is-active" data-tab="details" role="tab" aria-selected="true">
                                <i class="fa fa-info-circle"></i> Details
                            </button></li>
                        </ul>

                        <div class="tg-detail-tabs__panel is-active" data-tab-panel="details" role="tabpanel">
                            <div class="tg-detail-fields">
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">Name</span>
                                    <span class="tg-detail-field__value">{{ $humanresource->name }}</span>
                                </div>
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">Slug</span>
                                    <span class="tg-detail-field__value"><code>{{ $humanresource->slug }}</code></span>
                                </div>
                                @if($humanresource->capacity)
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">Capacity</span>
                                    <span class="tg-detail-field__value">{{ $humanresource->capacity }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Related Items Sidebar --}}
                <div class="col-lg-4">
                    <div class="tg-detail-sidebar">
                        <div class="tg-detail-sidebar__section">
                            <h3 class="tg-detail-sidebar__title"><i class="fa fa-building-o"></i> Business</h3>
                            <div class="tg-detail-sidebar__item">
                                <a href="{{ route('manager.business.show', $humanresource->business) }}">{{ $humanresource->business->name }}</a>
                            </div>
                        </div>
                        <div class="tg-detail-sidebar__section">
                            <h3 class="tg-detail-sidebar__title"><i class="fa fa-list"></i> Quick Actions</h3>
                            <a href="{{ route('manager.business.humanresource.edit', [$humanresource->business, $humanresource->id]) }}"
                               class="btn btn-outline-secondary btn-sm w-100 mb-2">
                                <i class="fa fa-pencil"></i> Edit Staff Member
                            </a>
                            <a href="{{ route('manager.business.humanresource.index', $humanresource->business) }}"
                               class="btn btn-outline-primary btn-sm w-100">
                                <i class="fa fa-arrow-left"></i> Back to Staff
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
