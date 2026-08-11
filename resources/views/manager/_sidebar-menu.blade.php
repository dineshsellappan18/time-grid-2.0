{{-- Forge SidebarNav: flat primary destinations --}}
<nav class="tg-nav" aria-label="Manager">
    <div class="tg-nav__section">
        <div class="tg-nav__label">{{ $business->name }}</div>
        <a class="tg-nav__item {{ $route == 'manager.business.show' ? 'is-active' : '' }}"
           href="{{ route('manager.business.show', $business->slug) }}">
            <i class="fa fa-tachometer"></i><span>{{ trans('nav.manager.left.dashboard') }}</span>
        </a>
        <a class="tg-nav__item {{ $route == 'manager.business.agenda.index' ? 'is-active' : '' }}"
           href="{{ route('manager.business.agenda.index', $business) }}">
            <i class="fa fa-calendar-check-o"></i><span>{{ trans('nav.manager.left.agenda') }}</span>
        </a>
        <a class="tg-nav__item {{ $route == 'manager.business.agenda.calendar' ? 'is-active' : '' }}"
           href="{{ route('manager.business.agenda.calendar', $business) }}">
            <i class="fa fa-calendar"></i><span>{{ trans('nav.manager.left.calendar') }}</span>
        </a>
        <a class="tg-nav__item {{ $route == 'manager.addressbook.index' ? 'is-active' : '' }}"
           href="{{ route('manager.addressbook.index', $business) }}">
            <i class="fa fa-users"></i><span>{{ trans('nav.manager.left.addressbook') }}</span>
        </a>
        <a class="tg-nav__item {{ $route == 'manager.business.service.index' ? 'is-active' : '' }}"
           href="{{ route('manager.business.service.index', $business) }}">
            <i class="fa fa-tags"></i><span>{{ trans('nav.manager.left.services') }}</span>
        </a>
        <a class="tg-nav__item {{ $route == 'manager.business.humanresource.index' ? 'is-active' : '' }}"
           href="{{ route('manager.business.humanresource.index', $business) }}">
            <i class="fa fa-user-md"></i><span>{{ trans('nav.manager.left.staff') }}</span>
        </a>
        <a class="tg-nav__item {{ in_array($route, ['manager.business.vacancy.create', 'manager.business.vacancy.show'], true) ? 'is-active' : '' }}"
           href="{{ route('manager.business.vacancy.create', $business) }}">
            <i class="fa fa-calendar-o"></i><span>{{ trans('nav.manager.left.availability') }}</span>
        </a>
    </div>

    <div class="tg-nav__section">
        <div class="tg-nav__label">{{ trans('nav.manager.left.preferences') }}</div>
        <a class="tg-nav__item {{ $route == 'manager.business.preferences' ? 'is-active' : '' }}"
           href="{{ route('manager.business.preferences', $business) }}">
            <i class="fa fa-cogs"></i><span>{{ trans('nav.manager.left.preferences') }}</span>
        </a>
        <a class="tg-nav__item {{ $route == 'manager.business.edit' ? 'is-active' : '' }}"
           href="{{ route('manager.business.edit', $business) }}">
            <i class="fa fa-pencil-square-o"></i><span>{{ trans('nav.manager.left.edit') }}</span>
        </a>
        <a class="tg-nav__item {{ $route == 'manager.business.notifications.show' ? 'is-active' : '' }}"
           href="{{ route('manager.business.notifications.show', $business) }}">
            <i class="fa fa-bullhorn"></i><span>{{ trans('nav.manager.left.notifications') }}</span>
        </a>
    </div>
</nav>
