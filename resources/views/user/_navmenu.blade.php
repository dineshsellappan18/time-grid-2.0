<li class="nav-item dropdown">
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">{{ trans('app.nav.user.business.menu') }}</a>
    <ul class="dropdown-menu">
    @if(isset($business))
        <li>
            <a class="dropdown-item" href="{{ route('user.businesses.home', compact('business')) }}">{!! Icon::map_marker() !!}&nbsp;{{ $business->name }}</a>
        </li>
        <li><hr class="dropdown-divider"></li>
    @endif
        <li>
            <a class="dropdown-item" href="{{ route('user.directory.list') }}">{{ trans('app.nav.user.business.selector') }}</a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('user.subscriptions') }}">{{ trans('app.nav.user.business.my_subscriptions') }}</a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('user.agenda') }}">{{ trans('app.nav.user.business.my_appointments') }}</a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('wizard.welcome') }}">{{ trans('app.nav.wizard') }}</a>
        </li>
    </ul>
</li>
