<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;

class BusinessReportsController extends Controller
{
    public function index(Request $request, Business $business)
    {
        Log::info('reports.index', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'view_reports',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->authorize('manage', $business);

        $tz = $business->timezone ?: config('app.timezone');

        $rangeStart = $request->input('from')
            ? Carbon::parse($request->input('from'), $tz)->startOfDay()->utc()
            : Carbon::today($tz)->subDays(29)->startOfDay()->utc();

        $rangeEnd = $request->input('to')
            ? Carbon::parse($request->input('to'), $tz)->endOfDay()->utc()
            : Carbon::today($tz)->endOfDay()->utc();

        $rangeDays = $rangeStart->copy()->diffInDays($rangeEnd) + 1;
        $prevStart = $rangeStart->copy()->subDays($rangeDays);
        $prevEnd   = $rangeStart->copy()->subSecond();

        $appointments = $business->bookings()
            ->whereBetween('start_at', [$rangeStart, $rangeEnd])
            ->get();

        $prevAppointments = $business->bookings()
            ->whereBetween('start_at', [$prevStart, $prevEnd])
            ->get();

        $total     = $appointments->count();
        $served    = $appointments->where('status', Appointment::STATUS_SERVED)->count();
        $canceled  = $appointments->where('status', Appointment::STATUS_CANCELED)->count();
        $confirmed = $appointments->where('status', Appointment::STATUS_CONFIRMED)->count();
        $reserved  = $appointments->where('status', Appointment::STATUS_RESERVED)->count();

        $prevTotal    = $prevAppointments->count();
        $prevServed   = $prevAppointments->where('status', Appointment::STATUS_SERVED)->count();
        $prevCanceled = $prevAppointments->where('status', Appointment::STATUS_CANCELED)->count();

        $completionRate   = $total > 0 ? round(($served / $total) * 100, 1) : 0;
        $cancellationRate = $total > 0 ? round(($canceled / $total) * 100, 1) : 0;

        $prevCompletionRate   = $prevTotal > 0 ? round(($prevServed / $prevTotal) * 100, 1) : 0;
        $prevCancellationRate = $prevTotal > 0 ? round(($prevCanceled / $prevTotal) * 100, 1) : 0;

        $kpis = [
            [
                'label' => 'Total Appointments',
                'value' => $total,
                'icon'  => 'fa-calendar',
                'color' => 'primary',
                'trend' => $this->computeTrend($total, $prevTotal),
            ],
            [
                'label' => 'Completed',
                'value' => $served,
                'icon'  => 'fa-check-circle',
                'color' => 'success',
                'trend' => $this->computeTrend($served, $prevServed),
            ],
            [
                'label' => 'Completion Rate',
                'value' => $completionRate . '%',
                'icon'  => 'fa-line-chart',
                'color' => 'info',
                'trend' => $this->computeTrend($completionRate, $prevCompletionRate),
            ],
            [
                'label' => 'Cancellation Rate',
                'value' => $cancellationRate . '%',
                'icon'  => 'fa-times-circle',
                'color' => 'danger',
                'trend' => $this->computeTrend($cancellationRate, $prevCancellationRate, true),
            ],
        ];

        $dailyLabels = [];
        $dailyData   = [];
        $cursor = $rangeStart->copy();
        while ($cursor->lte($rangeEnd)) {
            $dayStr = $cursor->copy()->timezone($tz)->format('M d');
            $dayCount = $appointments->filter(function ($a) use ($cursor, $tz) {
                return $a->start_at->copy()->timezone($tz)->toDateString() === $cursor->copy()->timezone($tz)->toDateString();
            })->count();
            $dailyLabels[] = $dayStr;
            $dailyData[]   = $dayCount;
            $cursor->addDay();
        }

        $servedLine = [];
        $canceledLine = [];
        $cursor = $rangeStart->copy();
        while ($cursor->lte($rangeEnd)) {
            $dayServed = $appointments->filter(function ($a) use ($cursor, $tz) {
                return $a->status === Appointment::STATUS_SERVED
                    && $a->start_at->copy()->timezone($tz)->toDateString() === $cursor->copy()->timezone($tz)->toDateString();
            })->count();
            $dayCanceled = $appointments->filter(function ($a) use ($cursor, $tz) {
                return $a->status === Appointment::STATUS_CANCELED
                    && $a->start_at->copy()->timezone($tz)->toDateString() === $cursor->copy()->timezone($tz)->toDateString();
            })->count();
            $servedLine[]   = $dayServed;
            $canceledLine[] = $dayCanceled;
            $cursor->addDay();
        }

        $statusLabels = ['Reserved', 'Confirmed', 'Completed', 'Canceled'];
        $statusData   = [$reserved, $confirmed, $served, $canceled];

        $serviceBreakdown = $appointments->groupBy(function ($a) {
            return $a->service_id;
        })->map(function ($group) {
            $service = $group->first()->service;
            return [
                'name'      => $service ? $service->name : 'Unknown',
                'count'     => $group->count(),
                'served'    => $group->where('status', Appointment::STATUS_SERVED)->count(),
                'canceled'  => $group->where('status', Appointment::STATUS_CANCELED)->count(),
            ];
        })->sortByDesc('count')->values()->all();

        $fromDisplay = $rangeStart->copy()->timezone($tz)->format('Y-m-d');
        $toDisplay   = $rangeEnd->copy()->timezone($tz)->format('Y-m-d');

        return view('manager.businesses.reports.index', compact(
            'business', 'kpis',
            'dailyLabels', 'dailyData',
            'servedLine', 'canceledLine',
            'statusLabels', 'statusData',
            'serviceBreakdown',
            'fromDisplay', 'toDisplay',
            'total'
        ));
    }

    public function exportCsv(Request $request, Business $business)
    {
        $this->authorize('manage', $business);

        $tz = $business->timezone ?: config('app.timezone');

        $rangeStart = $request->input('from')
            ? Carbon::parse($request->input('from'), $tz)->startOfDay()->utc()
            : Carbon::today($tz)->subDays(29)->startOfDay()->utc();

        $rangeEnd = $request->input('to')
            ? Carbon::parse($request->input('to'), $tz)->endOfDay()->utc()
            : Carbon::today($tz)->endOfDay()->utc();

        $appointments = $business->bookings()
            ->with(['contact', 'service', 'humanresource'])
            ->whereBetween('start_at', [$rangeStart, $rangeEnd])
            ->orderBy('start_at')
            ->get();

        $csv = "Date,Time,Contact,Service,Staff,Status,Comments\n";

        foreach ($appointments as $a) {
            $date    = $a->start_at->copy()->timezone($tz)->format('Y-m-d');
            $time    = $a->start_at->copy()->timezone($tz)->format('H:i');
            $contact = trim(($a->contact->firstname ?? '') . ' ' . ($a->contact->lastname ?? ''));
            $service = $a->service->name ?? '';
            $staff   = $a->humanresource->name ?? '';
            $status  = $a->statusLabel;
            $comment = str_replace('"', '""', $a->comments ?? '');

            $csv .= "\"{$date}\",\"{$time}\",\"{$contact}\",\"{$service}\",\"{$staff}\",\"{$status}\",\"{$comment}\"\n";
        }

        $filename = "report_{$business->slug}_{$rangeStart->format('Ymd')}_{$rangeEnd->format('Ymd')}.csv";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    private function computeTrend($current, $previous, bool $invertColor = false): array
    {
        if ($previous == 0 && $current == 0) {
            return ['direction' => 'flat', 'value' => '0%', 'color' => 'muted'];
        }
        if ($previous == 0) {
            return ['direction' => 'up', 'value' => 'New', 'color' => $invertColor ? 'danger' : 'success'];
        }

        $change = round((($current - $previous) / $previous) * 100, 1);

        if ($change > 0) {
            return ['direction' => 'up', 'value' => "+{$change}%", 'color' => $invertColor ? 'danger' : 'success'];
        }
        if ($change < 0) {
            return ['direction' => 'down', 'value' => "{$change}%", 'color' => $invertColor ? 'success' : 'danger'];
        }

        return ['direction' => 'flat', 'value' => '0%', 'color' => 'muted'];
    }
}
