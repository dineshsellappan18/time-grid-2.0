@extends('layouts.app')

@section('title', trans('manager.services.create.title', ['default' => 'Create Service']))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('manager.business.service.store', $business) }}" class="tg-form-modern">
                {{ csrf_field() }}

                <div class="tg-form-card">
                    <h2 class="tg-form-card__title"><i class="fa fa-briefcase"></i> {{ trans('manager.services.create.title', ['default' => 'New Service']) }}</h2>
                    <p class="tg-form-card__desc">{{ trans('manager.services.create.instructions', ['default' => 'Define a service that your business offers.']) }}</p>

                    <div class="tg-form-group">
                        <label for="svc-name" class="tg-form-label">{{ trans('manager.service.form.name.label') }} <span class="text-danger">*</span></label>
                        <input type="text" id="svc-name" name="name" class="form-control"
                               value="{{ old('name') }}" required
                               placeholder="{{ trans('manager.service.form.name.label') }}">
                        <div class="tg-form-feedback">Service name is required.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="tg-form-group">
                                <label for="svc-duration" class="tg-form-label">{{ trans('manager.service.form.duration.label') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-hourglass-half"></i></span>
                                    <input type="number" id="svc-duration" name="duration" class="form-control"
                                           value="{{ old('duration') }}" required step="5" min="5"
                                           placeholder="30">
                                </div>
                                <div class="tg-form-feedback">Duration is required (min 5 minutes).</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($types->count() > 0)
                            <div class="tg-form-group">
                                <label for="svc-type" class="tg-form-label">{{ trans('manager.service.form.servicetype.label') }}</label>
                                <select id="svc-type" name="type_id" class="form-select">
                                    @foreach($types as $typeId => $typeName)
                                        <option value="{{ $typeId }}">{{ $typeName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="tg-form-group">
                        <label for="svc-desc" class="tg-form-label">{{ trans('manager.contacts.form.description.label', ['default' => 'Description']) }}</label>
                        <textarea id="svc-desc" name="description" class="form-control" rows="3"
                                  placeholder="{{ trans('manager.contacts.form.description.label', ['default' => 'Describe this service...']) }}">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="tg-form-wizard__nav">
                    <button type="submit" class="btn btn-success tg-form-wizard__submit tg-auth-submit">
                        <span class="tg-auth-submit__label"><i class="fa fa-check"></i> {{ trans('manager.services.btn.store', ['default' => 'Create Service']) }}</span>
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
