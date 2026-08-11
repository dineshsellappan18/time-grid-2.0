<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ trans('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Timegrid — online appointment scheduling">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    @yield('css')
</head>
<body class="tg-auth">
    {!! Analytics::render() !!}

    <a href="#auth-content" class="visually-hidden-focusable">Skip to content</a>

    <div class="tg-auth-shell">
        <header class="tg-auth-brandbar">
            <a href="{{ url('/') }}" class="tg-auth-brand" aria-label="Timegrid home">time<b>grid</b></a>
            <div class="tg-auth-brandbar__lang">
                <ul class="nav mb-0">
                    @include('_navi18n')
                </ul>
            </div>
        </header>

        <main class="tg-auth-main" id="auth-content">
            @include('flash::message')
            @yield('content')
        </main>

        <footer class="tg-auth-footer">
            <a href="https://timegrid.io">timegrid</a>
        </footer>
    </div>

    @stack('footer_scripts')
</body>
</html>
