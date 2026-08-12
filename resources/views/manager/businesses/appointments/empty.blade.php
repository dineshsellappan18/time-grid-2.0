@extends('layouts.app')

@section('title', trans('manager.agenda.title'))
@section('subtitle', trans('manager.agenda.subtitle'))

@section('content')
<div class="container-fluid px-0">
    <div class="tg-empty-state tg-empty-state--page">
        <i class="fa fa-calendar-o tg-empty-state__icon"></i>
        <h2 class="tg-empty-state__title">{{ trans('emptystate.manager.appointments.title') }}</h2>
        <p class="tg-empty-state__text">{{ trans('emptystate.manager.appointments.hint') }}</p>

        <div class="tg-empty-state__share-link mb-4">
            <div class="input-group" style="max-width: 32rem; margin: 0 auto;">
                <span class="input-group-text"><i class="fa fa-link"></i></span>
                <input type="text" class="form-control" id="businessShareLink"
                       value="{{ url('/'.$business->slug) }}" readonly>
                <button type="button" class="btn btn-outline-primary" id="copyLinkBtn" title="Copy link">
                    <i class="fa fa-clipboard"></i> Copy
                </button>
            </div>
            <small class="text-muted mt-1 d-block">Share this link with your customers to start receiving bookings.</small>
        </div>

        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ route('manager.business.vacancy.create', $business) }}" class="btn btn-primary">
                <i class="fa fa-calendar-plus-o"></i> Set Availability
            </a>
            <a href="{{ route('manager.business.show', $business) }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var copyBtn = document.getElementById('copyLinkBtn');
    var linkInput = document.getElementById('businessShareLink');
    if (copyBtn && linkInput) {
        copyBtn.addEventListener('click', function() {
            linkInput.select();
            navigator.clipboard.writeText(linkInput.value).then(function() {
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
