@extends('layouts.auth')

@section('content')
<div class="tg-auth-card">
    <h1 class="tg-auth-card__title">{{ trans('auth.reset.title') }}</h1>
    <p class="tg-auth-card__subtitle">{{ trans('app.name') }}</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ url('/password/email') }}" class="tg-auth-form">
        {{ csrf_field() }}

        <div class="mb-4">
            <label class="form-label" for="email">{{ trans('auth.login.email') }}</label>
            <input id="email" type="email" class="form-control form-control-lg{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
            @if ($errors->has('email'))
                <div class="invalid-feedback">{{ $errors->first('email') }}</div>
            @endif
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">
            {{ trans('auth.reset.btn.send_link') }}
        </button>
    </form>

    <p class="tg-auth-switch text-center mb-0 mt-3">
        <a class="tg-auth-link" href="{{ url('/login') }}">{{ trans('auth.btn.already_registered') }}</a>
    </p>
</div>
@endsection
