@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-6 offset-md-3">

        {!! Alert::info(trans('manager.services.create.instructions')) !!}

        <div class="card">
            <div class="card-header">
                {{ trans('manager.services.create.title') }}
            </div>

            <div class="card-body">
                {!! Form::model($service, ['route' => ['manager.business.service.store', $business], 'class' => 'form-horizontal']) !!}
                    @include('manager.businesses.services._form', ['submitLabel' => trans('manager.services.btn.store'), 'extended' => false])
                {!! Form::close() !!}
            </div>
        </div>

    </div>
</div>
@endsection