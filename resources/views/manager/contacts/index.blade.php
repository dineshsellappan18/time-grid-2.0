@extends('layouts.app')

@section('title', trans('manager.contacts.title'))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="tg-table-wrapper">

                {{-- Search & Filter Bar --}}
                <div class="tg-table-toolbar">
                    <div class="tg-table-search">
                        <i class="fa fa-search tg-table-search__icon"></i>
                        <input type="text" class="form-control tg-table-search__input"
                               placeholder="{{ trans('manager.contacts.list.btn.filter', ['default' => 'Search contacts...']) }}"
                               aria-label="Search contacts">
                    </div>
                    <div class="tg-table-toolbar__actions">
                        <a href="{{ route('manager.addressbook.create', $business) }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> {{ trans('manager.businesses.contacts.btn.create', ['default' => 'Add Contact']) }}
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

                {{-- Loading Skeleton --}}
                <div class="tg-table-skeleton" style="display:none;">
                    @for ($i = 0; $i < 5; $i++)
                    <div class="tg-table-skeleton__row">
                        <div class="tg-table-skeleton__cell tg-table-skeleton__cell--sm"></div>
                        <div class="tg-table-skeleton__cell"></div>
                        <div class="tg-table-skeleton__cell"></div>
                        <div class="tg-table-skeleton__cell tg-table-skeleton__cell--lg"></div>
                        <div class="tg-table-skeleton__cell tg-table-skeleton__cell--md"></div>
                    </div>
                    @endfor
                </div>

                {{-- Data Table --}}
                <table class="tg-table table">
                    <thead>
                        <tr>
                            <th class="tg-table__th--check">
                                <input type="checkbox" class="form-check-input tg-table__select-all" aria-label="Select all">
                            </th>
                            <th data-sortable>{{ trans('manager.contacts.list.header.lastname', ['default' => 'Last Name']) }}</th>
                            <th data-sortable>{{ trans('manager.contacts.list.header.firstname', ['default' => 'First Name']) }}</th>
                            <th data-sortable>{{ trans('manager.contacts.list.header.email', ['default' => 'Email']) }}</th>
                            <th data-sortable>{{ trans('manager.contacts.list.header.mobile', ['default' => 'Mobile']) }}</th>
                            <th class="tg-table__th--actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                        <tr data-label-name="{{ $contact->firstname }} {{ $contact->lastname }}"
                            data-label-email="{{ $contact->email }}"
                            data-label-mobile="{{ $contact->mobile }}">
                            <td class="tg-table__td--check">
                                <input type="checkbox" class="form-check-input tg-table__row-select" value="{{ $contact->id }}" aria-label="Select {{ $contact->firstname }} {{ $contact->lastname }}">
                            </td>
                            <td data-title="{{ trans('manager.contacts.list.header.lastname', ['default' => 'Last Name']) }}">
                                <a href="{{ route('manager.addressbook.show', [$business, $contact->id]) }}" class="tg-table__link">{{ $contact->lastname }}</a>
                            </td>
                            <td data-title="{{ trans('manager.contacts.list.header.firstname', ['default' => 'First Name']) }}">
                                {{ $contact->firstname }}
                            </td>
                            <td data-title="{{ trans('manager.contacts.list.header.email', ['default' => 'Email']) }}">
                                <a href="mailto:{{ $contact->email }}" class="tg-table__link--subtle">{{ $contact->email }}</a>
                            </td>
                            <td data-title="{{ trans('manager.contacts.list.header.mobile', ['default' => 'Mobile']) }}">
                                {{ $contact->mobile }}
                            </td>
                            <td class="tg-table__td--actions">
                                <a href="{{ route('manager.addressbook.show', [$business, $contact->id]) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr class="tg-table__empty-row">
                            <td colspan="6">
                                <div class="tg-empty-state">
                                    <i class="fa fa-address-book-o tg-empty-state__icon"></i>
                                    <h4 class="tg-empty-state__title">{{ trans('manager.contacts.list.msg.filter_no_results', ['default' => 'No contacts found']) }}</h4>
                                    <p class="tg-empty-state__desc">Add your first contact to start managing your address book.</p>
                                    <a href="{{ route('manager.addressbook.create', $business) }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus"></i> {{ trans('manager.businesses.contacts.btn.create', ['default' => 'Add Contact']) }}
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
