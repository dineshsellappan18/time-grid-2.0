@extends('layouts.auth')

@section('content')
<div class="tg-auth-card">
    <h1 class="tg-auth-card__title">{{ trans('auth.login.title') }}</h1>
    <p class="tg-auth-card__subtitle">{{ trans('app.name') }}</p>

    @if (session('status'))
        <div class="tg-auth-alert tg-auth-alert--success" role="alert">
            <i class="fa fa-check-circle"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

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

    <form method="POST" action="{{ url('/login') }}" class="tg-auth-form" id="login-form" novalidate>
        {{ csrf_field() }}

        <div class="mb-3">
            <label class="form-label" for="email">{{ trans('auth.login.email') }}</label>
            <div class="tg-input-wrapper">
                <input id="email" type="email" name="email"
                       class="form-control form-control-lg{{ $errors->has('email') ? ' is-invalid' : '' }}"
                       value="{{ old('email') }}"
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
                <input id="password" type="password" name="password"
                       class="form-control form-control-lg{{ $errors->has('password') ? ' is-invalid' : '' }}"
                       placeholder="Enter your password"
                       required
                       minlength="6"
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

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember">{{ trans('auth.login.remember_me') }}</label>
            </div>
            <a class="tg-auth-link" href="{{ url('/password/reset') }}">{{ trans('auth.login.forgot') }}</a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 tg-auth-submit" id="login-submit">
            <span class="tg-auth-submit__label">{{ trans('auth.login.login') }}</span>
            <span class="tg-auth-submit__spinner" aria-hidden="true">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                Signing in&hellip;
            </span>
        </button>
    </form>

    <div class="tg-auth-divider"><span>{{ trans('auth.label.oauth_direct_access') }}</span></div>
    @include('auth/social')

    <p class="tg-auth-switch text-center mb-0">
        <a class="tg-auth-link" href="{{ url('/register') }}">{{ trans('auth.btn.not_registered') }}</a>
    </p>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/auth-validation.js'])
@endpush
