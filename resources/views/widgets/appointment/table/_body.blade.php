{{-- Appointment Table Partial --}}
<table class="table table-hover">
    <thead>
        <tr>
            <th><span class="d-md-none d-lg-block">{!! Icon::barcode() !!}</span>&nbsp;<span class="d-none d-sm-block d-sm-none d-md-block">{{ trans('user.appointments.index.th.code') }}</span></th>
            <th><span class="d-md-none d-lg-block">{!! Icon::asterisk() !!}</span>&nbsp;<span class="d-none d-sm-block d-sm-none d-md-block">{{ trans('user.appointments.index.th.status') }}</span></th>
            <th><span class="d-md-none d-lg-block">{!! Icon::calendar() !!}</span>&nbsp;<span class="d-none d-sm-block d-sm-none d-md-block">{{ trans('user.appointments.index.th.calendar') }}</span></th>
            <th><span class="d-md-none d-lg-block">{!! Icon::time() !!}</span>&nbsp;<span class="d-none d-sm-block d-sm-none d-md-block">{{ trans('user.appointments.index.th.start_time') }}</span></th>
            <th><span class="d-md-none d-lg-block">{!! Icon::time() !!}</span>&nbsp;<span class="d-none d-sm-block d-sm-none d-md-block">{{ trans('user.appointments.index.th.finish_time') }}</span></th>
            <th><span class="d-md-none d-lg-block">{!! Icon::hourglass() !!}</span>&nbsp;<span class="d-none d-sm-block d-sm-none d-md-block">{{ trans('user.appointments.index.th.duration') }}</span></th>
            <th><span class="d-md-none d-lg-block">{!! Icon::tag() !!}</span>&nbsp;<span class="d-none d-sm-block d-sm-none d-md-block">{{ trans('user.appointments.index.th.service') }}</span></th>
            <th><span class="d-md-none d-lg-block">{!! Icon::user() !!}</span>&nbsp;<span class="d-none d-sm-block d-sm-none d-md-block">{{ trans('user.appointments.index.th.contact') }}</span></th>
            <th></th>
        </tr>
    </thead>
    <tbody class="searchable">

        @foreach ($appointments as $appointment)
        {!! $appointment->row !!}
        @endforeach
    </tbody>
</table>