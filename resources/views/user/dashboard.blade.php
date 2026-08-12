@extends('layouts.user')

@section('content')
<div class="container py-5">

    @if($appointmentsCount + $subscriptionsCount == 0)
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm text-center p-5">
                <div class="card-body">
                    <i class="fa fa-search fa-3x text-muted mb-3"></i>
                    <h3 class="mb-3">{{trans('user.dashboard.card.directory.title')}}</h3>
                    <p class="text-muted mb-4">{{trans('user.dashboard.card.directory.description')}}</p>
                    <a href="{{ route('user.directory.list') }}" class="btn btn-primary btn-lg">
                        <i class="fa fa-th-list me-1"></i> {{trans('user.dashboard.card.directory.button')}}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row g-4">
        @if($appointmentsCount > 0)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                            <i class="fa fa-calendar-check-o fa-2x text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">{{trans_choice('user.dashboard.card.agenda.title', $appointmentsCount)}}</h4>
                            <span class="badge bg-primary rounded-pill mt-1">{{ $appointmentsCount }}</span>
                        </div>
                    </div>
                    <p class="text-muted flex-grow-1">{{trans_choice('user.dashboard.card.agenda.description', $appointmentsCount)}}</p>
                    <a href="{{ route('user.agenda') }}" class="btn btn-primary">
                        <i class="fa fa-list me-1"></i> {{trans('user.dashboard.card.agenda.button')}}
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if($subscriptionsCount > 0)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                            <i class="fa fa-building-o fa-2x text-success"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">{{trans('user.dashboard.card.subscriptions.title', ['count' => $subscriptionsCount])}}</h4>
                            <span class="badge bg-success rounded-pill mt-1">{{ $subscriptionsCount }}</span>
                        </div>
                    </div>
                    <p class="text-muted flex-grow-1">{{trans('user.dashboard.card.subscriptions.description')}}</p>
                    <a href="{{ route('user.subscriptions') }}" class="btn btn-success">
                        <i class="fa fa-address-book-o me-1"></i> {{trans('user.dashboard.card.subscriptions.button')}}
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row mt-4">
        <div class="col-md-6 offset-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <i class="fa fa-plus-circle fa-2x text-muted mb-2"></i>
                    <h5 class="mb-2">Book a new appointment</h5>
                    <p class="text-muted mb-3">Browse available businesses and services</p>
                    <a href="{{ route('user.directory.list') }}" class="btn btn-outline-primary">
                        <i class="fa fa-th-list me-1"></i> {{trans('user.dashboard.card.directory.button')}}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
