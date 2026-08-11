@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="card">
            <div class="card-header">{{ trans('servicetype.title.edit') }}</div>

            <div class="card-body">
                {!! Form::open(['method' => 'put', 'route' => ['manager.business.servicetype.update', $business]]) !!}
                    @include('manager.businesses.servicetype._form', ['submitLabel' => trans('servicetype.btn.update')])
                {!! Form::close() !!}
            </div>

        </div>
    </div>
</div>
@endsection
