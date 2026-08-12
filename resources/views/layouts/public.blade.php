<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ isset($business) ? $business->name . ' / ' : '' }}{{ trans('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">

@yield('css')
@yield('headscripts')
</head>
<body class="tg-public">
    {!! Analytics::render() !!}
    @include('_github-forkme')

    <div class="tg-public-shell">
        @include('cookieConsent::index')

        <header class="tg-public-topbar">
            <div class="container tg-public-topbar__inner">
                <a href="{{ route('home') }}" class="tg-public-brand">time<b>grid</b></a>

                <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#tg-public-nav">
                    <i class="fa fa-bars"></i>
                </button>

                <div class="collapse navbar-collapse tg-public-nav" id="tg-public-nav">
                    <ul class="nav align-items-center gap-1 mb-0">
                        @include('_navi18n')
                        @include('user._navmenu')
                    </ul>
                    <ul class="nav align-items-center gap-1 mb-0 ms-lg-auto">
                        @if(!auth()->guest())
                            @include('user._notifications-menu')
                            @include('_user-account-menu')
                        @endif
                    </ul>
                </div>
            </div>
        </header>

        <main class="tg-public-main">
            <div class="container">
                @hasSection('title')
                <section class="tg-page__header px-0">
                    <h1 class="tg-page__title">
                        @yield('title')
                        @hasSection('subtitle')
                            <small class="tg-page__subtitle">@yield('subtitle')</small>
                        @endif
                    </h1>
                </section>
                @endif

                <section class="tg-public-content">
                    @include('flash::message')
                    @include('_errors')
                    @yield('content')
                </section>
            </div>
        </main>

    </div>

@vite(['resources/js/app.js'])
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
</script>
@stack('footer_scripts')
</body>
</html>
