@extends('layouts.auth')

@section('content')
<div class="tg-auth-card">
    <h1 class="tg-auth-card__title">{{ trans('auth.register.btn.submit') }}</h1>
    <p class="tg-auth-card__subtitle">{{ trans('auth.register.title') }}</p>

    @unless(config('root.app.allow_register'))
        <div class="alert alert-danger">{{ trans('app.allow_register') }}</div>
    @endunless

    @if (count($errors) > 0)
    <div class="alert alert-danger">
        <strong>{{ trans('auth.login.alert.whoops') }}</strong> {{ trans('auth.login.alert.message') }}
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @if ($errors->has('email'))
        <a class="btn btn-success w-100 mb-3" href="{{ url('/login') }}">{{ trans('auth.btn.already_registered') }}</a>
    @endif
    @if ($errors->has('password'))
        <a class="btn btn-warning w-100 mb-3" href="{{ url('/password/email') }}">{{ trans('auth.btn.forgot') }}</a>
    @endif
    @endif

    <form method="POST" action="{{ url('/register') }}" id="registration" class="tg-auth-form">
        {{ csrf_field() }}

        @if (!app()->environment('local'))
        <div class="mb-3">
            {!! app('captcha')->display() !!}
        </div>
        @endif

        <div class="mb-3">
            <label class="form-label" for="email">{{ trans('auth.register.email') }}</label>
            <input id="email" type="email" class="form-control form-control-lg" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="name">{{ trans('auth.register.name') }}</label>
            <input id="name" class="form-control form-control-lg" name="name" value="{{ old('name') }}" minlength="3" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">{{ trans('auth.register.password') }}</label>
            <input id="password" type="password" class="form-control form-control-lg" name="password" minlength="6" required>
        </div>

        <div class="mb-4">
            <label class="form-label" for="password_confirmation">{{ trans('auth.register.password_confirmation') }}</label>
            <input id="password_confirmation" type="password" class="form-control form-control-lg" name="password_confirmation" minlength="6" required>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100" id="submit">
            {{ trans('auth.register.btn.submit') }}
        </button>
    </form>

    <div class="tg-auth-divider"><span>{{ trans('auth.label.oauth_direct_access') }}</span></div>
    @include('auth/social')

    <p class="tg-auth-switch text-center mb-0">
        <a class="tg-auth-link" href="{{ url('/login') }}">{{ trans('auth.btn.already_registered') }}</a>
    </p>
</div>
@endsection
