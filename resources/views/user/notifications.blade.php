@extends('layouts.user')

@section('content')

    @foreach ($notifications as $notification)
        <div class="p-3 bg-light rounded">
            {{ $notification->from_id }} {{ $notification->text }}
        </div>
    @endforeach

@endsection