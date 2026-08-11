<div class="tg-auth-social">
    <a class="btn btn-outline-secondary w-100 tg-auth-social__btn" href="{{ route('social.login', ['facebook']) }}">
        <i class="fa fa-facebook"></i> {{ trans('auth.social.facebook') }}
    </a>
    <a class="btn btn-outline-secondary w-100 tg-auth-social__btn" href="{{ route('social.login', ['google']) }}">
        <i class="fa fa-google"></i> {{ trans('auth.social.google') }}
    </a>
    <a class="btn btn-outline-secondary w-100 tg-auth-social__btn" href="{{ route('social.login', ['github']) }}">
        <i class="fa fa-github"></i> {{ trans('auth.social.github') }}
    </a>
</div>
