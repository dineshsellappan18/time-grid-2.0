@foreach($timetable as $date => $services)
<div class="col-md-2">

    @foreach($services as $service => $times)
    
        <div class="card">

            <div class="card-header">{!! Icon::calendar() !!}&nbsp;{{ $date }}</div>
    
            <div class="card-body">
                <p>{!! Icon::tag() !!}&nbsp;{{ $service }}</p>
            </div>
            
            @include('manager.businesses.vacancies._services', ['date' => $date, 'times' => $times])
        
            <div class="card-footer">{{ $service }}</div>
        
        </div>

    @endforeach

</div>
@endforeach
