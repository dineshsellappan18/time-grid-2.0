@extends('layouts.auth')

@section('content')
<div class="tg-auth-card">
    <h1 class="tg-auth-card__title">{{ trans('auth.reset.title') }}</h1>
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
    @endif

    <form method="POST" action="{{ url('/password/reset') }}" class="tg-auth-form">
        {{ csrf_field() }}
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label" for="email">{{ trans('auth.login.email') }}</label>
            <input id="email" type="email" class="form-control form-control-lg{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>
            @if ($errors->has('email'))
                <div class="invalid-feedback">{{ $errors->first('email') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">{{ trans('auth.login.password') }}</label>
            <input id="password" type="password" class="form-control form-control-lg{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
            @if ($errors->has('password'))
                <div class="invalid-feedback">{{ $errors->first('password') }}</div>
            @endif
        </div>

        <div class="mb-4">
            <label class="form-label" for="password_confirmation">{{ trans('auth.register.password_confirmation') }}</label>
            <input id="password_confirmation" type="password" class="form-control form-control-lg{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" name="password_confirmation" required>
            @if ($errors->has('password_confirmation'))
                <div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
            @endif
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">
            {{ trans('auth.reset.btn.reset') }}
        </button>
    </form>
</div>
@endsection
