<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ isset($business) ? $business->name . ' / ' : '' }}{{ trans('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <link rel="manifest" href="/manifest.json">

@yield('css')

@yield('headscripts')

</head>

<body class="skin-blue">

    {!! Analytics::render() !!}

    @include('_github-forkme')

    <div class="wrapper">

        <!-- Main Header -->
        <header class="main-header">
            <nav class="navbar navbar-expand-lg">
                <div class="container">
                    <a href="{{ route('home') }}" class="navbar-brand">time<b>grid</b></a>
                    <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbar-collapse">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <ul class="navbar-nav me-auto">
                            @include('_navi18n')

                            @if(auth()->check())
                                @include('user._navmenu')
                            @endif
                        </ul>
                    </div>

                    <div class="ms-auto">
                        <ul class="navbar-nav">

                            @if(auth()->check())
                                @include('user._notifications-menu')
                                @include('_user-account-menu')
                            @else
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ url('/login') }}"><i class="fa fa-sign-in"></i> {{ trans('app.nav.login') }}</a>
                                </li>
                            @endif

                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <div class="content-wrapper" style="margin-left: 0;">
            <div class="container">
                <section class="content-header">
                    <h1>
                        @yield('title', '')
                        <small>@yield('subtitle', '')</small>
                    </h1>
                </section>

                <section class="content">

                    @include('flash::message')

                    @yield('content')

                </section>
            </div>
        </div>

        @include('_footer')

</div>
<!-- ./wrapper -->

@vite(['resources/js/app.js'])

<script type="text/javascript">
$(document).ready(function() {
    $('.btn').tooltipster({ animation: "grow", theme: 'tooltipster-light' });
});
</script>

@stack('footer_scripts')

</body>
</html>
