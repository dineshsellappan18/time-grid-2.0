@extends('layouts.app')

@section('title', trans('manager.businesses.dashboard.title'))
@section('subtitle', $business->name)

@section('content')
<div class="container-fluid px-0">

    @if ($business->services()->count() == 0)
    <div class="row mb-3">
        <div class="col-12">
            {!! Alert::warning(Button::withIcon(Icon::tag())
                ->warning()
                ->asLinkTo( route('manager.business.service.create', $business)) . '&nbsp;' .
                    trans('manager.businesses.dashboard.alert.no_services_set'))
            !!}
        </div>
    </div>
    @endif

    @if ($business->vacancies()->future()->count() == 0)
    <div class="row mb-3">
        <div class="col-12">
            {!! Alert::warning(Button::withIcon(Icon::time())
                ->warning()
                ->asLinkTo( route('manager.business.vacancy.create', $business)) . '&nbsp;' .
                    trans('manager.businesses.dashboard.alert.no_vacancies_set'))
            !!}
        </div>
    </div>
    @endif

    @foreach ($boxes->chunk(3) as $chunk)
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mb-1">
            @foreach ($chunk as $box)
                <div class="col">
                    @include('manager.components.info-box', $box)
                </div>
            @endforeach
        </div>
    @endforeach

</div>
@endsection
