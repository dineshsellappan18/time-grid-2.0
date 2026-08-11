@extends('layouts.user')

@section('content')
{!! Form::open(['id' => 'postAppointmentStatus', 'method' => 'post', 'route' => ['api.booking.action']]) !!}
<div class="container-fluid">
    {!! $appointment->panel !!}
    {!! $appointment->actionButtons !!}
</div>
{!! Form::close() !!}
@endsection

{{-- ToDo: Reusable code with app/resources/views/user/appointments/dateslot/show.blade.php --}}
@push('footer_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

function prepareEvents(){

        console.log('prepareEvents()');

        var form = document.getElementById("postAppointmentStatus");
        var buttons = document.querySelectorAll('.action');
        var buttons = document.querySelectorAll('.actiongroup');
        var token = document.querySelector('input[name=_token]').value;

        buttons.forEach(function(btn) { btn.addEventListener('click', function(event) {

        event.preventDefault();

        var business = this.dataset.business;
        var appointment = this.dataset.appointment;
        var action = this.dataset.action;
        var code = this.dataset.code;

        this.parentElement.style.display = 'none';

            tgAjax({
                url: form.getAttribute("action"),
                method: 'post',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: { business: business, appointment: appointment, action: action, widget: 'panel' }
            }).done(function (data) {
                    console.log('AJAX Done');
                    document.getElementById(code).outerHTML = data.html;
            }).fail(function (data) {
                    console.log('AJAX Fail');
            }).always(function (data) {
                    this.parentElement.style.display = '';
                    // prepareEvents();
                    console.log('AJAX Finish');
                    console.log(data);
            });
        });
    }

prepareEvents();

});
</script>
@endpush