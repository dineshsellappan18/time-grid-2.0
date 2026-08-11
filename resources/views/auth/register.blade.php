@extends('layouts.auth')

@section('content')
<div class="tg-auth-card">
    <h1 class="tg-auth-card__title">{{ trans('auth.register.btn.submit') }}</h1>
    <p class="tg-auth-card__subtitle">{{ trans('auth.register.title') }}</p>

    @unless(config('root.app.allow_register'))
        <div class="tg-auth-alert tg-auth-alert--warning" role="alert">
            <i class="fa fa-info-circle"></i>
            <span>{{ trans('app.allow_register') }}</span>
        </div>
    @endunless

    @if (count($errors) > 0)
    <div class="tg-auth-alert tg-auth-alert--danger" role="alert">
        <i class="fa fa-exclamation-circle"></i>
        <div>
            <strong>{{ trans('auth.login.alert.whoops') }}</strong> {{ trans('auth.login.alert.message') }}
        </div>
    </div>
    @if ($errors->has('email'))
        <a class="btn btn-outline-primary w-100 mb-3" href="{{ url('/login') }}">{{ trans('auth.btn.already_registered') }}</a>
    @endif
    @if ($errors->has('password'))
        <a class="btn btn-outline-warning w-100 mb-3" href="{{ url('/password/email') }}">{{ trans('auth.btn.forgot') }}</a>
    @endif
    @endif

    <form method="POST" action="{{ url('/register') }}" id="registration" class="tg-auth-form" novalidate>
        {{ csrf_field() }}

        @if (!app()->environment('local'))
        <div class="mb-3">
            {!! app('captcha')->display() !!}
        </div>
        @endif

        <div class="mb-3">
            <label class="form-label" for="email">{{ trans('auth.register.email') }}</label>
            <div class="tg-input-wrapper">
                <input id="email" type="email"
                       class="form-control form-control-lg{{ $errors->has('email') ? ' is-invalid' : '' }}"
                       name="email" value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required
                       aria-describedby="email-feedback">
                <i class="fa fa-envelope tg-input-icon"></i>
            </div>
            @if ($errors->has('email'))
                <div class="invalid-feedback d-block" id="email-feedback">{{ $errors->first('email') }}</div>
            @else
                <div class="invalid-feedback" id="email-feedback">Please enter a valid email address.</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label" for="name">{{ trans('auth.register.name') }}</label>
            <div class="tg-input-wrapper">
                <input id="name"
                       class="form-control form-control-lg{{ $errors->has('name') ? ' is-invalid' : '' }}"
                       name="name" value="{{ old('name') }}"
                       placeholder="Your full name"
                       minlength="3" required
                       aria-describedby="name-feedback">
                <i class="fa fa-user tg-input-icon"></i>
            </div>
            @if ($errors->has('name'))
                <div class="invalid-feedback d-block" id="name-feedback">{{ $errors->first('name') }}</div>
            @else
                <div class="invalid-feedback" id="name-feedback">Name must be at least 3 characters.</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">{{ trans('auth.register.password') }}</label>
            <div class="tg-input-wrapper">
                <input id="password" type="password"
                       class="form-control form-control-lg{{ $errors->has('password') ? ' is-invalid' : '' }}"
                       name="password"
                       placeholder="Min. 6 characters"
                       minlength="6" required
                       aria-describedby="password-feedback">
                <i class="fa fa-lock tg-input-icon"></i>
                <button type="button" class="tg-password-toggle" aria-label="Toggle password visibility">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
            @if ($errors->has('password'))
                <div class="invalid-feedback d-block" id="password-feedback">{{ $errors->first('password') }}</div>
            @else
                <div class="invalid-feedback" id="password-feedback">Password must be at least 6 characters.</div>
            @endif
        </div>

        <div class="mb-4">
            <label class="form-label" for="password_confirmation">{{ trans('auth.register.password_confirmation') }}</label>
            <div class="tg-input-wrapper">
                <input id="password_confirmation" type="password"
                       class="form-control form-control-lg{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                       name="password_confirmation"
                       placeholder="Repeat your password"
                       minlength="6" required
                       aria-describedby="password-confirm-feedback">
                <i class="fa fa-lock tg-input-icon"></i>
            </div>
            @if ($errors->has('password_confirmation'))
                <div class="invalid-feedback d-block" id="password-confirm-feedback">{{ $errors->first('password_confirmation') }}</div>
            @else
                <div class="invalid-feedback" id="password-confirm-feedback">Passwords do not match.</div>
            @endif
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 tg-auth-submit" id="submit">
            <span class="tg-auth-submit__label">{{ trans('auth.register.btn.submit') }}</span>
            <span class="tg-auth-submit__spinner" aria-hidden="true">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                Creating account&hellip;
            </span>
        </button>
    </form>

    <div class="tg-auth-divider"><span>{{ trans('auth.label.oauth_direct_access') }}</span></div>
    @include('auth/social')

    <p class="tg-auth-switch text-center mb-0">
        <a class="tg-auth-link" href="{{ url('/login') }}">{{ trans('auth.btn.already_registered') }}</a>
    </p>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/auth-validation.js'])
@endpush
