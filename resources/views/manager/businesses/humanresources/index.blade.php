@extends('layouts.app')

@section('title', trans('manager.humanresource.index.title'))
@section('subtitle', trans('manager.humanresource.index.subtitle'))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="mb-0" style="font-size:1.25rem;font-weight:600;">
                    <i class="fa fa-users text-muted"></i> Team Members
                    @if($business->humanresources->isNotEmpty())
                    <span class="badge bg-secondary ms-1">{{ $business->humanresources->count() }}</span>
                    @endif
                </h2>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="fa fa-plus"></i> Add Team Member
                </button>
            </div>

            {{-- Member Cards Grid --}}
            @if($business->humanresources->isNotEmpty())
            <div class="tg-table-wrapper">
                <div class="tg-table-toolbar">
                    <div class="tg-table-search">
                        <i class="fa fa-search tg-table-search__icon"></i>
                        <input type="text" class="tg-table-search__input" placeholder="Search team members..." id="teamSearch">
                    </div>
                </div>

                <div class="tg-member-grid" id="memberGrid">
                    @foreach($business->humanresources as $member)
                    <div class="tg-member-card" data-searchable="{{ strtolower($member->name) }}">
                        <div class="tg-member-card__header">
                            <div class="tg-member-card__avatar">
                                <span class="tg-member-card__initials">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                                <span class="tg-member-card__status-dot {{ $member->capacity > 0 ? 'tg-member-card__status-dot--available' : 'tg-member-card__status-dot--unavailable' }}"></span>
                            </div>
                            <div class="tg-member-card__info">
                                <a href="{{ route('manager.business.humanresource.show', [$business, $member->id]) }}" class="tg-member-card__name">
                                    {{ $member->name }}
                                </a>
                                <div class="tg-member-card__badges">
                                    @if($member->capacity >= 10)
                                    <span class="tg-role-badge tg-role-badge--lead"><i class="fa fa-star"></i> Lead</span>
                                    @elseif($member->capacity >= 5)
                                    <span class="tg-role-badge tg-role-badge--senior"><i class="fa fa-certificate"></i> Senior</span>
                                    @elseif($member->capacity >= 1)
                                    <span class="tg-role-badge tg-role-badge--staff"><i class="fa fa-user"></i> Staff</span>
                                    @else
                                    <span class="tg-role-badge tg-role-badge--inactive"><i class="fa fa-pause-circle"></i> Inactive</span>
                                    @endif
                                    <span class="tg-permission-badge">
                                        <i class="fa fa-shield"></i> Capacity: {{ $member->capacity }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="tg-member-card__meta">
                            <span class="tg-member-card__meta-item">
                                <i class="fa fa-circle {{ $member->capacity > 0 ? 'text-success' : 'text-muted' }}"></i>
                                {{ $member->capacity > 0 ? 'Available' : 'Unavailable' }}
                            </span>
                            @if($member->calendar_link)
                            <span class="tg-member-card__meta-item">
                                <i class="fa fa-calendar-o"></i> Calendar linked
                            </span>
                            @endif
                        </div>
                        <div class="tg-member-card__actions">
                            <a href="{{ route('manager.business.humanresource.edit', [$business, $member->id]) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('manager.business.humanresource.destroy', [$business, $member]) }}" class="d-inline"
                                  onsubmit="return confirm('Remove {{ $member->name }} from the team?')">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="tg-empty-state tg-empty-state--page">
                <i class="fa fa-users tg-empty-state__icon"></i>
                <h3 class="tg-empty-state__title">No team members yet</h3>
                <p class="tg-empty-state__text">Add staff members who provide services to your customers.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="fa fa-plus"></i> Add First Team Member
                </button>
            </div>
            @endif

            {{-- Back Link --}}
            <div class="mt-3">
                <a href="{{ route('manager.business.show', $business) }}" class="text-decoration-none text-muted">
                    <i class="fa fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Add Member Modal --}}
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('manager.business.humanresource.store', $business) }}" id="addMemberForm" novalidate>
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemberModalLabel"><i class="fa fa-user-plus"></i> Add Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="memberName" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                            <input type="text" class="form-control" id="memberName" name="name" required minlength="2" placeholder="Enter member name">
                        </div>
                        <div class="invalid-feedback">Name is required (min 2 characters).</div>
                    </div>
                    <div class="mb-3">
                        <label for="memberCapacity" class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-users"></i></span>
                            <input type="number" class="form-control" id="memberCapacity" name="capacity" required min="0" step="1" value="1" placeholder="e.g. 5">
                        </div>
                        <small class="text-muted">Maximum concurrent appointments this member can handle.</small>
                        <div class="invalid-feedback">Capacity must be 0 or greater.</div>
                    </div>
                    <div class="mb-0">
                        <label for="memberCalendarLink" class="form-label fw-semibold">Calendar Link <span class="text-muted fw-normal">(optional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            <input type="url" class="form-control" id="memberCalendarLink" name="calendar_link" placeholder="https://calendar.example.com/...">
                        </div>
                        <div class="invalid-feedback">Please enter a valid URL.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addMemberSubmit">
                        <i class="fa fa-plus"></i> Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('teamSearch');
    var grid = document.getElementById('memberGrid');

    if (searchInput && grid) {
        searchInput.addEventListener('input', function() {
            var term = this.value.toLowerCase().trim();
            var cards = grid.querySelectorAll('.tg-member-card');
            cards.forEach(function(card) {
                var searchable = card.getAttribute('data-searchable') || '';
                card.style.display = searchable.indexOf(term) !== -1 ? '' : 'none';
            });
        });
    }

    var form = document.getElementById('addMemberForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
});
</script>
@endpush
