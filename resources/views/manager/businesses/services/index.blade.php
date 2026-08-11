@extends('layouts.app')

@section('title', trans('manager.services.index.title'))
@section('subtitle', trans('manager.services.index.instructions'))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="tg-table-wrapper">

                {{-- Search & Actions Bar --}}
                <div class="tg-table-toolbar">
                    <div class="tg-table-search">
                        <i class="fa fa-search tg-table-search__icon"></i>
                        <input type="text" class="form-control tg-table-search__input"
                               placeholder="{{ trans('manager.services.index.search', ['default' => 'Search services...']) }}"
                               aria-label="Search services">
                    </div>
                    <div class="tg-table-toolbar__actions">
                        <a href="{{ route('manager.business.servicetype.edit', [$business]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-tags"></i> {{ trans('servicetype.btn.edit', ['default' => 'Service Types']) }}
                        </a>
                        <a href="{{ route('manager.business.service.create', [$business]) }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> {{ trans('manager.services.btn.create', ['default' => 'Add Service']) }}
                        </a>
                    </div>
                </div>

                {{-- Bulk Actions Bar --}}
                <div class="tg-bulk-actions" id="bulk-actions">
                    <span class="tg-bulk-actions__count">0 selected</span>
                    <div class="tg-bulk-actions__btns">
                        <button type="button" class="btn btn-outline-danger btn-sm" disabled>
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                </div>

                {{-- Data Table --}}
                <table class="tg-table table">
                    <thead>
                        <tr>
                            <th class="tg-table__th--check">
                                <input type="checkbox" class="form-check-input tg-table__select-all" aria-label="Select all">
                            </th>
                            <th data-sortable>{{ trans('manager.services.index.th.name', ['default' => 'Service']) }}</th>
                            <th data-sortable>{{ trans('manager.services.index.th.type', ['default' => 'Type']) }}</th>
                            <th data-sortable>{{ trans('manager.services.index.th.duration', ['default' => 'Duration']) }}</th>
                            <th class="tg-table__th--actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($business->services as $service)
                        <tr data-label-name="{{ $service->name }}"
                            data-label-type="{{ $service->type ? $service->type->name : '' }}">
                            <td class="tg-table__td--check">
                                <input type="checkbox" class="form-check-input tg-table__row-select" value="{{ $service->id }}" aria-label="Select {{ $service->name }}">
                            </td>
                            <td data-title="{{ trans('manager.services.index.th.name', ['default' => 'Service']) }}">
                                <a href="{{ route('manager.business.service.show', [$business, $service->id]) }}" class="tg-table__link">{{ $service->name }}</a>
                            </td>
                            <td data-title="{{ trans('manager.services.index.th.type', ['default' => 'Type']) }}">
                                @if($service->type)
                                    <span class="badge bg-secondary">{{ $service->type->name }}</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td data-title="{{ trans('manager.services.index.th.duration', ['default' => 'Duration']) }}" data-sort-value="{{ $service->duration ?? 0 }}">
                                {{ $service->duration ?? '—' }} min
                            </td>
                            <td class="tg-table__td--actions">
                                <div class="tg-table__action-group">
                                    <a href="{{ route('manager.business.service.edit', [$business, $service->id]) }}" class="btn btn-sm btn-outline-secondary" title="{{ trans('manager.service.btn.edit', ['default' => 'Edit']) }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('manager.business.service.destroy', [$service->business, $service]) }}"
                                          onsubmit="return confirm('{{ trans('manager.service.btn.delete', ['default' => 'Delete']) }}?')">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ trans('manager.service.btn.delete', ['default' => 'Delete']) }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr class="tg-table__empty-row">
                            <td colspan="5">
                                <div class="tg-empty-state">
                                    <i class="fa fa-briefcase tg-empty-state__icon"></i>
                                    <h4 class="tg-empty-state__title">{{ trans('manager.services.index.empty', ['default' => 'No services yet']) }}</h4>
                                    <p class="tg-empty-state__desc">Create your first service to start scheduling appointments.</p>
                                    <a href="{{ route('manager.business.service.create', [$business]) }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus"></i> {{ trans('manager.services.btn.create', ['default' => 'Add Service']) }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="tg-table-pagination" data-per-page="15"></div>
            </div>

            @if ($business->services()->count())
            <div class="tg-table-cta mt-3">
                <div class="tg-auth-alert tg-auth-alert--success">
                    <i class="fa fa-check-circle"></i>
                    {{ trans('manager.services.create.alert.go_to_vacancies', ['default' => 'Services are set up! Now configure your availability.']) }}
                </div>
                <a href="{{ route('manager.business.vacancy.create', $business) }}" class="btn btn-success btn-sm mt-2">
                    <i class="fa fa-clock-o"></i> {{ trans('manager.services.create.btn.go_to_vacancies', ['default' => 'Set Availability']) }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/datatable.js'])
@endpush
