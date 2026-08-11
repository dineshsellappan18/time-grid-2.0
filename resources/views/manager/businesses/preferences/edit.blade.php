@extends('layouts.app')

@section('title', trans('manager.businesses.preferences.title', ['default' => 'Settings']))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <form method="POST" action="{{ route('manager.business.preferences', $business) }}" id="settings-form" class="tg-settings-form">
                {{ csrf_field() }}

                {{-- Hours & Booking Section --}}
                <div class="tg-settings-section">
                    <div class="tg-settings-section__header">
                        <h2 class="tg-settings-section__title"><i class="fa fa-clock-o"></i> {{ trans('manager.businesses.preferences.sections.hours', ['default' => 'Hours & Booking']) }}</h2>
                        <p class="tg-settings-section__desc">{{ trans('manager.businesses.preferences.sections.hours_desc', ['default' => 'Operating hours, appointment rules, and availability windows.']) }}</p>
                    </div>
                    <div class="tg-settings-section__body">
                        @foreach (['start_at', 'finish_at'] as $key)
                            @if (isset($parameters[$key]))
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\Business.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\Business.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <input type="time" id="pref-{{ $key }}" name="{{ $key }}" class="form-control"
                                           value="{{ $business->pref($key) }}">
                                </div>
                            </div>
                            @endif
                        @endforeach
                        @foreach (['appointment_take_today', 'appointment_flexible_arrival'] as $key)
                            @if (isset($parameters[$key]))
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\Business.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\Business.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input tg-toggle" type="checkbox"
                                               id="pref-{{ $key }}" name="{{ $key }}" value="1"
                                               {{ $business->pref($key) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                        @foreach (['appointment_cancellation_pre_hs', 'appointment_code_length', 'availability_future_days', 'service_default_duration', 'timeslot_step'] as $key)
                            @if (isset($parameters[$key]))
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\Business.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\Business.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <input type="number" id="pref-{{ $key }}" name="{{ $key }}" class="form-control"
                                           value="{{ $business->pref($key) }}"
                                           step="{{ $parameters[$key]['step'] ?? 1 }}">
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Display & Format Section --}}
                <div class="tg-settings-section">
                    <div class="tg-settings-section__header">
                        <h2 class="tg-settings-section__title"><i class="fa fa-eye"></i> {{ trans('manager.businesses.preferences.sections.display', ['default' => 'Display & Format']) }}</h2>
                        <p class="tg-settings-section__desc">{{ trans('manager.businesses.preferences.sections.display_desc', ['default' => 'Visibility of business information and date/time formatting.']) }}</p>
                    </div>
                    <div class="tg-settings-section__body">
                        @foreach (['show_map', 'show_postal_address', 'show_phone'] as $key)
                            @if (isset($parameters[$key]))
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\Business.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\Business.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input tg-toggle" type="checkbox"
                                               id="pref-{{ $key }}" name="{{ $key }}" value="1"
                                               {{ $business->pref($key) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                        @foreach (['time_format', 'date_format'] as $key)
                            @if (isset($parameters[$key]))
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\Business.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\Business.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <input type="text" id="pref-{{ $key }}" name="{{ $key }}" class="form-control"
                                           value="{{ $business->pref($key) }}"
                                           placeholder="{{ trans('preferences.App\Models\Business.'.$key.'.format', ['default' => '']) }}">
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Notifications & Policy Section --}}
                <div class="tg-settings-section">
                    <div class="tg-settings-section__header">
                        <h2 class="tg-settings-section__title"><i class="fa fa-bell-o"></i> {{ trans('manager.businesses.preferences.sections.notifications', ['default' => 'Notifications & Policy']) }}</h2>
                        <p class="tg-settings-section__desc">{{ trans('manager.businesses.preferences.sections.notifications_desc', ['default' => 'Email notifications, guest registration, and cancellation policies.']) }}</p>
                    </div>
                    <div class="tg-settings-section__body">
                        @foreach (['report_daily_schedule', 'disable_outbound_mailing', 'allow_guest_registration'] as $key)
                            @if (isset($parameters[$key]))
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\Business.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\Business.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input tg-toggle" type="checkbox"
                                               id="pref-{{ $key }}" name="{{ $key }}" value="1"
                                               {{ $business->pref($key) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                        @if (isset($parameters['cancellation_policy_advice']))
                        <div class="tg-settings-row tg-settings-row--wide">
                            <div class="tg-settings-row__info">
                                <label class="tg-settings-row__label" for="pref-cancellation_policy_advice">{{ trans('preferences.App\Models\Business.cancellation_policy_advice.label', ['default' => 'Cancellation Policy']) }}</label>
                                <span class="tg-settings-row__help">{{ trans('preferences.App\Models\Business.cancellation_policy_advice.help', ['default' => '']) }}</span>
                            </div>
                            <div class="tg-settings-row__control tg-settings-row__control--full">
                                <textarea id="pref-cancellation_policy_advice" name="cancellation_policy_advice"
                                          class="form-control" rows="3"
                                          placeholder="{{ trans('preferences.App\Models\Business.cancellation_policy_advice.format', ['default' => 'Describe your cancellation policy...']) }}">{{ $business->pref('cancellation_policy_advice') }}</textarea>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Advanced Section --}}
                <div class="tg-settings-section">
                    <div class="tg-settings-section__header">
                        <h2 class="tg-settings-section__title"><i class="fa fa-cogs"></i> {{ trans('manager.businesses.preferences.sections.advanced', ['default' => 'Advanced']) }}</h2>
                        <p class="tg-settings-section__desc">{{ trans('manager.businesses.preferences.sections.advanced_desc', ['default' => 'Vacancy editing and autopublish behavior.']) }}</p>
                    </div>
                    <div class="tg-settings-section__body">
                        @foreach (['vacancy_edit_advanced_mode', 'vacancy_autopublish'] as $key)
                            @if (isset($parameters[$key]))
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\Business.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\Business.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input tg-toggle" type="checkbox"
                                               id="pref-{{ $key }}" name="{{ $key }}" value="1"
                                               {{ $business->pref($key) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Sticky Save Bar --}}
                <div class="tg-settings-savebar" id="settings-savebar">
                    <span class="tg-settings-savebar__text"><i class="fa fa-exclamation-circle"></i> You have unsaved changes</span>
                    <div class="tg-settings-savebar__actions">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="settings-discard">
                            Discard
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm tg-auth-submit" id="settings-save">
                            <span class="tg-auth-submit__label"><i class="fa fa-check"></i> Save Changes</span>
                            <span class="tg-auth-submit__spinner" aria-hidden="true">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Saving&hellip;
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/settings.js'])
@endpush
