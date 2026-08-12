@extends('layouts.app')

@section('title', trans('manager.agenda.title'))
@section('subtitle', trans('manager.agenda.subtitle'))

@section('content')
{!! Form::open(['id' => 'postAppointmentStatus', 'method' => 'post', 'route' => ['api.booking.action']]) !!}
<div class="container-fluid px-0">
    <div class="tg-table-wrapper">

        {{-- Filter Bar --}}
        <div class="tg-table-toolbar">
            <div class="tg-table-search">
                <i class="fa fa-search tg-table-search__icon"></i>
                <input type="text" class="tg-table-search__input" placeholder="Search appointments...">
            </div>
            <div class="tg-table-filters">
                <select class="tg-table-filter__select" data-filter-column="1">
                    <option value="">All Statuses</option>
                    <option value="reserved">Reserved</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="canceled">Canceled</option>
                    <option value="served">Served</option>
                </select>
                @php
                    $services = $appointments->pluck('service')->filter()->unique('id');
                @endphp
                @if($services->isNotEmpty())
                <select class="tg-table-filter__select" data-filter-column="6">
                    <option value="">All Services</option>
                    @foreach($services as $svc)
                    <option value="{{ strtolower($svc->name) }}">{{ $svc->name }}</option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>

        {{-- Bulk Actions --}}
        <div class="tg-bulk-actions">
            <span class="tg-bulk-actions__count">0 selected</span>
            <button type="button" class="btn btn-sm btn-outline-success tg-bulk-action" data-bulk-action="confirm">
                <i class="fa fa-check"></i> Confirm
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger tg-bulk-action" data-bulk-action="cancel">
                <i class="fa fa-times"></i> Cancel
            </button>
        </div>

        {{-- Data Table --}}
        <table class="tg-table table">
            <thead>
                <tr>
                    <th class="tg-table__th--checkbox"><input type="checkbox" class="tg-table__select-all"></th>
                    <th data-sortable>Code</th>
                    <th data-sortable>Status</th>
                    <th data-sortable>Date</th>
                    <th data-sortable>Start</th>
                    <th data-sortable>End</th>
                    <th data-sortable>Service</th>
                    <th data-sortable>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($appointments as $appointment)
                <tr id="{{ $appointment->code }}" class="tg-table__row--clickable"
                    data-href="{{ route('manager.business.agenda.show', [$business, $appointment]) }}">
                    <td class="tg-table__td--checkbox">
                        <input type="checkbox" class="tg-table__row-select" value="{{ $appointment->id }}">
                    </td>
                    <td data-sort-value="{{ $appointment->code }}">
                        <code class="tg-code">{{ $appointment->code }}</code>
                    </td>
                    <td data-sort-value="{{ $appointment->status }}" data-filter-value="{{ $appointment->status() }}">
                        <span class="badge bg-{{ $appointment->statusToCssClass() }}">{{ $appointment->status() }}</span>
                    </td>
                    <td data-sort-value="{{ $appointment->start_at ? $appointment->start_at->timestamp : 0 }}">
                        {{ $appointment->date('d/M') }}
                    </td>
                    <td title="{{ $appointment->timezone() }} {{ $appointment->start_at->diffForHumans() }}">
                        {{ $appointment->time }}
                    </td>
                    <td title="{{ $appointment->timezone() }}">
                        {{ $appointment->finishTime }}
                    </td>
                    <td data-filter-value="{{ $appointment->service ? strtolower($appointment->service->name) : '' }}">
                        {{ $appointment->service ? $appointment->service->name : '—' }}
                        @if($appointment->comments)
                            <i class="fa fa-pencil text-muted" title="{{ $appointment->comments }}"></i>
                        @endif
                    </td>
                    <td>
                        @if($appointment->contact)
                        <a href="{{ route('manager.addressbook.show', [$business, $appointment->contact->id]) }}"
                           class="text-decoration-none" onclick="event.stopPropagation();">
                            {{ $appointment->contact->fullname }}
                        </a>
                        @endif
                    </td>
                    <td onclick="event.stopPropagation();">
                        <span class="btn-group btn-group-sm">
                            @if($appointment->isConfirmableBy(auth()->id()))
                            <button type="button" class="btn btn-sm btn-outline-success action"
                                data-action="confirm" data-business="{{ $business->id }}"
                                data-appointment="{{ $appointment->id }}" data-code="{{ $appointment->code }}"
                                title="Confirm">
                                <i class="fa fa-check"></i>
                            </button>
                            @endif
                            @if($appointment->isServeableBy(auth()->id()))
                            <button type="button" class="btn btn-sm btn-outline-primary action"
                                data-action="serve" data-business="{{ $business->id }}"
                                data-appointment="{{ $appointment->id }}" data-code="{{ $appointment->code }}"
                                title="Mark Served">
                                <i class="fa fa-check-circle"></i>
                            </button>
                            @endif
                            @if($appointment->isCancelableBy(auth()->id()))
                            <button type="button" class="btn btn-sm btn-outline-danger action"
                                data-action="cancel" data-business="{{ $business->id }}"
                                data-appointment="{{ $appointment->id }}" data-code="{{ $appointment->code }}"
                                title="Cancel">
                                <i class="fa fa-times"></i>
                            </button>
                            @endif
                            <a href="{{ route('manager.business.agenda.show', [$business, $appointment]) }}"
                               class="btn btn-sm btn-outline-secondary" title="View Details">
                                <i class="fa fa-eye"></i>
                            </a>
                        </span>
                    </td>
                </tr>
                @endforeach
                <tr class="tg-table__empty-row" style="display:none;">
                    <td colspan="9">
                        <div class="tg-empty-state tg-empty-state--compact">
                            <i class="fa fa-filter tg-empty-state__icon"></i>
                            <p class="tg-empty-state__text">No appointments match your filters.</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="tg-table-pagination" data-per-page="15"></div>

        {{-- Footer Links --}}
        <div class="d-flex gap-2 mt-3">
            <a href="{{ route('manager.business.agenda.calendar', [$business]) }}" class="btn btn-outline-primary">
                <i class="fa fa-calendar"></i> Calendar View
            </a>
            <a href="{{ route('manager.business.show', [$business]) }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>
{!! Form::close() !!}
@endsection

@push('footer_scripts')
@vite(['resources/js/datatable.js', 'resources/js/ajax.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('postAppointmentStatus');
    var actionUrl = form.getAttribute('action');
    var token = document.querySelector('input[name=_token]').value;

    document.querySelectorAll('.action').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            tgAppointmentAction(this, actionUrl, {
                _token: token,
                business: this.dataset.business,
                appointment: this.dataset.appointment,
                action: this.dataset.action,
                widget: 'row'
            }, '#' + this.dataset.code);
        });
    });

    document.querySelectorAll('.tg-table__row--clickable').forEach(function(row) {
        row.addEventListener('click', function() {
            var href = this.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });
});
</script>
@endpush
