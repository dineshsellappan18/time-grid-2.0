@extends('root.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Platform Health & Modernization Console</h2>

    {{-- Runtime Status Tiles --}}
    <div class="row mb-4">
        @foreach($runtime as $service => $info)
        <div class="col-md col-sm-6 mb-3">
            <div class="card h-100 {{ $info['status'] === 'ok' ? 'border-success' : 'border-danger' }}">
                <div class="card-body text-center">
                    <h6 class="card-subtitle text-muted text-uppercase mb-2">{{ ucfirst($service) }}</h6>
                    <h4 class="card-title mb-1">
                        @if($info['status'] === 'ok')
                            <span class="text-success">{{ $info['version'] }}</span>
                        @else
                            <span class="text-danger">{{ $info['version'] }}</span>
                        @endif
                    </h4>
                    <span class="badge {{ $info['status'] === 'ok' ? 'bg-success' : 'bg-danger' }}">
                        {{ $info['status'] }}
                    </span>
                    @if(isset($info['message']))
                        <br><small class="text-muted">{{ $info['message'] }}</small>
                    @endif
                    @if(isset($info['built_at']))
                        <br><small class="text-muted">Built: {{ $info['built_at'] }}</small>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Phase Timeline --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa fa-road"></i> Phase Timeline
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Phase</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Exit Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($phaseTimeline as $phase)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $phase['phase'] }}</span></td>
                        <td>{{ $phase['name'] }}</td>
                        <td>
                            @if($phase['status'] === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($phase['status'] === 'in_progress')
                                <span class="badge bg-primary">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                        <td>{{ $phase['exit_date'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mb-4">
        {{-- Architecture Metrics --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fa fa-cubes"></i> Architecture Metrics
                </div>
                <div class="card-body">
                    @if($architectureMetrics['available'])
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted">Layer Violations</th>
                                    <td>
                                        <span class="badge {{ $architectureMetrics['layer_violations'] === 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $architectureMetrics['layer_violations'] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Dependency Cycles</th>
                                    <td>
                                        <span class="badge {{ $architectureMetrics['cycles'] === 0 ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $architectureMetrics['cycles'] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">PHPStan Level</th>
                                    <td><span class="badge bg-info">{{ $architectureMetrics['phpstan_level'] }}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Coverage</th>
                                    <td>{{ $architectureMetrics['coverage'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <small class="text-muted d-block mt-2">Last updated: {{ $architectureMetrics['last_updated'] }}</small>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fa fa-exclamation-circle fa-2x mb-2"></i>
                            <p>{{ $architectureMetrics['message'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Supply Chain & Hot Paths --}}
        <div class="col-lg-6">
            <div class="card h-100 mb-3">
                <div class="card-header">
                    <i class="fa fa-chain"></i> Supply Chain
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted">PHP Packages</th>
                                <td>{{ $supplyChain['php_packages'] }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Security Advisories</th>
                                <td>
                                    <span class="badge {{ $supplyChain['advisories'] === 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $supplyChain['advisories'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Node Lock Present</th>
                                <td>
                                    @if($supplyChain['node_available'])
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-warning text-dark">No</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card h-100">
                <div class="card-header">
                    <i class="fa fa-bolt"></i> Hot-Path Query Counts
                </div>
                <div class="card-body">
                    @if($hotPathQueries['available'] ?? false)
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted">Agenda Index</th>
                                    <td><span class="badge bg-info">{{ $hotPathQueries['agenda_index'] }}</span> queries</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">iCal Feed</th>
                                    <td><span class="badge bg-info">{{ $hotPathQueries['ical_feed'] }}</span> queries</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Booking Availability</th>
                                    <td><span class="badge bg-info">{{ $hotPathQueries['booking_availability'] }}</span> queries</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fa fa-exclamation-circle"></i> {{ $hotPathQueries['message'] ?? 'Unavailable' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Guard Mode --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa fa-shield"></i> iCal Guard Mode
        </div>
        <div class="card-body">
            <span class="badge {{ $guardMode === 'enforce' ? 'bg-danger' : 'bg-warning text-dark' }} fs-6">
                {{ ucfirst($guardMode) }}
            </span>
            <span class="text-muted ms-2">
                @if($guardMode === 'shadow')
                    Shadow mode — denials are logged but access is not blocked.
                @else
                    Enforcement active — invalid tokens are rejected.
                @endif
            </span>
        </div>
    </div>

    {{-- Audit Trail --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-history"></i> Audit Trail</span>
            <small class="text-muted">No PII rendered</small>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('root.console') }}" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="filter_actor" class="form-control form-control-sm" placeholder="Actor ID" value="{{ $auditTrail['filters']['actor'] }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="filter_action" class="form-control form-control-sm" placeholder="Action" value="{{ $auditTrail['filters']['action'] }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="filter_resource" class="form-control form-control-sm" placeholder="Resource" value="{{ $auditTrail['filters']['resource'] }}">
                </div>
                <div class="col-md-2">
                    <select name="filter_outcome" class="form-select form-select-sm">
                        <option value="">All outcomes</option>
                        <option value="allowed" {{ $auditTrail['filters']['outcome'] === 'allowed' ? 'selected' : '' }}>Allowed</option>
                        <option value="denied" {{ $auditTrail['filters']['outcome'] === 'denied' ? 'selected' : '' }}>Denied</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">Filter</button>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>Actor</th>
                            <th>Resource</th>
                            <th>Action</th>
                            <th>Outcome</th>
                            <th>Correlation ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditTrail['entries'] as $entry)
                        <tr>
                            <td class="text-nowrap"><small>{{ $entry->occurred_at }}</small></td>
                            <td>{{ $entry->actor_id ?? '—' }}</td>
                            <td>{{ $entry->resource_type }}</td>
                            <td>{{ $entry->action }}</td>
                            <td>
                                @if($entry->outcome === 'denied')
                                    <span class="badge bg-danger">denied</span>
                                @elseif($entry->outcome === 'allowed')
                                    <span class="badge bg-success">allowed</span>
                                @else
                                    <span class="badge bg-secondary">{{ $entry->outcome ?? '—' }}</span>
                                @endif
                            </td>
                            <td><code class="small">{{ $entry->correlation_id ?? '—' }}</code></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No audit entries found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($auditTrail['pagination']->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $auditTrail['pagination']->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
