<div class="mb-3">
    <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text"><i class="fa fa-user"></i></span>
        {!! Form::text('name', null, ['required', 'class' => 'form-control', 'id' => 'name', 'minlength' => '2', 'placeholder' => 'Enter member name']) !!}
    </div>
    <div class="invalid-feedback">Name is required (min 2 characters).</div>
</div>

<div class="mb-3">
    <label for="capacity" class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text"><i class="fa fa-users"></i></span>
        {!! Form::number('capacity', null, ['required', 'class' => 'form-control', 'id' => 'capacity', 'min' => '0', 'step' => '1', 'placeholder' => 'e.g. 5']) !!}
    </div>
    <small class="text-muted">Maximum concurrent appointments this member can handle.</small>
</div>

<div class="mb-4">
    <label for="calendar_link" class="form-label fw-semibold">Calendar Link <span class="text-muted fw-normal">(optional)</span></label>
    <div class="input-group">
        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
        {!! Form::text('calendar_link', null, ['class' => 'form-control', 'id' => 'calendar_link', 'placeholder' => 'https://calendar.example.com/...']) !!}
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="fa fa-check"></i> {{ $submitLabel }}
    </button>
    <a href="{{ route('manager.business.humanresource.index', $business) }}" class="btn btn-outline-secondary">
        Cancel
    </a>
</div>
