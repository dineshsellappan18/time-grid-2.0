@extends('layouts.app')

@section('title', trans('manager.vacancies.title'))

@section('content')
<div class="container-fluid">

    @if(count($timetable) > 0)
        <div class="row">
            @include('manager.businesses.vacancies._days', ['dates' => $timetable])
        </div>
    @else
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center py-5">
                <h2>{!! Icon::calendar() !!} {{ trans('manager.vacancies.msg.edit.no_vacancies_yet') }}</h2>
                <p class="text-muted">{{ trans('manager.vacancies.msg.edit.publish_first') }}</p>
                <a href="{{ route('manager.business.vacancy.create', [$business]) }}" class="btn btn-primary btn-lg mt-3">
                    {!! Icon::plus() !!} {{ trans('manager.vacancies.btn.create') }}
                </a>
            </div>
        </div>
    @endif

</div>
@endsection