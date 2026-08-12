@extends('layouts.app')

@section('title', trans('manager.humanresource.edit.title'))
@section('subtitle', $humanresource->name)

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="mb-3">
                <a href="{{ route('manager.business.humanresource.show', [$business, $humanresource->id]) }}" class="text-decoration-none text-muted">
                    <i class="fa fa-arrow-left"></i> Back to {{ $humanresource->name }}
                </a>
            </div>

            <div class="tg-detail-section">
                <div class="tg-detail-section__header">
                    <h2 class="tg-detail-section__title"><i class="fa fa-pencil"></i> Edit Team Member</h2>
                </div>
                <div class="tg-detail-section__body">
                    {!! Form::model($humanresource, ['method' => 'put', 'route' => ['manager.business.humanresource.update', $business, $humanresource->id], 'novalidate']) !!}
                        @include('manager.businesses.humanresources._form', ['submitLabel' => trans('manager.humanresource.btn.update', ['default' => 'Save Changes'])])
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
