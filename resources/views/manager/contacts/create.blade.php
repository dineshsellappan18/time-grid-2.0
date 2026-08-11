@extends('layouts.app')

@section('title', trans('manager.contacts.create.title'))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('manager.addressbook.store', $business) }}" class="tg-form-wizard">
                {{ csrf_field() }}
                <input type="hidden" name="mobile" value="">

                {{-- Step Indicator --}}
                <div class="tg-step-indicator">
                    <div class="tg-step-indicator__step is-active" data-step="0">
                        <span class="tg-step-indicator__number">1</span>
                        <span class="tg-step-indicator__label">Personal Info</span>
                    </div>
                    <div class="tg-step-indicator__connector"></div>
                    <div class="tg-step-indicator__step" data-step="1">
                        <span class="tg-step-indicator__number">2</span>
                        <span class="tg-step-indicator__label">Contact Details</span>
                    </div>
                </div>

                {{-- Step 1: Personal Info --}}
                <div class="tg-form-step is-active" data-step="0">
                    <div class="tg-form-card">
                        <h2 class="tg-form-card__title"><i class="fa fa-user"></i> Personal Information</h2>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="tg-form-group">
                                    <label for="ct-firstname" class="tg-form-label">{{ trans('manager.contacts.form.firstname.label') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="ct-firstname" name="firstname" class="form-control"
                                           value="{{ old('firstname') }}" required
                                           placeholder="{{ trans('manager.contacts.form.firstname.label') }}">
                                    <div class="tg-form-feedback">{{ trans('manager.contacts.form.firstname.validation', ['default' => 'First name is required.']) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tg-form-group">
                                    <label for="ct-lastname" class="tg-form-label">{{ trans('manager.contacts.form.lastname.label') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="ct-lastname" name="lastname" class="form-control"
                                           value="{{ old('lastname') }}" required
                                           placeholder="{{ trans('manager.contacts.form.lastname.label') }}">
                                    <div class="tg-form-feedback">{{ trans('manager.contacts.form.lastname.validation', ['default' => 'Last name is required.']) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="tg-form-group">
                                    <label for="ct-gender" class="tg-form-label">{{ trans('manager.contacts.form.gender.label') }}</label>
                                    <select id="ct-gender" name="gender" class="form-select">
                                        <option value="M">{{ trans('manager.contacts.form.gender.male.label') }}</option>
                                        <option value="F">{{ trans('manager.contacts.form.gender.female.label') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tg-form-group">
                                    <label for="ct-birthdate" class="tg-form-label">{{ trans('manager.contacts.form.birthdate.label') }}</label>
                                    <input type="date" id="ct-birthdate" name="birthdate" class="form-control"
                                           value="{{ old('birthdate') }}">
                                </div>
                            </div>
                        </div>

                        <div class="tg-form-group">
                            <label for="ct-nin" class="tg-form-label">{{ trans('manager.contacts.form.nin.label') }}</label>
                            <input type="text" id="ct-nin" name="nin" class="form-control"
                                   value="{{ old('nin') }}"
                                   placeholder="{{ trans('manager.contacts.form.nin.label') }}">
                        </div>
                    </div>
                </div>

                {{-- Step 2: Contact Details --}}
                <div class="tg-form-step" data-step="1" style="display:none;">
                    <div class="tg-form-card">
                        <h2 class="tg-form-card__title"><i class="fa fa-envelope-o"></i> Contact Details</h2>

                        <div class="tg-form-group">
                            <label for="ct-email" class="tg-form-label">{{ trans('manager.contacts.form.email.label') }}</label>
                            <input type="email" id="ct-email" name="email" class="form-control"
                                   value="{{ old('email') }}"
                                   placeholder="{{ trans('manager.contacts.form.email.label') }}">
                            <div class="tg-form-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="tg-form-group">
                            <label for="ct-mobile" class="tg-form-label">{{ trans('manager.contacts.form.mobile.label') }}</label>
                            <input type="tel" id="ct-mobile" name="mobile-input" class="form-control"
                                   value="{{ old('mobile') }}"
                                   placeholder="{{ trans('manager.contacts.form.mobile.label') }}">
                        </div>

                        <div class="tg-form-group">
                            <label for="ct-address" class="tg-form-label">{{ trans('manager.contacts.form.postal_address.label') }}</label>
                            <input type="text" id="ct-address" name="postal_address" class="form-control"
                                   value="{{ old('postal_address') }}"
                                   placeholder="{{ trans('manager.contacts.form.postal_address.label') }}">
                        </div>

                        <div class="tg-form-group">
                            <label for="ct-notes" class="tg-form-label">{{ trans('manager.contacts.form.notes.label') }}</label>
                            <textarea id="ct-notes" name="notes" class="form-control" rows="3"
                                      placeholder="{{ trans('manager.contacts.form.notes.label') }}">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Navigation Buttons --}}
                <div class="tg-form-wizard__nav">
                    <button type="button" class="btn btn-outline-secondary tg-form-wizard__prev" style="display:none;">
                        <i class="fa fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn btn-primary tg-form-wizard__next">
                        Next <i class="fa fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-success tg-form-wizard__submit tg-auth-submit" style="display:none;">
                        <span class="tg-auth-submit__label"><i class="fa fa-check"></i> {{ trans('manager.contacts.btn.store', ['default' => 'Create Contact']) }}</span>
                        <span class="tg-auth-submit__spinner" aria-hidden="true">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Creating&hellip;
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/form-wizard.js'])
@endpush
