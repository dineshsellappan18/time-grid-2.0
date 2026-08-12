@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">

                <div class="card-header">
                    {!! Icon::star() !!}&nbsp;{{ $business->subscriptionsCount }}
                </div>

                    <ul class="list-group">

                        <li class="list-group-item text-center">
                            <h1>{!! $business->name !!}</h1>
                        </li>

                        <li class="list-group-item">
                            {!! $business->industryIcon !!}
                        </li>

                        <li class="list-group-item">
                            <div class="row">
                            <div class="col-md-12">
                                <div class="media">
                                  <div class="flex-shrink-0 me-3 d-none d-md-block">
                                    <a href="#">{!! $business->facebookImg('normal') !!}</a>
                                  </div>
                                  <div class="media-body">
                                    <div class="{{ $business->pref('description_div_class') }}">
                                        <h5>{!! Markdown::convertToHtml(strip_tags($business->description)) !!}</h5>
                                    </div>
                                  </div>
                                </div>
                            </div>
                            </div>
                        </li>

                        @if ($business->phone || $business->postal_address)
                        <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-4">
                                    @if ($business->pref('show_phone') && $business->phone)
                                        {!! Icon::phone() !!}&nbsp;{{ $business->phone }}
                                    @endif
                                    </div>
                                    <div class="col-md-8">
                                    @if ($business->pref('show_postal_address') && $business->postal_address)
                                        {!! Icon::home() !!}&nbsp;{{ $business->postal_address }}
                                    @endif
                                    </div>
                                </div>
                        </li>
                        @endif

                        @if ($business->pref('show_map') && $business->postal_address)
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    {!! $business->staticMap(11) !!}
                                </div>
                            </div>
                        </li>
                        @endif

                        @if (!($appointment and $appointment->isActive()))
                        <li class="list-group-item">
                            @if ($user->getContactSubscribedTo($business->id) === null)
                                {!! Button::large()->primary(trans('user.business.btn.subscribe_to', ['business' => $business->name]))
                                                   ->asLinkTo(route('user.business.contact.create', $business))
                                                   ->withIcon(Icon::star())->block() !!}
                            @else
                                @if($available)
                                    {!! Button::large()->success(trans('user.appointments.btn.book'))
                                                       ->asLinkTo(route('user.booking.book', $business))
                                                       ->withIcon(Icon::calendar())->block() !!}
                                @else
                                    <div class="alert alert-warning">{{ trans('user.appointments.alert.no_vacancies') }}</div>
                                @endif
                            @endif
                        </li>
                        @endif

                        @if($user->isOwnerOf($business->id))
                        <li class="list-group-item">
                            {!! Button::primary(trans('user.go_to_business_dashboard', ['business' => $business->name]))->withIcon(Icon::dashboard())->block()->large()->asLinkTo(route('manager.business.show', $business), $business->name) !!}
                        </li>
                        @endif

                    </ul>
                
            </div>

            @if ($appointment)
            {!! Form::open(['id' => 'postAppointmentStatus', 'method' => 'post', 'route' => ['api.booking.action']]) !!}
                {!! $appointment->panel !!}
            {!! Form::close() !!}
            @endif

        </div>
    </div>
</div>
@endsection

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
        var panel = $('#'+code);

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