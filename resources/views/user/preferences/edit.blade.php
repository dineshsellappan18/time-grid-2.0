@extends('layouts.user')

@section('title', trans('user.preferences.title', ['default' => 'Settings']))

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <h2 class="mb-3" style="font-size:1.25rem;font-weight:600;">
                <i class="fa fa-cog text-muted"></i> Settings
            </h2>

            {{-- Tab Navigation --}}
            <div class="tg-settings-tabs">
                <ul class="tg-settings-tabs__nav" role="tablist">
                    <li><button class="tg-settings-tabs__btn is-active" data-tab="profile" role="tab" aria-selected="true">
                        <i class="fa fa-user"></i> Profile
                    </button></li>
                    <li><button class="tg-settings-tabs__btn" data-tab="notifications" role="tab" aria-selected="false">
                        <i class="fa fa-bell"></i> Notifications
                    </button></li>
                    <li><button class="tg-settings-tabs__btn" data-tab="preferences" role="tab" aria-selected="false">
                        <i class="fa fa-sliders"></i> Preferences
                    </button></li>
                    <li><button class="tg-settings-tabs__btn" data-tab="account" role="tab" aria-selected="false">
                        <i class="fa fa-shield"></i> Account
                    </button></li>
                    <li><button class="tg-settings-tabs__btn" data-tab="appearance" role="tab" aria-selected="false">
                        <i class="fa fa-paint-brush"></i> Appearance
                    </button></li>
                </ul>

                {{-- ============================================================ --}}
                {{-- TAB: Profile --}}
                {{-- ============================================================ --}}
                <div class="tg-settings-tabs__panel is-active" data-tab-panel="profile" role="tabpanel">
                    <form method="POST" action="{{ route('user.profile.update') }}" id="profile-form" class="tg-settings-form" novalidate enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="tg-settings-section">
                            <div class="tg-settings-section__header">
                                <h3 class="tg-settings-section__title">Personal Information</h3>
                                <p class="tg-settings-section__desc">Update your name, email, and avatar.</p>
                            </div>
                            <div class="tg-settings-section__body">
                                {{-- Avatar Upload --}}
                                <div class="tg-settings-row">
                                    <div class="tg-settings-row__info">
                                        <label class="tg-settings-row__label">Avatar</label>
                                        <span class="tg-settings-row__help">Click to upload a new photo. Max 2MB.</span>
                                    </div>
                                    <div class="tg-settings-row__control">
                                        <div class="tg-avatar-upload" id="avatarUpload">
                                            <div class="tg-avatar-upload__preview">
                                                <span class="tg-avatar-upload__initials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                            </div>
                                            <label for="avatarFile" class="btn btn-outline-secondary btn-sm">
                                                <i class="fa fa-camera"></i> Change
                                            </label>
                                            <input type="file" id="avatarFile" name="avatar" accept="image/*" class="d-none">
                                        </div>
                                    </div>
                                </div>

                                {{-- Name --}}
                                <div class="tg-settings-row">
                                    <div class="tg-settings-row__info">
                                        <label class="tg-settings-row__label" for="settings-name">Full Name</label>
                                    </div>
                                    <div class="tg-settings-row__control">
                                        <input type="text" id="settings-name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required minlength="3">
                                        <div class="invalid-feedback">Name must be at least 3 characters.</div>
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div class="tg-settings-row">
                                    <div class="tg-settings-row__info">
                                        <label class="tg-settings-row__label" for="settings-email">Email Address</label>
                                    </div>
                                    <div class="tg-settings-row__control">
                                        <input type="email" id="settings-email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                        <div class="invalid-feedback">Please enter a valid email.</div>
                                    </div>
                                </div>

                                {{-- Username --}}
                                <div class="tg-settings-row">
                                    <div class="tg-settings-row__info">
                                        <label class="tg-settings-row__label" for="settings-username">Username</label>
                                        <span class="tg-settings-row__help">Optional public display name.</span>
                                    </div>
                                    <div class="tg-settings-row__control">
                                        <input type="text" id="settings-username" name="username" class="form-control" value="{{ old('username', $user->username) }}" minlength="3" placeholder="Optional">
                                    </div>
                                </div>

                                {{-- Role --}}
                                <div class="tg-settings-row">
                                    <div class="tg-settings-row__info">
                                        <label class="tg-settings-row__label">Role</label>
                                    </div>
                                    <div class="tg-settings-row__control">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($user->businesses->isNotEmpty())
                                            <span class="tg-role-badge tg-role-badge--lead"><i class="fa fa-star"></i> Business Owner</span>
                                            @else
                                            <span class="tg-role-badge tg-role-badge--staff"><i class="fa fa-user"></i> Member</span>
                                            @endif
                                            <small class="text-muted">Role is assigned automatically.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tg-settings-section__actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i> Save Profile
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ============================================================ --}}
                {{-- TAB: Notifications --}}
                {{-- ============================================================ --}}
                <div class="tg-settings-tabs__panel" data-tab-panel="notifications" role="tabpanel">
                    <form method="POST" action="{{ route('user.preferences') }}" id="notifications-form" class="tg-settings-form">
                        {{ csrf_field() }}

                        <div class="tg-settings-section">
                            <div class="tg-settings-section__header">
                                <h3 class="tg-settings-section__title">Notification Preferences</h3>
                                <p class="tg-settings-section__desc">Choose which notifications you want to receive.</p>
                            </div>
                            <div class="tg-settings-section__body">
                                @foreach($notificationPrefs as $pref)
                                <div class="tg-settings-row">
                                    <div class="tg-settings-row__info">
                                        <label class="tg-settings-row__label" for="notif-{{ $pref['key'] }}">{{ $pref['label'] }}</label>
                                        <span class="tg-settings-row__help">{{ $pref['help'] }}</span>
                                    </div>
                                    <div class="tg-settings-row__control">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input tg-toggle" type="checkbox"
                                                   id="notif-{{ $pref['key'] }}" name="{{ $pref['key'] }}" value="1"
                                                   {{ $pref['value'] ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="tg-settings-section__actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i> Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ============================================================ --}}
                {{-- TAB: Preferences (General / App Settings) --}}
                {{-- ============================================================ --}}
                <div class="tg-settings-tabs__panel" data-tab-panel="preferences" role="tabpanel">
                    <form method="POST" action="{{ route('user.preferences') }}" id="settings-form" class="tg-settings-form">
                        {{ csrf_field() }}

                        <div class="tg-settings-section">
                            <div class="tg-settings-section__header">
                                <h3 class="tg-settings-section__title">App Preferences</h3>
                                <p class="tg-settings-section__desc">Regional and display preferences.</p>
                            </div>
                            <div class="tg-settings-section__body">
                                @foreach ($parameters as $key => $value)
                                    @if ($value['type'] == 'string')
                                    <div class="tg-settings-row">
                                        <div class="tg-settings-row__info">
                                            <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\User.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                            <span class="tg-settings-row__help">{{ trans('preferences.App\Models\User.'.$key.'.help', ['default' => '']) }}</span>
                                        </div>
                                        <div class="tg-settings-row__control">
                                            <input type="text" id="pref-{{ $key }}" name="{{ $key }}" class="form-control" value="{{ $user->pref($key) }}" placeholder="{{ trans('preferences.App\Models\User.'.$key.'.format', ['default' => '']) }}">
                                        </div>
                                    </div>
                                    @elseif ($value['type'] == 'bool')
                                    <div class="tg-settings-row">
                                        <div class="tg-settings-row__info">
                                            <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\User.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                            <span class="tg-settings-row__help">{{ trans('preferences.App\Models\User.'.$key.'.help', ['default' => '']) }}</span>
                                        </div>
                                        <div class="tg-settings-row__control">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input tg-toggle" type="checkbox" id="pref-{{ $key }}" name="{{ $key }}" value="1" {{ $user->pref($key) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    @elseif ($value['type'] == 'int')
                                    <div class="tg-settings-row">
                                        <div class="tg-settings-row__info">
                                            <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\User.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                            <span class="tg-settings-row__help">{{ trans('preferences.App\Models\User.'.$key.'.help', ['default' => '']) }}</span>
                                        </div>
                                        <div class="tg-settings-row__control">
                                            <input type="number" id="pref-{{ $key }}" name="{{ $key }}" class="form-control" value="{{ $user->pref($key) }}" step="{{ $value['step'] ?? 1 }}">
                                        </div>
                                    </div>
                                    @elseif ($value['type'] == 'time')
                                    <div class="tg-settings-row">
                                        <div class="tg-settings-row__info">
                                            <label class="tg-settings-row__label" for="pref-{{ $key }}">{{ trans('preferences.App\Models\User.'.$key.'.label', ['default' => ucwords(str_replace('_', ' ', $key))]) }}</label>
                                            <span class="tg-settings-row__help">{{ trans('preferences.App\Models\User.'.$key.'.help', ['default' => '']) }}</span>
                                        </div>
                                        <div class="tg-settings-row__control">
                                            <input type="time" id="pref-{{ $key }}" name="{{ $key }}" class="form-control" value="{{ $user->pref($key) }}">
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="tg-settings-section__actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i> Save Preferences
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ============================================================ --}}
                {{-- TAB: Account --}}
                {{-- ============================================================ --}}
                <div class="tg-settings-tabs__panel" data-tab-panel="account" role="tabpanel">

                    {{-- Password Change --}}
                    <form method="POST" action="{{ route('user.profile.update') }}" id="password-form" class="tg-settings-form" novalidate>
                        {{ csrf_field() }}
                        <input type="hidden" name="name" value="{{ $user->name }}">
                        <input type="hidden" name="email" value="{{ $user->email }}">

                        <div class="tg-settings-section">
                            <div class="tg-settings-section__header">
                                <h3 class="tg-settings-section__title">Change Password</h3>
                                <p class="tg-settings-section__desc">Update your account password.</p>
                            </div>
                            <div class="tg-settings-section__body">
                                <div class="tg-settings-row">
                                    <div class="tg-settings-row__info">
                                        <label class="tg-settings-row__label" for="new-password">New Password</label>
                                        <span class="tg-settings-row__help">At least 6 characters.</span>
                                    </div>
                                    <div class="tg-settings-row__control">
                                        <input type="password" id="new-password" name="password" class="form-control" minlength="6" required placeholder="Enter new password">
                                        <div class="invalid-feedback">Password must be at least 6 characters.</div>
                                    </div>
                                </div>
                                <div class="tg-settings-row">
                                    <div class="tg-settings-row__info">
                                        <label class="tg-settings-row__label" for="confirm-password">Confirm Password</label>
                                    </div>
                                    <div class="tg-settings-row__control">
                                        <input type="password" id="confirm-password" name="password_confirmation" class="form-control" minlength="6" required placeholder="Repeat new password">
                                        <div class="invalid-feedback">Passwords must match.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tg-settings-section__actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-lock"></i> Update Password
                            </button>
                        </div>
                    </form>

                    {{-- Account Deletion --}}
                    <div class="tg-settings-section tg-settings-section--danger mt-4">
                        <div class="tg-settings-section__header">
                            <h3 class="tg-settings-section__title text-danger"><i class="fa fa-exclamation-triangle"></i> Danger Zone</h3>
                            <p class="tg-settings-section__desc">Permanently delete your account and all associated data. This action cannot be undone.</p>
                        </div>
                        <div class="tg-settings-section__body">
                            <button type="button" class="btn btn-outline-danger" id="showDeleteAccount">
                                <i class="fa fa-trash"></i> Delete My Account
                            </button>

                            <div class="tg-settings-delete-confirm mt-3" id="deleteConfirmation" style="display:none;">
                                <div class="alert alert-danger">
                                    <strong>Are you sure?</strong> This will permanently delete your account, all your businesses, appointments, and associated data.
                                </div>
                                <form method="POST" action="{{ route('user.account.destroy') }}" id="deleteAccountForm" novalidate>
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                    <div class="mb-3">
                                        <label for="confirm-email" class="form-label fw-semibold">Type your email to confirm: <code>{{ $user->email }}</code></label>
                                        <input type="email" id="confirm-email" name="confirm_email" class="form-control" required placeholder="Enter your email address">
                                        <div class="invalid-feedback">Email must match your account email.</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fa fa-trash"></i> Permanently Delete Account
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="cancelDelete">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- TAB: Appearance --}}
                {{-- ============================================================ --}}
                <div class="tg-settings-tabs__panel" data-tab-panel="appearance" role="tabpanel">
                    <div class="tg-settings-section">
                        <div class="tg-settings-section__header">
                            <h3 class="tg-settings-section__title">Theme & Appearance</h3>
                            <p class="tg-settings-section__desc">Customize the look and feel of the application.</p>
                        </div>
                        <div class="tg-settings-section__body">
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label">Color Mode</label>
                                    <span class="tg-settings-row__help">Switch between light and dark themes.</span>
                                </div>
                                <div class="tg-settings-row__control">
                                    <div class="tg-theme-toggle" id="themeToggle">
                                        <button type="button" class="tg-theme-toggle__btn is-active" data-theme="light">
                                            <i class="fa fa-sun-o"></i> Light
                                        </button>
                                        <button type="button" class="tg-theme-toggle__btn" data-theme="dark">
                                            <i class="fa fa-moon-o"></i> Dark
                                        </button>
                                        <button type="button" class="tg-theme-toggle__btn" data-theme="auto">
                                            <i class="fa fa-desktop"></i> System
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Preview card --}}
                            <div class="tg-settings-row">
                                <div class="tg-settings-row__info">
                                    <label class="tg-settings-row__label">Preview</label>
                                </div>
                                <div class="tg-settings-row__control">
                                    <div class="tg-theme-preview" id="themePreview">
                                        <div class="tg-theme-preview__header"></div>
                                        <div class="tg-theme-preview__sidebar"></div>
                                        <div class="tg-theme-preview__content">
                                            <div class="tg-theme-preview__line"></div>
                                            <div class="tg-theme-preview__line tg-theme-preview__line--short"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer_scripts')
