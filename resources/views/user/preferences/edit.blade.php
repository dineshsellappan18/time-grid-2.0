@extends('layouts.app')
@section('title', trans('user.preferences.title'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-body">
                    {!! Form::open(['route' => ['user.preferences'], 'id' => 'preferences', 'data-bs-toggle' => 'validator']) !!}
                        @include('user.preferences._form')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
