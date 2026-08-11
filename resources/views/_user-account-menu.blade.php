{{-- UserMenu — Forge topbar account dropdown --}}
<li class="nav-item dropdown" data-nav="user-menu">
    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="{{ $gravatarURL }}" class="rounded-circle" width="28" height="28" alt="{{ $user->name }}">
        <span class="d-none d-sm-inline">{{ $user->name }}</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end tg-user-menu">
        <li class="tg-user-menu__header px-3 py-3 text-center">
            <img src="{{ $gravatarURL }}" class="rounded-circle mb-2" width="64" height="64" alt="{{ $user->name }}">
            <div class="fw-semibold text-truncate" title="{{ $user->name }}">{{ $user->name }}</div>
            <div class="text-muted small text-truncate" title="{{ $user->email }}">{{ $user->email }}</div>
        </li>
        <li><hr class="dropdown-divider my-0"></li>
        <li>
            <a class="dropdown-item" href="{{ route('user.profile') }}">
                <i class="fa fa-user-circle-o"></i>&nbsp; {{ trans('app.nav.profile', ['default' => 'My Profile']) }}
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('manager.business.index') }}">
                {{ trans('app.nav.manager.business.menu') }}
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ docs_url(Session::get('language')) }}" target="_blank" rel="noopener">
                {{ trans('app.nav.manual') }}
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('user.preferences') }}">
                {{ trans('app.nav.preferences') }}
            </a>
        </li>
        <li><hr class="dropdown-divider my-0"></li>
        <li>
            <a class="dropdown-item text-danger" href="{{ url('/logout') }}">
                {{ trans('app.nav.logout') }}
            </a>
        </li>
    </ul>
</li>
