@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">

                <div class="card-header">
                        {!! Icon::star() !!}&nbsp;{{ $business->subscriptionsCount }} {{ $business->name }}
                </div>

                    <ul class="list-group">

                        <li class="list-group-item">
                            {!! $business->industryIcon() !!}
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
                        </li>

                        @if ($business->pref('show_map') && $business->postal_address)
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    {!! $business->staticMap(11) !!}
                                </div>
                            </div>
                        </li>
                        @endif

                        <li class="list-group-item">
                            {!! Button::large()->success(trans('user.appointments.btn.book'))->asLinkTo(route('user.booking.book', compact('business')))->withIcon(Icon::calendar())->block() !!}
                        </li>

                    </ul>
                
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/ajax.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('postAppointmentStatus');
    if (!form) return;
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