<div id="panel" class="card">
    <!-- Default panel contents -->
    <div class="card-header">{{ trans('user.appointments.form.timetable.title', ['business' => $business->name]) }}</div>
    <div class="card-body">
        {!! Alert::info(trans('user.appointments.form.timetable.instructions')) !!}

        <div class="row">
            <div class="form-group col-sm-12">
                @foreach ($business->services as $service)
                @if($service->description)
                    <div class="well service-description hidden" id="service-description-{{$service->id}}">
                        <strong>{{$service->name}}:</strong>&nbsp;{{ $service->description }}
                    </div>
                @endif
                @if($service->prerequisites)
                    {!! Panel::warning()->withHeader(Icon::alert() ."&nbsp;&nbsp;". trans('app.label.attention'))
                        ->withBody("<pre>{$service->prerequisites}</pre>")
                        ->withAttributes([
                            'class' => 'service-prerequisites hidden',
                            'id' => "service-prerequisites-{$service->id}"]) !!}
                @endif
                @endforeach
            </div>
        </div>

    <div id="moreDates">
    {!! Button::primary(trans('user.appointments.btn.more_dates'))
        ->asLinkTo(route('user.booking.book', ['business' => $business, 'date' => date('Y-m-d', strtotime("$startFromDate +7 days"))]))
        ->small()
        ->block() !!}
    </div>

    </div>

    <table id="timetable" class="table table-condensed table-hover">
    @foreach ($dates as $date => $vacancies)
        @if (empty($vacancies))
        <tr class="daterow">
            <td class="dateslot disable">
                {!! Button::normal(Carbon::parse($date)->formatLocalized('%A %d %B'))
                    ->block()
                    ->disable()
                    ->prependIcon(Icon::calendar())
                    ->withAttributes(['class' => 'btn-date']) !!}
            </td>
            <td class="serviceslot" >
                <p class="d-none d-sm-block">
                    {!! Icon::remove() !!}&nbsp;&nbsp;{{ trans('user.appointments.form.timetable.msg.no_vacancies') }}
                </p>
                <p class="d-lg-none d-xl-block d-md-none d-lg-block d-sm-none d-md-block">{!! Icon::remove() !!}&nbsp;&nbsp;{{ trans('N/D') }}</p>
            </td>
        </tr>
        @else
        <tr class="daterow date_{{ $date }}">
            <td class="dateslot">
                {!! Button::success(Carbon::parse($date)->formatLocalized('%A %d %B'))
                    ->block()
                    ->prependIcon(Icon::calendar())
                    ->withAttributes(['class' => 'btn-date']) !!}
            </td>
            <td class="serviceslot" >
                @foreach ($vacancies as $vacancy)
                {!! Button::primary($vacancy->service->name)
                    ->prependIcon(Icon::ok())
                    ->withAttributes([
                        'class' => 'service service'.$vacancy->service_id,
                        'data-service' => $vacancy->service_id,
                        'data-date' => $vacancy->date]) !!}
                @endforeach
            </td>
        </tr>
        @endif
    @endforeach
    </table>
</div>

@push('footer_scripts')
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var extraEl = document.getElementById('extra');
    if (extraEl) {
        extraEl.classList.remove('hidden');
        extraEl.style.display = 'none';
    }

    document.querySelectorAll('#timetable .btn.service').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var service = this.dataset.service;
            var date = this.dataset.date;

            document.querySelectorAll('.service-prerequisites').forEach(function(el) { el.style.display = 'none'; });
            var prereqEl = document.getElementById('service-prerequisites-' + service);
            if (prereqEl) { prereqEl.classList.remove('hidden'); prereqEl.style.display = ''; }

            document.querySelectorAll('.service-description').forEach(function(el) { el.style.display = 'none'; });
            var descEl = document.getElementById('service-description-' + service);
            if (descEl) { descEl.classList.remove('hidden'); descEl.style.display = ''; }

            document.querySelectorAll('.service').forEach(function(el) { el.classList.remove('btn-success'); });

            document.getElementById('date').value = date;
            document.getElementById('service').value = service;

            this.classList.toggle('btn-success');

            document.querySelectorAll('#timetable .daterow').forEach(function(row) {
                row.style.display = row.classList.contains('date_' + date) ? '' : 'none';
            });

            if (extraEl) { extraEl.style.display = ''; }
        });
    });

    document.querySelectorAll('#timetable .btn.btn-date').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            document.querySelectorAll('.daterow').forEach(function(row) { row.style.display = ''; });
            if (extraEl) { extraEl.style.display = 'none'; }
        });
    });
});
</script>
@endpush