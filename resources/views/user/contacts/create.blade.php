@extends('layouts.user')

@section('content')
<div class="container-fluid">

        {!! Alert::info(trans('user.contacts.create.help')) !!}

        <div class="card">
            <div class="card-header">
                {{ trans('user.contacts.create.title') }}
            </div>

            <div class="card-body">
                {!! Form::model($contact, ['route' => ['user.business.contact.store', $business]]) !!}
                @include('user.contacts._form', ['submitLabel' => trans('user.contacts.btn.store'), 'contact' => $contact])
                {!! Form::close() !!}
            </div>
        </div>

</div>
@endsection