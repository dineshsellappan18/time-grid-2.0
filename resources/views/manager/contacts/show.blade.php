@extends('layouts.app')

@section('title', $contact->fullname)

@section('css')
<style>
.user-row {
    margin-bottom: 14px;
}

.user-row:last-child {
    margin-bottom: 0;
}

.dropdown-user {
    margin: 13px 0;
    padding: 5px;
    height: 100%;
}

.dropdown-user:hover {
    cursor: pointer;
}

.table-user-information > tbody > tr:first-child {
    border-top: 0;
}

.table-user-information > tbody > tr > td {
    border-top: 0;
}
.toppad {
    margin-top:20px;
}
</style>
@endsection

@section('content')
<div class="container-fluid">

    <div class="col-12 col-sm-12 col-md-8 col-lg-8 col-offset-0 col-sm-offset-0 col-md-offset-2 col-lg-offset-2 toppad" >

        <div class="card border-info">

            <div class="card-header">
                <h3 class="card-title">{{ $contact->fullname }}</h3>
            </div>

            <div class="card-body">

                <div class="row">
                    <div class="col-md-3 col-lg-3 " align="center">
                        @if($contact->email)
                            <img alt="{{$contact->fullname}}"
                                 src="{{ Gravatar::get($contact->email) }}"
                                 class="rounded-circle">
                        @endif
                        <p>&nbsp;</p>
                        <small>{{ trans('app.gender.'.$contact->gender) }} {{ $contact->age or '' }}</small>
                    </div>
                    
                    <div class=" col-md-9 col-lg-9 ">
                        <table class="table table-user-information">
                            <tbody>
                            @if ($contact->email)
                            <tr>
                                <td class="text-right">
                                    <label class="control-label">
                                        {{ trans('manager.contacts.label.email') }}
                                    </label>
                                    <span class="badge bg-warning text-dark" title="Confidential">C</span>
                                </td>
                                <td>{{ $contact->email }}</td>
                            </tr>
                            @endif
                            @if ($contact->nin)
                            <tr>
                                <td class="text-right">
                                    <label class="control-label">
                                        {{ trans('manager.contacts.label.nin') }}
                                    </label>
                                    <span class="badge bg-danger" title="Restricted PII">R</span>
                                </td>
                                <td>{{ $contact->nin }}</td>
                            </tr>
                            @endif
                            @if ($contact->birthdate)
                            <tr>
                                <td class="text-right">
                                    <label class="control-label">
                                        {{ trans('manager.contacts.label.birthdate') }}
                                    </label>
                                    <span class="badge bg-danger" title="Restricted PII">R</span>
                                </td>
                                <td>{{ $contact->birthdate->formatLocalized('%d %B %Y') }}</td>
                            </tr>
                            @endif
                            @if ($contact->mobile)
                            <tr>
                                <td class="text-right">
                                    <label class="control-label">
                                        {{ trans('manager.contacts.label.mobile') }}
                                    </label>
                                    <span class="badge bg-danger" title="Restricted PII">R</span>
                                </td>
                                <td>{{ (trim($contact->mobile) != '') ? phone_format($contact->mobile, $contact->mobile_country) : '' }}</td>
                            </tr>
                            @endif
                            @if ($contact->postal_address)
                            <tr>
                                <td class="text-right">
                                    <label class="control-label">
                                        {{ trans('manager.contacts.label.postal_address') }}
                                    </label>
                                    <span class="badge bg-warning text-dark" title="Confidential">C</span>
                                </td>
                                <td>{{ $contact->postal_address }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-right">
                                    <label class="control-label">
                                        {{ trans('manager.contacts.label.member_since') }}
                                    </label>
                                    </td>
                                <td>{{ $contact->pivot->created_at->diffForHumans() }}</td>
                            </tr>
                            <tr>
                                <td class="text-right">
                                    <label class="control-label">{{ trans('manager.contacts.label.notes') }}</label>
                                </td>
                                <td>{{ $contact->pivot->notes }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <div class="card-footer">
            {!! $contact->quality == 100 ? ProgressBar::success($contact->quality)->animated()->striped()->visible() : ProgressBar::normal($contact->quality)->animated()->striped()->visible() !!}

            @if ($contact->user)
            {!! Button::success($contact->user->username)->withIcon(Icon::ok_circle()) !!}
            @else
            {!! Button::warning()->withIcon(Icon::remove_circle()) !!}
            @endif

            <span class="float-end">
            {!! Button::warning()
                ->withIcon(Icon::edit())
                ->asLinkTo( route('manager.addressbook.edit', [$business, $contact]) )
                ->withAttributes([
                    'data-for' => 'edit',
                    'data-bs-toggle' => 'tooltip',
                    'title' => trans('manager.contacts.btn.edit')])
                !!}

            {!! Button::danger()
                ->withIcon(Icon::trash())
                ->asLinkTo( route('manager.addressbook.destroy', [$business, $contact]) )
                ->withAttributes([
                    'id' => 'delete-btn',
                    'type' => 'button',
                    'data-for' => 'delete',
                    'data-bs-toggle' => 'tooltip',
                    'data-method' => 'DELETE',
                    'title' => trans('manager.contacts.btn.delete'),
                    'data-confirm'=> trans('manager.contacts.btn.confirm_delete')])
                !!}
            </span>
        </div>

        {{-- Data Subject Rights --}}
        @if(auth()->user()->isOwnerOf($business->id))
        <div class="card-footer bg-light">
            <small class="text-muted d-block mb-2"><i class="fa fa-shield"></i> Data Subject Rights (GDPR)</small>
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('manager.addressbook.export', [$business, $contact]) }}"
                   class="btn btn-outline-primary"
                   data-action="export"
                   data-bs-toggle="tooltip"
                   title="Export contact data (portability)">
                    <i class="fa fa-download"></i> Export
                </a>
                <a href="{{ route('manager.addressbook.edit', [$business, $contact]) }}"
                   class="btn btn-outline-secondary"
                   data-action="rectify"
                   data-bs-toggle="tooltip"
                   title="Rectify contact data (correction)">
                    <i class="fa fa-pencil"></i> Rectify
                </a>
                <form method="POST" action="{{ route('manager.addressbook.erase', [$business, $contact]) }}" class="d-inline" data-action="erase">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger"
                            data-bs-toggle="tooltip"
                            title="Erase restricted PII (right to erasure)"
                            onclick="return confirm('This will permanently erase all restricted personal data for this contact. This action cannot be undone. Continue?')">
                        <i class="fa fa-eraser"></i> Erase
                    </button>
                </form>
            </div>
        </div>
        @endif
            
        </div>

    @if($contact->hasAppointment())
    @include('manager.contacts._appointment', ['appointments' => $contact->appointments()->orderBy('start_at')->ofBusiness($business->id)->Active()->get()] )
    @endif

    @if(auth()->user()->isOwnerOf($business->id))
    {!! Button::large()->success(trans('user.appointments.btn.book_in_biz_on_behalf_of', ['biz' => $business->name, 'contact' => $contact->fullname()]))
                       ->asLinkTo(route('user.booking.book', ['business' => $business, 'behalfOfId' => $contact->id]))
                       ->withIcon(Icon::calendar())->block() !!}
    @endif

    </div>

</div>
@endsection

@push('footer_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var panels = document.querySelectorAll('.user-infos');
    var panelsButton = document.querySelectorAll('.dropdown-user');
    panels.hide();

    //Click dropdown
    panelsButton.click(function() {
        //get data-for attribute
        var dataFor = this.dataset.for;
        var idFor = $(dataFor);

        //current button
        var currentButton = this;
        idFor.slideToggle(400, function() {
            //Completed slidetoggle
            if(idFor.is(':visible'))
            {
                currentButton.html('<i class="glyphicon glyphicon-chevron-up text-muted"></i>');
            }
            else
            {
                currentButton.html('<i class="glyphicon glyphicon-chevron-down text-muted"></i>');
            }
        })
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) { new bootstrap.Tooltip(el); });
});

(function() {
 
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
 
            return form.append(token, hiddenInput).appendTo('body');
        }
    };
 
    laravel.initialize();

    document.querySelectorAll('img').error(function(){
        this.style.display = 'none';
    });

})();
</script>
@endpush