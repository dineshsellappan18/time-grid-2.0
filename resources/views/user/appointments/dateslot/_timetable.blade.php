<div id="panel" class="card">
    <!-- Default panel contents -->
    <div class="card-header">{{ trans('user.appointments.form.timetable.title') }}</div>
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
    document.getElementById('extra').removeClass('hidden').hide();
    $('#timetable .btn.service').click(function(e){
        var service = this.dataset.service;
        console.log('Press ' + service);
        document.querySelectorAll('.service-prerequisites').hide();
        $('#service-prerequisites-'+service).removeClass('hidden').show();
        document.querySelectorAll('.service-description').hide();
        $('#service-description-'+service).removeClass('hidden').show();
        document.querySelectorAll('.service').removeClass('btn-success');
        document.getElementById('date').val( this.dataset.date );
        document.getElementById('service').val( this.dataset.service );
        this.toggleClass('btn-success');
        $('tr:not(.date_'+this.dataset.date+')').hide();
        document.getElementById('extra').show();
    });
    $('#timetable .btn.btn-date').click(function(e){
        document.querySelectorAll('.daterow').show();
        document.getElementById('extra').hide();
    });
    document.getElementById('date').click(function(e){
        document.getElementById('panel').show();
        return false;
    });
});
</script>
@endpush