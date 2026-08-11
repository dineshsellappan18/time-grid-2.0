@extends('layouts.app')

@section('title', trans('manager.businesses.sharing.title', ['business' => $business->name]))
@section('subtitle', trans('manager.businesses.sharing.subtitle'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">

            {{-- Subscription URL --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fa fa-link"></i> {{ trans('manager.businesses.sharing.url.title') ?? 'Subscription URL' }}
                </div>
                <div class="card-body">
                    @if($hasToken)
                        @if(session('new_token_url'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>New token issued.</strong> Copy this URL now — it will not be shown again.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="ical-url" value="{{ session('new_token_url') }}" readonly>
                                <button class="btn btn-outline-primary" type="button" data-clipboard-target="#ical-url" id="copy-url-btn">
                                    <i class="fa fa-clipboard"></i> Copy
                                </button>
                            </div>
                        @else
                            <div class="input-group mb-3">
                                <input type="text" class="form-control text-muted" value="{{ $maskedUrl }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" disabled>
                                    <i class="fa fa-lock"></i> Masked
                                </button>
                            </div>
                            <small class="text-muted">The full URL is only shown immediately after issuing or rotating the token.</small>
                        @endif
                    @else
                        <p class="text-muted mb-3">No token has been issued for this business yet.</p>
                        <form method="POST" action="{{ route('manager.business.agenda.sharing.rotate', [$business]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary" data-action="issue-token">
                                <i class="fa fa-key"></i> Issue Token
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Token Metadata --}}
            @if($hasToken && $tokenMetadata)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fa fa-info-circle"></i> Token Metadata
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width: 200px;">Issued</th>
                                <td>{{ $tokenMetadata['issued_at'] }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Last Rotated</th>
                                <td>{{ $tokenMetadata['rotated_at'] ?? 'Never' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Last Used</th>
                                <td>{{ $tokenMetadata['last_used'] ?? 'Never' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Storage</th>
                                <td><span class="badge bg-success">{{ $tokenMetadata['storage'] }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Guard Mode --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fa fa-shield"></i> Guard Mode
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge {{ $guardMode === 'enforce' ? 'bg-danger' : 'bg-warning text-dark' }} fs-6">
                            {{ ucfirst($guardMode) }}
                        </span>
                        <span class="text-muted">
                            @if($guardMode === 'shadow')
                                Shadow mode — denials are logged but not enforced. Calendar clients continue to work.
                            @else
                                Enforcement active — invalid tokens are rejected with 403.
                            @endif
                        </span>
                    </div>
                    <div class="mt-3">
                        <strong>Divergences:</strong>
                        <span class="badge {{ $divergenceCount > 0 ? 'bg-warning text-dark' : 'bg-success' }}">{{ $divergenceCount }}</span>
                        @if($divergenceCount === 0)
                            <small class="text-muted ms-2">No divergences detected — cutover criteria approaching.</small>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Rotate Token --}}
            @if($hasToken)
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="fa fa-refresh"></i> Rotate Token
                </div>
                <div class="card-body">
                    <p class="text-muted">Rotating the token will immediately invalidate all existing calendar subscriptions using the current URL. Staff and calendar clients will need the new URL.</p>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#rotateConfirmModal" data-action="rotate-token">
                        <i class="fa fa-refresh"></i> Rotate Token...
                    </button>
                </div>
            </div>
            @endif

        </div>

        <div class="col-lg-4">
            {{-- Authorization Matrix --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fa fa-table"></i> Authorization Matrix
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Principal</th>
                                <th>Valid</th>
                                <th>Invalid</th>
                                <th>None</th>
                                <th>Revoked</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($authorizationMatrix as $row)
                            <tr>
                                <td class="fw-bold">{{ $row['principal'] }}</td>
                                <td><span class="badge bg-success">{{ $row['valid_token'] }}</span></td>
                                <td><span class="badge bg-danger">{{ $row['invalid_token'] }}</span></td>
                                <td><span class="badge bg-secondary">{{ $row['no_token'] }}</span></td>
                                <td><span class="badge bg-danger">{{ $row['revoked_token'] }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Denial Log --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-exclamation-triangle"></i> Denial Log</span>
                    <small class="text-muted">Last 50 entries — no PII shown</small>
                </div>
                <div class="card-body p-0">
                    @if(empty($denialLog))
                        <div class="p-4 text-center text-muted">
                            <i class="fa fa-check-circle fa-2x mb-2"></i>
                            <p>No denials recorded.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Outcome</th>
                                        <th>Reason</th>
                                        <th>Correlation ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($denialLog as $entry)
                                    <tr>
                                        <td class="text-nowrap"><small>{{ $entry['timestamp'] }}</small></td>
                                        <td><span class="badge bg-danger">{{ $entry['outcome'] }}</span></td>
                                        <td>{{ $entry['reason'] }}</td>
                                        <td><code class="small">{{ $entry['correlation_id'] }}</code></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Rotate Confirmation Modal --}}
@if($hasToken)
<div class="modal fade" id="rotateConfirmModal" tabindex="-1" aria-labelledby="rotateConfirmLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="rotateConfirmLabel">Confirm Token Rotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>This action cannot be undone.</strong></p>
                <p>Rotating the token will:</p>
                <ul>
                    <li>Immediately revoke the current token</li>
                    <li>Issue a new token and display the URL once</li>
                    <li>Break all existing calendar client subscriptions using the old URL</li>
                </ul>
                <p>Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('manager.business.agenda.sharing.rotate', [$business]) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning" data-action="confirm-rotate">
                        <i class="fa fa-refresh"></i> Rotate Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('footer_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var copyBtn = document.getElementById('copy-url-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var urlInput = document.getElementById('ical-url');
            navigator.clipboard.writeText(urlInput.value).then(function() {
                copyBtn.innerHTML = '<i class="fa fa-check"></i> Copied!';
                setTimeout(function() {
                    copyBtn.innerHTML = '<i class="fa fa-clipboard"></i> Copy';
                }, 2000);
            });
        });
    }
});
</script>
@endpush
