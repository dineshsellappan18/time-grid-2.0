@extends('layouts.auth')

@section('content')
<div class="tg-auth-card">
    <h1 class="tg-auth-card__title">{{ trans('auth.reset.title') }}</h1>
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
            <strong>{{ trans('auth.login.alert.whoops') }}</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ url('/password/email') }}" class="tg-auth-form" id="password-reset-form" novalidate>
        {{ csrf_field() }}

        <div class="mb-4">
            <label class="form-label" for="email">{{ trans('auth.login.email') }}</label>
            <div class="tg-input-wrapper">
                <input id="email" type="email"
                       class="form-control form-control-lg{{ $errors->has('email') ? ' is-invalid' : '' }}"
                       name="email" value="{{ old('email') }}"
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

        <button type="submit" class="btn btn-primary btn-lg w-100 tg-auth-submit">
            <span class="tg-auth-submit__label">{{ trans('auth.reset.btn.send_link') }}</span>
            <span class="tg-auth-submit__spinner" aria-hidden="true">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                Sending&hellip;
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
