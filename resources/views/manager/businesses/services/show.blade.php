@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-offset-0 col-sm-offset-0 col-md-offset-3 col-lg-offset-3 toppad" >

            <div class="card border-info">
                <div class="card-header">
                    <h3 class="card-title">{{ $service->name }}</h3>
                </div>

                <ul class="list-group">
                    @if($service->type)
                    <li class="list-group-item">
                        {{ $service->type->name }}
                    </li>
                    @endif
                    <li class="list-group-item">
                        <span class='glyphicon glyphicon-tag'></span>&nbsp;{{ $service->slug }}&nbsp;
                        &nbsp;&nbsp;
                        <span class='glyphicon glyphicon-hourglass'></span>&nbsp;{{ $service->duration }}&prime;
                    </li>
                </ul>

                <div class="card-body">
                    <p>{{ $service->description }}</p>
                </div>

                @include('manager.businesses.services._availability', ['service' => $service])

                <div class="card-footer">
                    {!! Button::danger()->withIcon(Icon::trash())->withAttributes([
                        'type' => 'button',
                        'data-bs-toggle' => 'tooltip',
                        'title' => trans('manager.service.btn.delete'),
                        'data-method' => 'DELETE',
                        'data-confirm' => trans('manager.service.btn.delete').'?']
                    )->asLinkTo( route('manager.business.service.destroy', [$service->business, $service]) ) !!}

                    {!! Button::normal()
                        ->withIcon(Icon::edit())
                        ->asLinkTo(route('manager.business.service.edit', [$service->business, $service->id]) ) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) { new bootstrap.Tooltip(el); });

    var laravel = {
        initialize: function() {,
 
        registerEvents: function() {
            this.methodLinks.on('click', this.handleMethod);
        },
 
        handleMethod: function(e) {
            var link = this;
            var httpMethod = link.data('method').toUpperCase();
            var form;
 
            // If the data-method attribute is not PUT or DELETE,
            // then we don't know what to do. Just ignore.
            if ( $.inArray(httpMethod, ['PUT', 'DELETE']) === - 1 ) {
                return;
            }
 
            // Allow user to optionally provide data-confirm="Are you sure?"
            if ( link.data('confirm') ) {
                if ( ! laravel.verifyConfirm(link) ) {
                    return false;
                }
            }
 
            form = laravel.createForm(link);
            form.submit();
 
            e.preventDefault();
        },
 
        verifyConfirm: function(link) {
            return confirm(link.data('confirm'));
        },
 
        createForm: function(link) {
            var form =
            $('<form>', {
                'method': 'POST',
                'action': link.attr('href')
            });
 
            var token =
            $('<input>', {
                'type': 'hidden',
                'name': '_token',
                    'value': '{{ csrf_token() }}'
                });
 
            var hiddenInput =
            $('<input>', {
                'name': '_method',
                'type': 'hidden',
                'value': link.data('method')
            });
 
            return form.append(token, hiddenInput)
                                 .appendTo('body');
        }
    };
 
    laravel.initialize();
 
});
</script>
@endpush