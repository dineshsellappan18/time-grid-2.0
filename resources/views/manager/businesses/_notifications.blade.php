<div class="card">
    <div class="card-header">
        <span class="glyphicon glyphicon-list-alt"></span>{{trans('app.notifications.title')}}
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <ul class="demo" data-notification-list>
                    @foreach ($notifications as $notification)
                    @include('manager.businesses._notification', ['notification' => $notification->toArray(), 'timestamp' => Carbon::parse($notification['created_at'])->timezone($business->timezone)])
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="card-footer"></div>
</div>
