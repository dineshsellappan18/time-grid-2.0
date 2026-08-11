@extends('layouts.app')

@section('title', $contact->fullname)

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            {{-- Detail Header --}}
            <div class="tg-detail-header">
                <div class="tg-detail-header__avatar">
                    @if($contact->email)
                        <img src="{{ Gravatar::get($contact->email) }}" alt="{{ $contact->fullname }}" class="tg-detail-header__img">
                    @else
                        <div class="tg-detail-header__initials">{{ strtoupper(substr($contact->firstname, 0, 1) . substr($contact->lastname, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="tg-detail-header__info">
                    <h1 class="tg-detail-header__title">{{ $contact->fullname }}</h1>
                    <div class="tg-detail-header__meta">
                        @if($contact->user)
                            <span class="badge bg-success"><i class="fa fa-check-circle"></i> {{ $contact->user->username }}</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="fa fa-times-circle"></i> {{ trans('manager.contacts.label.unlinked', ['default' => 'Unlinked']) }}</span>
                        @endif
                        <span class="tg-detail-header__meta-item">
                            <i class="fa fa-calendar-o"></i> {{ trans('manager.contacts.label.member_since', ['default' => 'Member since']) }} {{ $contact->pivot->created_at->diffForHumans() }}
                        </span>
                        @if($contact->gender)
                        <span class="tg-detail-header__meta-item">
                            <i class="fa fa-user"></i> {{ trans('app.gender.'.$contact->gender) }}
                        </span>
                        @endif
                    </div>
                    <div class="tg-detail-header__progress">
                        <div class="progress" style="height: 6px; max-width: 200px;">
                            <div class="progress-bar {{ $contact->quality == 100 ? 'bg-success' : 'bg-primary' }}"
                                 role="progressbar" style="width: {{ $contact->quality }}%"
                                 aria-valuenow="{{ $contact->quality }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">{{ $contact->quality }}% complete</small>
                    </div>
                </div>
                <div class="tg-detail-header__actions">
                    <a href="{{ route('manager.addressbook.edit', [$business, $contact]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-pencil"></i> {{ trans('manager.contacts.btn.edit', ['default' => 'Edit']) }}
                    </a>
                    <form method="POST" action="{{ route('manager.addressbook.destroy', [$business, $contact]) }}" class="d-inline"
                          onsubmit="return confirm('{{ trans('manager.contacts.btn.confirm_delete', ['default' => 'Are you sure you want to delete this contact?']) }}')">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fa fa-trash"></i> {{ trans('manager.contacts.btn.delete', ['default' => 'Delete']) }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="row mt-3">
                {{-- Main Content with Tabs --}}
                <div class="col-lg-8">
                    <div class="tg-detail-tabs">
                        <ul class="tg-detail-tabs__nav" role="tablist">
                            <li><button class="tg-detail-tabs__btn is-active" data-tab="info" role="tab" aria-selected="true">
                                <i class="fa fa-info-circle"></i> {{ trans('manager.contacts.tab.info', ['default' => 'Information']) }}
                            </button></li>
                            @if($contact->hasAppointment())
                            <li><button class="tg-detail-tabs__btn" data-tab="appointments" role="tab" aria-selected="false">
                                <i class="fa fa-calendar"></i> {{ trans('manager.contacts.tab.appointments', ['default' => 'Appointments']) }}
                            </button></li>
                            @endif
                            @if(auth()->user()->isOwnerOf($business->id))
                            <li><button class="tg-detail-tabs__btn" data-tab="privacy" role="tab" aria-selected="false">
                                <i class="fa fa-shield"></i> {{ trans('manager.contacts.tab.privacy', ['default' => 'Privacy']) }}
                            </button></li>
                            @endif
                        </ul>

                        {{-- Info Tab --}}
                        <div class="tg-detail-tabs__panel is-active" data-tab-panel="info" role="tabpanel">
                            <div class="tg-detail-fields">
                                @if ($contact->email)
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">
                                        {{ trans('manager.contacts.label.email') }}
                                        <span class="badge bg-warning text-dark ms-1" title="Confidential">C</span>
                                    </span>
                                    <span class="tg-detail-field__value">
                                        <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                    </span>
                                </div>
                                @endif
                                @if ($contact->nin)
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">
                                        {{ trans('manager.contacts.label.nin') }}
                                        <span class="badge bg-danger ms-1" title="Restricted PII">R</span>
                                    </span>
                                    <span class="tg-detail-field__value">{{ $contact->nin }}</span>
                                </div>
                                @endif
                                @if ($contact->birthdate)
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">
                                        {{ trans('manager.contacts.label.birthdate') }}
                                        <span class="badge bg-danger ms-1" title="Restricted PII">R</span>
                                    </span>
                                    <span class="tg-detail-field__value">{{ $contact->birthdate->formatLocalized('%d %B %Y') }}</span>
                                </div>
                                @endif
                                @if ($contact->mobile)
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">
                                        {{ trans('manager.contacts.label.mobile') }}
                                        <span class="badge bg-danger ms-1" title="Restricted PII">R</span>
                                    </span>
                                    <span class="tg-detail-field__value">{{ (trim($contact->mobile) != '') ? phone_format($contact->mobile, $contact->mobile_country) : '' }}</span>
                                </div>
                                @endif
                                @if ($contact->postal_address)
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">
                                        {{ trans('manager.contacts.label.postal_address') }}
                                        <span class="badge bg-warning text-dark ms-1" title="Confidential">C</span>
                                    </span>
                                    <span class="tg-detail-field__value">{{ $contact->postal_address }}</span>
                                </div>
                                @endif
                                @if ($contact->pivot->notes)
                                <div class="tg-detail-field">
                                    <span class="tg-detail-field__label">{{ trans('manager.contacts.label.notes') }}</span>
                                    <span class="tg-detail-field__value">{{ $contact->pivot->notes }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Appointments Tab --}}
                        @if($contact->hasAppointment())
                        <div class="tg-detail-tabs__panel" data-tab-panel="appointments" role="tabpanel" style="display:none;">
                            @include('manager.contacts._appointment', ['appointments' => $contact->appointments()->orderBy('start_at')->ofBusiness($business->id)->Active()->get()])
                        </div>
                        @endif

                        {{-- Privacy / GDPR Tab --}}
                        @if(auth()->user()->isOwnerOf($business->id))
                        <div class="tg-detail-tabs__panel" data-tab-panel="privacy" role="tabpanel" style="display:none;">
                            <div class="tg-detail-privacy">
                                <p class="text-muted mb-3"><i class="fa fa-shield"></i> Data Subject Rights (GDPR)</p>
                                <div class="tg-detail-privacy__actions">
                                    <a href="{{ route('manager.addressbook.export', [$business, $contact]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fa fa-download"></i> Export Data
                                    </a>
                                    <a href="{{ route('manager.addressbook.edit', [$business, $contact]) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fa fa-pencil"></i> Rectify
                                    </a>
                                    <form method="POST" action="{{ route('manager.addressbook.erase', [$business, $contact]) }}" class="d-inline"
                                          onsubmit="return confirm('This will permanently erase all restricted personal data for this contact. This action cannot be undone. Continue?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fa fa-eraser"></i> Erase PII
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Related Items Sidebar --}}
                <div class="col-lg-4">
                    <div class="tg-detail-sidebar">
                        <div class="tg-detail-sidebar__section">
                            <h3 class="tg-detail-sidebar__title"><i class="fa fa-calendar"></i> Quick Actions</h3>
                            @if(auth()->user()->isOwnerOf($business->id))
                            <a href="{{ route('user.booking.book', ['business' => $business, 'behalfOfId' => $contact->id]) }}"
                               class="btn btn-success btn-sm w-100 mb-2">
                                <i class="fa fa-plus"></i> {{ trans('user.appointments.btn.book_in_biz_on_behalf_of', ['biz' => $business->name, 'contact' => $contact->fullname()]) }}
                            </a>
                            @endif
                            <a href="{{ route('manager.addressbook.edit', [$business, $contact]) }}"
                               class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fa fa-pencil"></i> {{ trans('manager.contacts.btn.edit', ['default' => 'Edit Contact']) }}
                            </a>
                        </div>

                        <div class="tg-detail-sidebar__section">
                            <h3 class="tg-detail-sidebar__title"><i class="fa fa-building-o"></i> Business</h3>
                            <div class="tg-detail-sidebar__item">
                                <a href="{{ route('manager.business.show', $business) }}">{{ $business->name }}</a>
                            </div>
                        </div>

                        @if($contact->hasAppointment())
                        <div class="tg-detail-sidebar__section">
                            <h3 class="tg-detail-sidebar__title"><i class="fa fa-history"></i> Recent Appointments</h3>
                            @foreach($contact->appointments()->orderBy('start_at', 'desc')->ofBusiness($business->id)->Active()->take(3)->get() as $appt)
                            <div class="tg-detail-sidebar__item">
                                <span class="tg-detail-sidebar__item-label">{{ $appt->code }}</span>
                                <span class="tg-detail-sidebar__item-value text-muted">{{ $appt->start_at->diffForHumans() }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
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
