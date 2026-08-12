@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">{{ trans('user.businesses.subscriptions.title') }}</div>

                <div class="card-body">
                    @if (!$contacts->isEmpty())
                    <table class="table table-condensed">
	                    @foreach ($contacts as $contact)
	                        <tr>
	                            <td>{{ $contact->nin }}</td>
	                            <td>{{ $contact->firstname }}</td>
	                            <td>{{ $contact->lastname }}</td>
	                            <td>{{ $contact->email }}</td>
	                            <td>
	                            @if($contact->businesses()->count())
	                                @foreach ($contact->businesses as $business)
	                                    {!! Button::normal($business->slug)->asLinkTo(route('user.business.contact.show', [$business, $contact])) !!}
	                                @endforeach
	                            @endif
	                            </td>
	                        </tr>
	                    @endforeach
	                    </table>
                    @else
                    	{{ trans('user.businesses.subscriptions.none_found') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection