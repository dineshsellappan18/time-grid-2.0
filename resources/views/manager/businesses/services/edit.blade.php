@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-6 offset-md-3">

        <div class="card">
            <div class="card-header">{{ trans('manager.services.edit.title') }}</div>

            <div class="card-body">
                {!! Form::model($service, ['method' => 'put', 'route' => ['manager.business.service.update', $service->business, $service->id], 'class' => 'form-horizontal']) !!}
                    @include('manager.businesses.services._form', ['submitLabel' => trans('manager.service.btn.update'), 'extended' => true])
                {!! Form::close() !!}
            </div>
        </div>

    </div>
</div>
@endsection
