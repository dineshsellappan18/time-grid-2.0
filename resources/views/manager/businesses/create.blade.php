@extends('layouts.bare')

@section('title', trans('manager.businesses.create.title'))
@section('subtitle', trans('manager.businesses.msg.register', ['plan' => trans($plan)]))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('manager.business.store') }}" id="registration"
                  class="tg-form-wizard" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input type="hidden" name="plan" value="{{ $plan }}">
                <input type="hidden" name="country_code" value="{{ $countryCode }}">
                <input type="hidden" name="locale" value="{{ $locale }}">
                <input type="hidden" name="phone" value="">
                <input type="hidden" name="strategy" value="timeslot">

                {{-- Step Indicator --}}
                <div class="tg-step-indicator">
                    <div class="tg-step-indicator__step is-active" data-step="0">
                        <span class="tg-step-indicator__number">1</span>
                        <span class="tg-step-indicator__label">Basic Info</span>
                    </div>
                    <div class="tg-step-indicator__connector"></div>
                    <div class="tg-step-indicator__step" data-step="1">
                        <span class="tg-step-indicator__number">2</span>
                        <span class="tg-step-indicator__label">Location & Contact</span>
                    </div>
                    <div class="tg-step-indicator__connector"></div>
                    <div class="tg-step-indicator__step" data-step="2">
                        <span class="tg-step-indicator__number">3</span>
                        <span class="tg-step-indicator__label">Settings</span>
                    </div>
                </div>

                {{-- Step 1: Basic Info --}}
                <div class="tg-form-step is-active" data-step="0">
                    <div class="tg-form-card">
                        <h2 class="tg-form-card__title"><i class="fa fa-building-o"></i> Business Details</h2>

                        <div class="tg-form-group">
                            <label for="biz-name" class="tg-form-label">{{ trans('manager.businesses.form.name.label') }} <span class="text-danger">*</span></label>
                            <input type="text" id="biz-name" name="name" class="form-control"
                                   value="{{ old('name') }}" required minlength="3"
                                   placeholder="{{ trans('manager.businesses.form.name.placeholder') }}">
                            <div class="tg-form-feedback">{{ trans('manager.businesses.form.name.validation', ['default' => 'Please enter a business name (min 3 characters).']) }}</div>
                        </div>

                        <div class="tg-form-group">
                            <label for="biz-desc" class="tg-form-label">{{ trans('manager.businesses.form.description.label') }} <span class="text-danger">*</span></label>
                            <textarea id="biz-desc" name="description" class="form-control" rows="4" required minlength="10"
                                      placeholder="{{ trans('manager.businesses.form.description.placeholder') }}">{{ old('description') }}</textarea>
                            <div class="tg-form-feedback">Description must be at least 10 characters.</div>
                        </div>

                        <div class="tg-form-group">
                            <label for="biz-category" class="tg-form-label">{{ trans('manager.businesses.form.category.label') }} <span class="text-danger">*</span></label>
                            <select id="biz-category" name="category" class="form-select" required>
                                <option value="">Select a category...</option>
                                @foreach($categories as $catId => $catName)
                                    <option value="{{ $catId }}" {{ old('category') == $catId ? 'selected' : '' }}>{{ $catName }}</option>
                                @endforeach
                            </select>
                            <div class="tg-form-feedback">Please select a category.</div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Location & Contact --}}
                <div class="tg-form-step" data-step="1" style="display:none;">
                    <div class="tg-form-card">
                        <h2 class="tg-form-card__title"><i class="fa fa-map-marker"></i> Location & Contact</h2>

                        <div class="tg-form-group">
                            <label for="biz-address" class="tg-form-label">{{ trans('manager.businesses.form.postal_address.label') }}</label>
                            <input type="text" id="biz-address" name="postal_address" class="form-control"
                                   value="{{ old('postal_address') }}"
                                   placeholder="{{ trans('manager.businesses.form.postal_address.placeholder') }}">
                        </div>

                        <div class="tg-form-group">
                            <label for="biz-phone" class="tg-form-label">{{ trans('manager.businesses.form.phone.label') }}</label>
                            <input type="tel" id="biz-phone" name="phone-input" class="form-control"
                                   value="{{ old('phone-input') }}"
                                   placeholder="{{ trans('manager.businesses.form.phone.placeholder') }}">
                        </div>

                        <div class="tg-form-group">
                            <label for="biz-facebook" class="tg-form-label">{{ trans('manager.businesses.form.social_facebook.label') }}</label>
                            <div class="tg-input-wrapper">
                                <span class="tg-input-icon"><i class="fa fa-facebook"></i></span>
                                <input type="text" id="biz-facebook" name="social_facebook" class="form-control"
                                       value="{{ old('social_facebook') }}" style="padding-left: 2.5rem;"
                                       placeholder="{{ trans('manager.businesses.form.social_facebook.placeholder') }}">
                            </div>
                        </div>

                        {{-- File Upload Area --}}
                        <div class="tg-form-group">
                            <label class="tg-form-label">Business Logo (optional)</label>
                            <div class="tg-file-upload">
                                <input type="file" name="logo" class="tg-file-upload__input" accept="image/*" style="display:none;">
                                <div class="tg-file-upload__label">
                                    <i class="fa fa-cloud-upload tg-file-upload__icon"></i>
                                    <span class="tg-file-upload__text">Drag & drop your logo here, or <strong>click to browse</strong></span>
                                    <small class="text-muted">PNG, JPG, or SVG up to 2MB</small>
                                </div>
                                <div class="tg-file-upload__preview" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Settings --}}
                <div class="tg-form-step" data-step="2" style="display:none;">
                    <div class="tg-form-card">
                        <h2 class="tg-form-card__title"><i class="fa fa-cogs"></i> Settings</h2>

                        <div class="tg-form-group">
                            <label for="biz-timezone" class="tg-form-label">{{ trans('manager.businesses.form.timezone.label') }} <span class="text-danger">*</span></label>
                            {!! Timezonelist::create('timezone', $timezone, ['id' => 'biz-timezone', 'name' => 'timezone', 'class' => 'form-select', 'required']) !!}
                            <div class="tg-form-feedback">Please select a timezone.</div>
                        </div>

                        <div class="tg-form-card__summary">
                            <p class="text-muted"><i class="fa fa-info-circle"></i> You can customize more settings after creating your business.</p>
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
                        <span class="tg-auth-submit__label"><i class="fa fa-check"></i> {{ trans('manager.businesses.btn.store', ['default' => 'Create Business']) }}</span>
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
