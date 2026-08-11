@extends('layouts.user')

@section('title', trans('user.profile.title', ['default' => 'My Profile']))

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        {{-- Profile Card --}}
        <div class="col-lg-4">
            <div class="tg-profile-card">
                <div class="tg-profile-card__header">
                    <div class="tg-avatar tg-avatar--xl" data-initials="{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(str_contains($user->name, ' ') ? explode(' ', $user->name)[1] : '', 0, 1)) }}">
                        @if(isset($gravatarURL) && $gravatarURL)
                            <img src="{{ $gravatarURL }}" alt="{{ $user->name }}" class="tg-avatar__img"
                                 onerror="this.style.display='none'; this.parentElement.classList.add('tg-avatar--fallback');">
                        @else
                            <span class="tg-avatar__initials">{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(str_contains($user->name, ' ') ? explode(' ', $user->name)[1] : '', 0, 1)) }}</span>
                        @endif
                    </div>
                    <h2 class="tg-profile-card__name">{{ $user->name }}</h2>
                    <p class="tg-profile-card__email">{{ $user->email }}</p>
                </div>
                <div class="tg-profile-card__body">
                    <div class="tg-profile-meta">
                        <div class="tg-profile-meta__row">
                            <span class="tg-profile-meta__label"><i class="fa fa-user"></i> Username</span>
                            <span class="tg-profile-meta__value">{{ $user->username ?: '—' }}</span>
                        </div>
                        <div class="tg-profile-meta__row">
                            <span class="tg-profile-meta__label"><i class="fa fa-building-o"></i> Businesses</span>
                            <span class="tg-profile-meta__value">{{ $user->businesses->count() }}</span>
                        </div>
                        <div class="tg-profile-meta__row">
                            <span class="tg-profile-meta__label"><i class="fa fa-calendar"></i> Appointments</span>
                            <span class="tg-profile-meta__value">{{ $user->appointments()->count() }}</span>
                        </div>
                        @if($user->last_login_at)
                        <div class="tg-profile-meta__row">
                            <span class="tg-profile-meta__label"><i class="fa fa-clock-o"></i> Last Login</span>
                            <span class="tg-profile-meta__value">{{ $user->last_login_at->diffForHumans() }}</span>
                        </div>
                        @endif
                        <div class="tg-profile-meta__row">
                            <span class="tg-profile-meta__label"><i class="fa fa-calendar-plus-o"></i> Member Since</span>
                            <span class="tg-profile-meta__value">{{ $user->created_at?->format('M d, Y') ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="tg-profile-card__footer">
                    <a href="{{ route('user.preferences') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fa fa-cog"></i> {{ trans('user.profile.preferences_link', ['default' => 'App Preferences']) }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Edit Profile Form --}}
        <div class="col-lg-8">
            <div class="tg-dash-panel" id="profile-panel">
                <div class="tg-dash-panel__header">
                    <h2 class="tg-dash-panel__title">
                        <i class="fa fa-pencil"></i>
                        <span id="panel-title-read">{{ trans('user.profile.info_title', ['default' => 'Personal Information']) }}</span>
                        <span id="panel-title-edit" style="display:none;">{{ trans('user.profile.edit_title', ['default' => 'Edit Profile']) }}</span>
                    </h2>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="edit-toggle">
                        <i class="fa fa-pencil"></i> {{ trans('user.profile.btn_edit', ['default' => 'Edit']) }}
                    </button>
                </div>

                {{-- Read Mode --}}
                <div class="tg-dash-panel__body" id="profile-read">
                    <div class="tg-profile-fields">
                        <div class="tg-profile-field">
                            <label class="tg-profile-field__label">{{ trans('user.profile.field.name', ['default' => 'Full Name']) }}</label>
                            <div class="tg-profile-field__value">{{ $user->name }}</div>
                        </div>
                        <div class="tg-profile-field">
                            <label class="tg-profile-field__label">{{ trans('user.profile.field.email', ['default' => 'Email Address']) }}</label>
                            <div class="tg-profile-field__value">{{ $user->email }}</div>
                        </div>
                        <div class="tg-profile-field">
                            <label class="tg-profile-field__label">{{ trans('user.profile.field.username', ['default' => 'Username']) }}</label>
                            <div class="tg-profile-field__value">{{ $user->username ?: '—' }}</div>
                        </div>
                        <div class="tg-profile-field">
                            <label class="tg-profile-field__label">{{ trans('user.profile.field.password', ['default' => 'Password']) }}</label>
                            <div class="tg-profile-field__value">••••••••</div>
                        </div>
                    </div>
                </div>

                {{-- Edit Mode --}}
                <div class="tg-dash-panel__body" id="profile-edit" style="display:none;">
                    @if (count($errors) > 0)
                    <div class="tg-auth-alert tg-auth-alert--danger mb-3" role="alert">
                        <i class="fa fa-exclamation-circle"></i>
                        <div>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('user.profile.update') }}" class="tg-auth-form" id="profile-form" novalidate>
                        {{ csrf_field() }}

                        <div class="tg-profile-fields">
                            <div class="tg-profile-field">
                                <label class="form-label" for="profile-name">{{ trans('user.profile.field.name', ['default' => 'Full Name']) }}</label>
                                <div class="tg-input-wrapper">
                                    <input id="profile-name" type="text" name="name"
                                           class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                           value="{{ old('name', $user->name) }}"
                                           placeholder="Your full name"
                                           minlength="3" required
                                           aria-describedby="name-feedback">
                                    <i class="fa fa-user tg-input-icon"></i>
                                </div>
                                @if ($errors->has('name'))
                                    <div class="invalid-feedback d-block" id="name-feedback">{{ $errors->first('name') }}</div>
                                @else
                                    <div class="invalid-feedback" id="name-feedback">Name must be at least 3 characters.</div>
                                @endif
                            </div>

                            <div class="tg-profile-field">
                                <label class="form-label" for="profile-email">{{ trans('user.profile.field.email', ['default' => 'Email Address']) }}</label>
                                <div class="tg-input-wrapper">
                                    <input id="profile-email" type="email" name="email"
                                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                           value="{{ old('email', $user->email) }}"
                                           placeholder="you@example.com"
                                           required
                                           aria-describedby="email-feedback">
                                    <i class="fa fa-envelope tg-input-icon"></i>
                                </div>
                                @if ($errors->has('email'))
                                    <div class="invalid-feedback d-block" id="email-feedback">{{ $errors->first('email') }}</div>
                                @else
                                    <div class="invalid-feedback" id="email-feedback">Please enter a valid email address.</div>
                                @endif
                            </div>

                            <div class="tg-profile-field">
                                <label class="form-label" for="profile-username">{{ trans('user.profile.field.username', ['default' => 'Username']) }}</label>
                                <div class="tg-input-wrapper">
                                    <input id="profile-username" type="text" name="username"
                                           class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}"
                                           value="{{ old('username', $user->username) }}"
                                           placeholder="Optional username"
                                           minlength="3"
                                           aria-describedby="username-feedback">
                                    <i class="fa fa-at tg-input-icon"></i>
                                </div>
                                @if ($errors->has('username'))
                                    <div class="invalid-feedback d-block" id="username-feedback">{{ $errors->first('username') }}</div>
                                @else
                                    <div class="invalid-feedback" id="username-feedback">Username must be at least 3 characters.</div>
                                @endif
                            </div>

                            <div class="tg-profile-field">
                                <label class="form-label" for="profile-password">{{ trans('user.profile.field.new_password', ['default' => 'New Password']) }}</label>
                                <div class="tg-input-wrapper">
                                    <input id="profile-password" type="password" name="password"
                                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                           placeholder="Leave blank to keep current"
                                           minlength="6"
                                           aria-describedby="password-feedback">
                                    <i class="fa fa-lock tg-input-icon"></i>
                                    <button type="button" class="tg-password-toggle" aria-label="Toggle password visibility">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                @if ($errors->has('password'))
                                    <div class="invalid-feedback d-block" id="password-feedback">{{ $errors->first('password') }}</div>
                                @else
                                    <div class="invalid-feedback" id="password-feedback">Password must be at least 6 characters.</div>
                                @endif
                            </div>

                            <div class="tg-profile-field" id="password-confirm-group" style="display:none;">
                                <label class="form-label" for="profile-password-confirm">{{ trans('user.profile.field.confirm_password', ['default' => 'Confirm Password']) }}</label>
                                <div class="tg-input-wrapper">
                                    <input id="profile-password-confirm" type="password" name="password_confirmation"
                                           class="form-control"
                                           placeholder="Repeat new password"
                                           minlength="6"
                                           aria-describedby="password-confirm-feedback">
                                    <i class="fa fa-lock tg-input-icon"></i>
                                </div>
                                <div class="invalid-feedback" id="password-confirm-feedback">Passwords do not match.</div>
                            </div>
                        </div>

                        <div class="tg-profile-actions">
                            <button type="submit" class="btn btn-primary tg-auth-submit" id="profile-save">
                                <span class="tg-auth-submit__label"><i class="fa fa-check"></i> {{ trans('user.profile.btn_save', ['default' => 'Save Changes']) }}</span>
                                <span class="tg-auth-submit__spinner" aria-hidden="true">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                    Saving&hellip;
                                </span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="edit-cancel">
                                {{ trans('user.profile.btn_cancel', ['default' => 'Cancel']) }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/auth-validation.js'])
<script>
document.addEventListener('DOMContentLoaded', function () {
    var readPanel = document.getElementById('profile-read');
    var editPanel = document.getElementById('profile-edit');
    var editToggle = document.getElementById('edit-toggle');
    var editCancel = document.getElementById('edit-cancel');
    var titleRead = document.getElementById('panel-title-read');
    var titleEdit = document.getElementById('panel-title-edit');
    var passwordField = document.getElementById('profile-password');
    var confirmGroup = document.getElementById('password-confirm-group');

    function showEdit() {
        readPanel.style.display = 'none';
        editPanel.style.display = '';
        editToggle.style.display = 'none';
        titleRead.style.display = 'none';
        titleEdit.style.display = '';
    }

    function showRead() {
        readPanel.style.display = '';
        editPanel.style.display = 'none';
        editToggle.style.display = '';
        titleRead.style.display = '';
        titleEdit.style.display = 'none';
    }

    editToggle.addEventListener('click', showEdit);
    editCancel.addEventListener('click', showRead);

    if (passwordField) {
        passwordField.addEventListener('input', function () {
            confirmGroup.style.display = this.value.length > 0 ? '' : 'none';
        });
    }

    @if(count($errors) > 0)
        showEdit();
    @endif
});
</script>
@endpush
