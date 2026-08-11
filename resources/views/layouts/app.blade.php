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
<body class="tg-app">

    {!! Analytics::render() !!}

    <div class="tg-shell">
        <aside class="offcanvas-lg offcanvas-start tg-sidebar" tabindex="-1" id="main-sidebar" aria-label="Business navigation">
            <div class="offcanvas-header tg-sidebar__brand d-lg-none">
                <a href="{{ url('/') }}" class="tg-brand">time<b>grid</b></a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#main-sidebar" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0 d-flex flex-column">
                <div class="tg-sidebar__brand d-none d-lg-flex">
                    <a href="{{ url('/') }}" class="tg-brand">time<b>grid</b></a>
                </div>

                <div class="tg-sidebar__body">
                    @if(isset($business))
                        @include('manager._sidebar-userpanel', compact('business'))
                        @include('manager._search', compact('business'))
                        @include('manager._sidebar-menu', compact('business'))
                    @endif
                </div>
            </div>
        </aside>

        <div class="tg-main">
            <header class="tg-topbar">
                <button class="btn btn-link tg-topbar__toggle d-lg-none" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#main-sidebar" aria-controls="main-sidebar">
                    <span class="visually-hidden">Toggle navigation</span>
                    <i class="fa fa-bars"></i>
                </button>

                <div class="tg-topbar__crumbs">
                    @if(isset($business))
                        <span class="tg-topbar__crumb">{{ $business->name }}</span>
                        <span class="tg-topbar__sep">/</span>
                    @endif
                    <span class="tg-topbar__crumb tg-topbar__crumb--current">@yield('title', '')</span>
                </div>

                @if(isset($business))
                <form class="tg-topbar__search d-none d-md-flex" method="post" action="{{ route('manager.search', $business) }}" role="search">
                    {{ csrf_field() }}
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                        <input type="text" name="criteria" class="form-control" placeholder="{{ trans('app.search.placeholder') }}">
                    </div>
                </form>
                @endif

                <div class="tg-topbar__chip d-none d-lg-inline-flex" title="Timezone">
                    <i class="fa fa-globe"></i>
                    <span>{{ $timezone ?? (isset($business) ? $business->timezone : config('app.timezone')) }}</span>
                </div>

                <div class="tg-topbar__actions">
                    <ul class="nav align-items-center gap-1 mb-0">
                        @include('_navi18n')
                        @include('user._navmenu')
                        @include('_user-account-menu')
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="offcanvas" data-bs-target="#control-sidebar" aria-label="Help">
                                <i class="fa fa-question-circle"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </header>

            <div class="tg-page">
                <section class="tg-page__header">
                    <h1 class="tg-page__title">
                        @yield('title', '')
                        @hasSection('subtitle')
                            <small class="tg-page__subtitle">@yield('subtitle')</small>
                        @endif
                    </h1>
                </section>

                <section class="tg-page__body">
                    @include('flash::message')
                    @include('_errors')
                    @yield('content')

                    @if(!session()->has('selected.business'))
                        {!! Button::success(trans('app.btn.get_to_dashboard'))
                                    ->large()
                                    ->block()
                                    ->asLinkTo( route('manager.business.index') ) !!}
                    @endif
                </section>

                @include('_footer')
            </div>
        </div>

        <aside class="offcanvas offcanvas-end" id="control-sidebar" tabindex="-1">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">{{ trans('app.nav.help') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                {!! $help !!}
            </div>
        </aside>
    </div>

@vite(['resources/js/app.js'])
@stack('footer_scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
</script>
</body>
</html>
