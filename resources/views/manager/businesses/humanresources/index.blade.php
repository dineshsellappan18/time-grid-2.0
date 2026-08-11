@extends('layouts.app')

@section('title', trans('manager.humanresource.index.title'))
@section('subtitle', trans('manager.humanresource.index.subtitle'))

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
                               placeholder="{{ trans('manager.humanresource.index.search', ['default' => 'Search staff...']) }}"
                               aria-label="Search staff">
                    </div>
                    <div class="tg-table-toolbar__actions">
                        <a href="{{ route('manager.business.humanresource.create', [$business]) }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> {{ trans('manager.humanresource.btn.create', ['default' => 'Add Staff']) }}
                        </a>
                    </div>
                </div>

                {{-- Data Table --}}
                <table class="tg-table table">
                    <thead>
                        <tr>
                            <th data-sortable>{{ trans('manager.humanresource.index.th.name', ['default' => 'Name']) }}</th>
                            <th class="tg-table__th--actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($business->humanresources as $humanresource)
                        <tr data-label-name="{{ $humanresource->name }}">
                            <td data-title="{{ trans('manager.humanresource.index.th.name', ['default' => 'Name']) }}">
                                <a href="{{ route('manager.business.humanresource.show', [$business, $humanresource->id]) }}" class="tg-table__link">{{ $humanresource->name }}</a>
                            </td>
                            <td class="tg-table__td--actions">
                                <a href="{{ route('manager.business.humanresource.edit', [$business, $humanresource->id]) }}" class="btn btn-sm btn-outline-secondary" title="{{ trans('manager.humanresource.btn.edit', ['default' => 'Edit']) }}">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr class="tg-table__empty-row">
                            <td colspan="2">
                                <div class="tg-empty-state">
                                    <i class="fa fa-users tg-empty-state__icon"></i>
                                    <h4 class="tg-empty-state__title">{{ trans('manager.humanresource.index.empty', ['default' => 'No staff members yet']) }}</h4>
                                    <p class="tg-empty-state__desc">{{ trans('manager.humanresource.index.instructions', ['default' => 'Add staff members who provide services.']) }}</p>
                                    <a href="{{ route('manager.business.humanresource.create', [$business]) }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus"></i> {{ trans('manager.humanresource.btn.create', ['default' => 'Add Staff']) }}
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
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/datatable.js'])
@endpush
