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

    <div class="wrapper">

        <!-- Main Header -->
        <header class="main-header">

            <!-- Logo -->
            <a href="{{ url('/') }}" class="logo">
                <span class="logo-mini"><b>t</b>g</span>
                <span class="logo-lg">time<b>grid</b></span>
            </a>

            <!-- Header Navbar -->
            <nav class="navbar navbar-expand-lg">
                <!-- Sidebar toggle button-->
                <button class="btn btn-link sidebar-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#main-sidebar">
                    <span class="visually-hidden">Toggle navigation</span>
                    <i class="fa fa-bars"></i>
                </button>
                <!-- Navbar Right Menu -->
                <div class="ms-auto">
                    <ul class="nav">

                        @include('_navi18n')
                        @include('user._navmenu')

                        <!-- User Account Menu -->
                        @include('_user-account-menu')

                        <!-- Control Sidebar Toggle Button -->
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="offcanvas" data-bs-target="#control-sidebar"><i class="fa fa-question"></i></a>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar" id="main-sidebar">

            <section class="sidebar">

                @if(isset($business))

                @include('manager._sidebar-userpanel', compact('business'))

                @include('manager._search', compact('business'))

                @include('manager._sidebar-menu', compact('business'))

                @endif

            </section>
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    @yield('title', '')
                    <small>@yield('subtitle', '')</small>
                </h1>
            </section>

            <!-- Main content -->
            <section class="content">

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
        </div>

        <!-- Main Footer -->
        @include('_footer')

        <!-- Control Sidebar -->
        <aside class="offcanvas offcanvas-end" id="control-sidebar" tabindex="-1">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">{{ trans('app.nav.help') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-justified" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#control-sidebar-userhelp-tab" data-bs-toggle="tab"><i class="fa fa-question"></i></a></li>
                    <li class="nav-item"><a class="nav-link" href="#control-sidebar-settings-tab" data-bs-toggle="tab"><i class="fa fa-gears"></i></a></li>
                </ul>
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="control-sidebar-userhelp-tab">
                        {!! $help !!}
                    </div>
                    <div class="tab-pane fade" id="control-sidebar-settings-tab">
                        <form method="post">
                            <h5>General Settings</h5>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">Report panel usage</label>
                                </div>
                                <p class="text-muted small">Some information about this general settings option</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

</div>
<!-- ./wrapper -->

@vite(['resources/js/app.js'])

@stack('footer_scripts')

<script type="text/javascript">
$(document).ready(function() {
    $('.btn').tooltipster({ animation: "grow", theme: 'tooltipster-light' });
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
});
</script>

</body>
</html>
