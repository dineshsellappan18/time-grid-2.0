<!-- User Account Menu -->
<li class="nav-item dropdown" data-nav="user-menu">
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <img src="{{ $gravatarURL }}" class="rounded-circle" width="25" height="25" alt="{{ $user->name }}">
        <span class="d-none d-sm-inline">{{ $user->name }}</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        <li class="px-3 py-2 text-center">
            <img src="{{ $gravatarURL }}" class="rounded-circle mb-2" width="80" height="80" alt="{{ $user->name }}">
            <p class="mb-0" title="{{ $timezone }}">{{ $user->name }}</p>
            <p class="text-muted small">{{ $user->email }}</p>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <div class="row px-3">
                <div class="col-4 text-center">
                    <a class="dropdown-item" href="{{ route('manager.business.index') }}">{{ trans('app.nav.manager.business.menu') }}</a>
                </div>
                <div class="col-4 text-center"></div>
                <div class="col-4 text-center">
                    <a class="dropdown-item" href="{{ docs_url(Session::get('language')) }}" target="_blank">{{ trans('app.nav.manual') }}</a>
                </div>
            </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li class="px-3 d-flex justify-content-between">
            <a href="{{ route('user.preferences') }}" class="btn btn-outline-secondary btn-sm">{{ trans('app.nav.preferences') }}</a>
            <a href="{{ url('/logout') }}" class="btn btn-outline-secondary btn-sm">{{ trans('app.nav.logout') }}</a>
        </li>
    </ul>
</li>
