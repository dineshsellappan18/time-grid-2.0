<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ trans('app.name') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
</head>
<body class="tg-welcome">

    {!! Analytics::render() !!}

    <div class="tg-welcome-shell">
        <nav class="tg-welcome-nav">
            <a class="tg-welcome-nav__brand" href="{{ url('/') }}">time<b>grid</b></a>
            <div class="tg-welcome-nav__actions">
                <ul class="nav mb-0">
                    @include('_navi18n')
                </ul>
                <a class="tg-welcome-nav__link" href="{{ url('/login') }}">{{ trans('welcome.jumbotron.btn.login') }}</a>
                <a class="btn btn-primary btn-sm" href="{{ url('/register') }}" data-nav="get-started">{{ trans('welcome.jumbotron.btn.begin') }}</a>
            </div>
        </nav>

        <section class="tg-welcome-hero">
            <div class="tg-welcome-hero__inner">
                <p class="tg-welcome-hero__brand">timegrid</p>
                <h1 class="tg-welcome-hero__title">{{ trans('welcome.jumbotron.title') }}</h1>
                <p class="tg-welcome-hero__lede">{{ trans('welcome.jumbotron.description') }}</p>
                <div class="tg-welcome-hero__cta">
                    <a class="btn btn-light btn-lg" href="{{ url('/register') }}" data-nav="get-started">{{ trans('welcome.jumbotron.btn.begin') }}</a>
                    <a class="btn btn-outline-light btn-lg" href="{{ url('/login') }}">{{ trans('welcome.jumbotron.btn.login') }}</a>
                </div>
            </div>
        </section>

        <section class="tg-welcome-features">
            <div class="tg-welcome-features__inner">
                <h2 class="tg-welcome-features__heading">Built for service professionals</h2>
                <div class="tg-welcome-features__grid">
                    <article class="tg-welcome-feature">
                        <h3>{{ trans('welcome.feature.1.title') }}</h3>
                        <p>{{ trans('welcome.feature.1.content') }}</p>
                    </article>
                    <article class="tg-welcome-feature">
                        <h3>{{ trans('welcome.feature.2.title') }}</h3>
                        <p>{{ trans('welcome.feature.2.content') }}</p>
                    </article>
                    <article class="tg-welcome-feature">
                        <h3>{{ trans('welcome.feature.3.title') }}</h3>
                        <p>{{ trans('welcome.feature.3.content') }}</p>
                    </article>
                    <article class="tg-welcome-feature">
                        <h3>{{ trans('welcome.feature.4.title') }}</h3>
                        <p>{{ trans('welcome.feature.4.content') }}</p>
                    </article>
                </div>
            </div>
        </section>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.lang').forEach(function (el) {
        el.addEventListener('click', function () {
            sessionStorage.language = this.dataset.lang;
        });
    });

    var modalEl = document.getElementById('myModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    if (typeof Storage !== 'undefined') {
        if (sessionStorage.language) {
            modal.hide();
        } else {
            modal.show();
        }
    }
});
</script>

<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="langModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title w-100 text-center" id="langModalTitle">Select a Language</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    @foreach ($availableLanguages as $locale => $language)
                        <div class="col-6 col-md-3 text-center">
                            {!! link_to_route('lang.switch', $language, $locale, ['class' => 'lang', 'data-lang' => $locale]) !!}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
