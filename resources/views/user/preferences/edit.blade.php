@extends('layouts.user')

@section('title', trans('user.preferences.title', ['default' => 'Settings']))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('user.preferences') }}" id="settings-form" class="tg-settings-form">
                {{ csrf_field() }}

                {{-- General Section --}}
                <div class="tg-settings-section">
                    <div class="tg-settings-section__header">
                        <h2 class="tg-settings-section__title"><i class="fa fa-globe"></i> {{ trans('user.preferences.sections.general', ['default' => 'General']) }}</h2>
                        <p class="tg-settings-section__desc">{{ trans('user.preferences.sections.general_desc', ['default' => 'Regional and display preferences.']) }}</p>
                    </div>
                    <div class="tg-settings-section__body">
                        @foreach ($parameters as $key => $value)
                            @if ($value['type'] == 'string')
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\User.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\User.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <input type="text" id="pref-{{ $key }}" name="{{ $key }}"
                                           class="form-control"
                                           value="{{ $user->pref($key) }}"
                                           placeholder="{{ trans('preferences.App\Models\User.'.$key.'.format', ['default' => 'e.g. America/New_York']) }}">
                                </div>
                            </div>
                            @elseif ($value['type'] == 'bool')
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\User.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\User.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input tg-toggle" type="checkbox"
                                               id="pref-{{ $key }}" name="{{ $key }}" value="1"
                                               {{ $user->pref($key) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            @elseif ($value['type'] == 'int')
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\User.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\User.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <input type="number" id="pref-{{ $key }}" name="{{ $key }}"
                                           class="form-control"
                                           value="{{ $user->pref($key) }}"
                                           step="{{ $value['step'] ?? 1 }}">
                                </div>
                            </div>
                            @elseif ($value['type'] == 'time')
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\User.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                    <span class="tg-settings-row__help">{{ trans('preferences.App\Models\User.'.$key.'.help', ['default' => '']) }}</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <input type="time" id="pref-{{ $key }}" name="{{ $key }}"
                                           class="form-control"
                                           value="{{ $user->pref($key) }}">
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
