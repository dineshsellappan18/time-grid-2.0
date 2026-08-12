@extends('layouts.app')

@section('title', trans('manager.humanresource.create.title'))
@section('subtitle', trans('manager.humanresource.create.subtitle'))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="mb-3">
                <a href="{{ route('manager.business.humanresource.index', $business) }}" class="text-decoration-none text-muted">
                    <i class="fa fa-arrow-left"></i> Back to Team
                </a>
            </div>

            <div class="tg-detail-section">
                <div class="tg-detail-section__header">
                    <h2 class="tg-detail-section__title"><i class="fa fa-user-plus"></i> Add Team Member</h2>
                </div>
                <div class="tg-detail-section__body">
                    {!! Form::model($humanresource, ['route' => ['manager.business.humanresource.store', $business], 'novalidate']) !!}
                        @include('manager.businesses.humanresources._form', ['submitLabel' => trans('manager.humanresource.btn.store', ['default' => 'Add Member'])])
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
