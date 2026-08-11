@extends('layouts.auth')

@section('content')
<div class="tg-auth-card">
    <h1 class="tg-auth-card__title">{{ trans('auth.login.title') }}</h1>
    <p class="tg-auth-card__subtitle">{{ trans('app.name') }}</p>

    @if (count($errors) > 0)
    <div class="alert alert-danger">
        <strong>{{ trans('auth.login.alert.whoops') }}</strong> {{ trans('auth.login.alert.message') }}
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <a class="btn btn-success w-100 mb-3" href="{{ url('/register') }}">{{ trans('auth.btn.not_registered') }}</a>
    @endif

    <form method="POST" action="{{ url('/login') }}" class="tg-auth-form">
        {{ csrf_field() }}

        <div class="mb-3">
            <label class="form-label" for="email">{{ trans('auth.login.email') }}</label>
            <input id="email" type="email" name="email" class="form-control form-control-lg" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">{{ trans('auth.login.password') }}</label>
            <input id="password" type="password" name="password" class="form-control form-control-lg" required>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember">{{ trans('auth.login.remember_me') }}</label>
            </div>
            <a class="tg-auth-link" href="{{ url('/password/reset') }}">{{ trans('auth.login.forgot') }}</a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">{{ trans('auth.login.login') }}</button>
    </form>

    <div class="tg-auth-divider"><span>{{ trans('auth.label.oauth_direct_access') }}</span></div>
    @include('auth/social')

    <p class="tg-auth-switch text-center mb-0">
        <a class="tg-auth-link" href="{{ url('/register') }}">{{ trans('auth.btn.not_registered') }}</a>
    </p>
</div>
@endsection
