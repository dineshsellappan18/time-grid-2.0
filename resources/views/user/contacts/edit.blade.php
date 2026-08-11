@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="card">

            <div class="card-header">{{ trans('user.contacts.create.title') }}</div>

            <div class="card-body">
                {!! Form::model($contact, ['method' => 'put', 'route' => ['user.business.contact.update', $business, $contact->id ]]) !!}
                    @include('user.contacts._form', ['submitLabel' => trans('user.contacts.btn.update')])
                {!! Form::close() !!}
            </div>

        </div>
    </div>
</div>
@endsection
