@extends('layouts.app')

@section('content')
<div class="container-fluid">
@if ($appointments->isEmpty())
    {!! Alert::info(trans('manager.businesses.index.msg.no_appointments')) !!}
@else
    {!! Form::open(['id' => 'postAppointmentStatus', 'method' => 'post', 'route' => ['api.booking.action']]) !!}
    {!! Form::hidden('business', $business->id) !!}
    <div class="container">
    
        @include('widgets.appointment.table._body', ['appointments' => $appointments, 'user' => $user, 'business' => $business])
    
    </div>
    {!! Form::close() !!}
@endif
</div>
@endsection

{{-- ToDo: Reusable code with app/resources/views/manager/businesses/appointments/dateslot/index.blade.php --}}
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

        var business = document.querySelector('input[name=business]').value;
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
                data: { business: business, appointment: appointment, action: action, widget:'row' }
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

    document.getElementById('filter').keyup(function () {

        var search = this.val();

        /* Enable multifield search */
        search = search.replace(/\ /g, '\.\*');
        
        var rex = new RegExp(search, 'i');
        $('.searchable tr').hide();
        $('.searchable tr').filter(function () {
            return rex.test(this.text());
        }).show();

    })

});
</script>
@endpush