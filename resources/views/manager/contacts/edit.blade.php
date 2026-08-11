@extends('layouts.app')

@section('title', trans('manager.contacts.create.title'))

@section('content')
<div class="container-fluid">

        <div class="card">

            <div class="card-header">{{ trans('manager.contacts.create.title') }}</div>

            <div class="card-body">

                {!! Form::model($contact, ['method' => 'put', 'route' => ['manager.addressbook.update', $business, $contact], 'class' => 'form-horizontal']) !!}
                    @include('manager.contacts._form', ['submitLabel' => trans('manager.contacts.btn.update')])
                {!! Form::close() !!}

            </div>

        </div>

</div>
@endsection
