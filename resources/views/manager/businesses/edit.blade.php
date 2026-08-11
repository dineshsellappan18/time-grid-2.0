@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="card">
            <div class="card-header">{{ trans('manager.businesses.edit.title') }}</div>

            <div class="card-body">
                {!! Form::model($business, ['method' => 'put', 'route' => ['manager.business.update', $business], 'id' => 'registration', 'data-bs-toggle' => 'validator']) !!}
                @include('manager.businesses._form', ['submitLabel' => trans('manager.businesses.btn.update')])
                {!! Form::close() !!}
            </div>

        </div>
    </div>
</div>
@endsection