@vite(['resources/js/settings.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    var tabBtns = document.querySelectorAll('.tg-settings-tabs__btn');
    var tabPanels = document.querySelectorAll('.tg-settings-tabs__panel');

    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target = btn.getAttribute('data-tab');
            tabBtns.forEach(function(b) { b.classList.remove('is-active'); b.setAttribute('aria-selected', 'false'); });
            tabPanels.forEach(function(p) { p.classList.remove('is-active'); });
            btn.classList.add('is-active');
            btn.setAttribute('aria-selected', 'true');
            var panel = document.querySelector('[data-tab-panel="' + target + '"]');
            if (panel) panel.classList.add('is-active');
        });
    });

    // Avatar preview
    var avatarInput = document.getElementById('avatarFile');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            var file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.querySelector('.tg-avatar-upload__preview');
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Account deletion toggle
    var showDeleteBtn = document.getElementById('showDeleteAccount');
    var deleteConfirm = document.getElementById('deleteConfirmation');
    var cancelDeleteBtn = document.getElementById('cancelDelete');

    if (showDeleteBtn && deleteConfirm) {
        showDeleteBtn.addEventListener('click', function() {
            deleteConfirm.style.display = '';
            showDeleteBtn.style.display = 'none';
        });
    }
    if (cancelDeleteBtn && deleteConfirm && showDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            deleteConfirm.style.display = 'none';
            showDeleteBtn.style.display = '';
        });
    }

    // Delete form validation
    var deleteForm = document.getElementById('deleteAccountForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            if (!deleteForm.checkValidity()) {
                e.preventDefault();
                deleteForm.classList.add('was-validated');
            }
        });
    }

    // Theme toggle
    var themeBtns = document.querySelectorAll('#themeToggle .tg-theme-toggle__btn');
    var savedTheme = localStorage.getItem('tg-theme') || 'light';

    function applyTheme(theme) {
        themeBtns.forEach(function(b) { b.classList.remove('is-active'); });
        var activeBtn = document.querySelector('#themeToggle [data-theme="' + theme + '"]');
        if (activeBtn) activeBtn.classList.add('is-active');

        var resolved = theme;
        if (theme === 'auto') {
            resolved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-bs-theme', resolved);
        localStorage.setItem('tg-theme', theme);

        var preview = document.getElementById('themePreview');
        if (preview) {
            preview.classList.toggle('tg-theme-preview--dark', resolved === 'dark');
        }
    }

    applyTheme(savedTheme);

    themeBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            applyTheme(btn.getAttribute('data-theme'));
        });
    });

    // Form validation
    var forms = document.querySelectorAll('.tg-settings-form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
</script>
@endpush
