@extends('layouts.auth')

@section('content')
<div class="tg-auth-card">
    <h1 class="tg-auth-card__title">{{ trans('auth.reset.title') }}</h1>
    <p class="tg-auth-card__subtitle">{{ trans('app.name') }}</p>

    @if (count($errors) > 0)
    <div class="tg-auth-alert tg-auth-alert--danger" role="alert">
        <i class="fa fa-exclamation-circle"></i>
        <div>
            <strong>{{ trans('auth.login.alert.whoops') }}</strong> {{ trans('auth.login.alert.message') }}
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ url('/password/reset') }}" class="tg-auth-form" id="password-reset-confirm-form" novalidate>
        {{ csrf_field() }}
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label" for="email">{{ trans('auth.login.email') }}</label>
            <div class="tg-input-wrapper">
                <input id="email" type="email"
                       class="form-control form-control-lg{{ $errors->has('email') ? ' is-invalid' : '' }}"
                       name="email" value="{{ $email ?? old('email') }}"
                       placeholder="you@example.com"
                       required autofocus
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
            <label class="form-label" for="password">{{ trans('auth.login.password') }}</label>
            <div class="tg-input-wrapper">
                <input id="password" type="password"
                       class="form-control form-control-lg{{ $errors->has('password') ? ' is-invalid' : '' }}"
                       name="password"
                       placeholder="Min. 6 characters"
                       required minlength="6"
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
                       required minlength="6"
                       aria-describedby="password-confirm-feedback">
                <i class="fa fa-lock tg-input-icon"></i>
            </div>
            @if ($errors->has('password_confirmation'))
                <div class="invalid-feedback d-block" id="password-confirm-feedback">{{ $errors->first('password_confirmation') }}</div>
            @else
                <div class="invalid-feedback" id="password-confirm-feedback">Passwords do not match.</div>
            @endif
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 tg-auth-submit">
            <span class="tg-auth-submit__label">{{ trans('auth.reset.btn.reset') }}</span>
            <span class="tg-auth-submit__spinner" aria-hidden="true">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                Resetting&hellip;
            </span>
        </button>
    </form>

    <p class="tg-auth-switch text-center mb-0 mt-3">
        <a class="tg-auth-link" href="{{ url('/login') }}">{{ trans('auth.btn.already_registered') }}</a>
    </p>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/auth-validation.js'])
@endpush
