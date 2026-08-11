<!-- Notifications Menu -->
<li class="nav-item dropdown" data-nav="notifications">
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <i class="fa fa-calendar-check-o"></i>
        <span class="badge {{ $appointments->count() > 0 ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $appointments->count() }}</span>
    </a>

    @foreach($appointments as $appointment)
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="{{ route('user.agenda') . '#' . $appointment->code() }}">
                <i class="fa fa-calendar-check-o text-info"></i> {{ $appointment->service->name }} : {{ $appointment->business->name }}
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li class="px-3 py-1 text-center"><a href="{{ route('user.agenda') }}">{{ trans('user.dashboard.card.agenda.button') }}</a></li>
    </ul>
    @endforeach
</li>
