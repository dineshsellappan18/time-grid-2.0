<li class="news-item">
<table cellpadding="4">
<tr>
    <td>
        @if ($notification['body']['name'] == 'user.booked')
            <span class="label badge bg-warning text-dark">{!! Icon::calendar() !!}</span>
        @endif
        @if ($notification['body']['name'] == 'appointment.cancel')
            <span class="label badge bg-danger">{!! Icon::calendar() !!}</span>
        @endif
        @if ($notification['body']['name'] == 'appointment.confirm')
            <span class="label badge bg-success">{!! Icon::calendar() !!}</span>
        @endif
        @if ($notification['body']['name'] == 'appointment.serve')
            <span class="label badge bg-info">{!! Icon::calendar() !!}</span>
        @endif
    </td>
    <td>
        @if ($notification['extra'])
            {{trans('notifications.'.$notification['body']['name'], ['user' => $notification['from']['name']] + $notification['extra']) }}
        @else
            {{trans('notifications.'.$notification['body']['name'], ['user' => $notification['from']['name']]) }}
        @endif
        &nbsp;<small class="text-muted" title="{{ $timestamp->toDateTimeString() }}">{{ $timestamp->diffForHumans() }}</small>
    </td>
</tr>
</table>
</li>