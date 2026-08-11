@extends('layouts.user')

@section('content')
<div class="container-fluid">
    
    <div class="col-md-4 col-md-offset-4">
    {!! Form::open(['id' => 'postAppointmentStatus', 'method' => 'post', 'route' => ['api.booking.action']]) !!}
    @if ($appointments->count())
        @foreach ($appointments as $appointment)
            {!! $appointment->panel !!}
        @endforeach
    @else
        <div class="row">
            {!! Alert::info(trans('user.appointments.alert.empty_list')) !!}
        </div>
    @endif
    {!! Form::close() !!}
    </div>
    
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/ajax.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('postAppointmentStatus');
    var actionUrl = form.getAttribute('action');
    var token = document.querySelector('input[name=_token]').value;

    document.querySelectorAll('.action').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            var business = this.dataset.business;
            var appointment = this.dataset.appointment;
            var action = this.dataset.action;
            var code = this.dataset.code;

            tgAppointmentAction(this, actionUrl, {
                _token: token,
                business: business,
                appointment: appointment,
                action: action,
                widget: 'panel'
            }, '#' + code);
        });
    });
});
</script>
@endpush
