@extends('layouts.app')

@section('title', trans('manager.reports.title', ['default' => 'Reports & Analytics']))
@section('subtitle', $business->name)

@section('content')
<div class="container-fluid px-0">

    {{-- Report Header with Date Range & Export --}}
    <div class="tg-report-header">
        <div class="tg-report-header__left">
            <h2 class="tg-report-header__title"><i class="fa fa-bar-chart"></i> Reports & Analytics</h2>
        </div>
        <div class="tg-report-header__controls">
            <form method="GET" action="{{ route('manager.business.reports.index', $business) }}" class="tg-date-range" id="dateRangeForm">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    <input type="date" class="form-control" name="from" value="{{ $fromDisplay }}" id="reportFrom">
                    <span class="input-group-text">to</span>
                    <input type="date" class="form-control" name="to" value="{{ $toDisplay }}" id="reportTo">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Apply</button>
                </div>
            </form>
            <div class="tg-report-header__export">
                <a href="{{ route('manager.business.reports.export', array_merge([$business], request()->only('from', 'to'))) }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-download"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    {{-- KPI Summary Cards --}}
    @if($total > 0 || true)
    <div class="tg-kpi-grid">
        @foreach($kpis as $kpi)
        <div class="tg-kpi-card tg-kpi-card--{{ $kpi['color'] }}">
            <div class="tg-kpi-card__icon">
                <i class="fa {{ $kpi['icon'] }}"></i>
            </div>
            <div class="tg-kpi-card__body">
                <div class="tg-kpi-card__value">{{ $kpi['value'] }}</div>
                <div class="tg-kpi-card__label">{{ $kpi['label'] }}</div>
            </div>
            <div class="tg-kpi-card__trend tg-kpi-card__trend--{{ $kpi['trend']['color'] }}">
                @if($kpi['trend']['direction'] === 'up')
                <i class="fa fa-arrow-up"></i>
                @elseif($kpi['trend']['direction'] === 'down')
                <i class="fa fa-arrow-down"></i>
                @else
                <i class="fa fa-minus"></i>
                @endif
                <span>{{ $kpi['trend']['value'] }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($total > 0)
    {{-- Charts Row --}}
    <div class="row mt-4">
        {{-- Bar Chart: Daily Appointments --}}
        <div class="col-lg-8 mb-4">
            <div class="tg-report-panel">
                <div class="tg-report-panel__header">
                    <h3 class="tg-report-panel__title"><i class="fa fa-bar-chart"></i> Daily Appointments</h3>
                </div>
                <div class="tg-report-panel__body">
                    <div class="tg-chart-container">
                        <canvas id="dailyBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Doughnut: Status Distribution --}}
        <div class="col-lg-4 mb-4">
            <div class="tg-report-panel">
                <div class="tg-report-panel__header">
                    <h3 class="tg-report-panel__title"><i class="fa fa-pie-chart"></i> Status Breakdown</h3>
                </div>
                <div class="tg-report-panel__body">
                    <div class="tg-chart-container tg-chart-container--square">
                        <canvas id="statusDoughnutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Line Chart: Trends --}}
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="tg-report-panel">
                <div class="tg-report-panel__header">
                    <h3 class="tg-report-panel__title"><i class="fa fa-line-chart"></i> Completion vs Cancellation Trend</h3>
                </div>
                <div class="tg-report-panel__body">
                    <div class="tg-chart-container">
                        <canvas id="trendLineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Service Breakdown Table --}}
        <div class="col-lg-4 mb-4">
            <div class="tg-report-panel">
                <div class="tg-report-panel__header">
                    <h3 class="tg-report-panel__title"><i class="fa fa-list-ul"></i> By Service</h3>
                </div>
                <div class="tg-report-panel__body p-0">
                    <table class="table table-sm mb-0" id="serviceBreakdownTable">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Done</th>
                                <th class="text-center">Cancel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceBreakdown as $svc)
                            <tr>
                                <td>{{ $svc['name'] }}</td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $svc['count'] }}</span></td>
                                <td class="text-center"><span class="badge bg-success">{{ $svc['served'] }}</span></td>
                                <td class="text-center"><span class="badge bg-danger">{{ $svc['canceled'] }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @else
    {{-- Empty State --}}
    <div class="tg-empty-state tg-empty-state--page mt-5">
        <i class="fa fa-bar-chart tg-empty-state__icon"></i>
        <h3 class="tg-empty-state__title">No report data available</h3>
        <p class="tg-empty-state__text">There are no appointments in the selected date range. Try adjusting the dates or create some appointments first.</p>
        <a href="{{ route('manager.business.agenda.calendar', $business) }}" class="btn btn-primary">
            <i class="fa fa-calendar"></i> Go to Calendar
        </a>
    </div>
    @endif

    {{-- Back Navigation --}}
    <div class="mt-3">
        <a href="{{ route('manager.business.show', $business) }}" class="text-decoration-none text-muted">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/reports.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dailyLabels = @json($dailyLabels);
    var dailyData = @json($dailyData);
    var servedLine = @json($servedLine);
    var canceledLine = @json($canceledLine);
    var statusLabels = @json($statusLabels);
    var statusData = @json($statusData);

    if (dailyData && dailyData.length > 0) {
        tgReports.initBarChart('dailyBarChart', dailyLabels, dailyData, 'Appointments');

        tgReports.initLineChart('trendLineChart', dailyLabels, [
            {
                label: 'Completed',
                data: servedLine,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.08)',
                fill: true,
                pointRadius: 2,
                pointHoverRadius: 5,
            },
            {
                label: 'Canceled',
                data: canceledLine,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.08)',
                fill: true,
                pointRadius: 2,
                pointHoverRadius: 5,
            }
        ]);

        tgReports.initDoughnutChart('statusDoughnutChart', statusLabels, statusData, [
            '#6366f1', '#3b82f6', '#22c55e', '#ef4444'
        ]);
    }
});
</script>
@endpush
