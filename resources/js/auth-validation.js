/**
 * Auth form client-side validation, loading states, and password toggle.
 * Vanilla ES module — no jQuery dependency.
 */

(function () {
    'use strict';

    function isValidEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function setFieldState(input, valid) {
        var wrapper = input.closest('.tg-input-wrapper');
        var feedback = input.getAttribute('aria-describedby');
        var feedbackEl = feedback ? document.getElementById(feedback) : null;

        if (valid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            if (wrapper) wrapper.classList.remove('has-error');
            if (feedbackEl) feedbackEl.classList.remove('d-block');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            if (wrapper) wrapper.classList.add('has-error');
            if (feedbackEl) feedbackEl.classList.add('d-block');
        }
    }

    function clearFieldState(input) {
        var wrapper = input.closest('.tg-input-wrapper');
        var feedback = input.getAttribute('aria-describedby');
        var feedbackEl = feedback ? document.getElementById(feedback) : null;

        input.classList.remove('is-invalid', 'is-valid');
        if (wrapper) wrapper.classList.remove('has-error');
        if (feedbackEl) feedbackEl.classList.remove('d-block');
    }

    function validateField(input) {
        var value = input.value.trim();
        var type = input.getAttribute('type') || 'text';
        var minLen = parseInt(input.getAttribute('minlength'), 10) || 0;
        var required = input.hasAttribute('required');

        if (required && !value) {
            setFieldState(input, false);
            return false;
        }

        if (type === 'email' && value && !isValidEmail(value)) {
            setFieldState(input, false);
            return false;
        }

        if (minLen > 0 && value.length < minLen) {
            setFieldState(input, false);
            return false;
        }

        if (input.name === 'password_confirmation') {
            var form = input.closest('form');
            var passwordInput = form ? form.querySelector('input[name="password"]') : null;
            if (passwordInput && value !== passwordInput.value) {
                setFieldState(input, false);
                return false;
            }
        }

        if (value) {
            setFieldState(input, true);
        }
        return true;
    }

    function initForm(form) {
        if (!form) return;

        var inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"])');

        inputs.forEach(function (input) {
            input.addEventListener('blur', function () {
                if (this.value.trim()) {
                    validateField(this);
                }
            });

            input.addEventListener('input', function () {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });

        form.addEventListener('submit', function (e) {
            var allValid = true;

            inputs.forEach(function (input) {
                if (!validateField(input)) {
                    allValid = false;
                }
            });

            if (!allValid) {
                e.preventDefault();
                var firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
                return;
            }

            var btn = form.querySelector('.tg-auth-submit');
            if (btn) {
                btn.classList.add('is-loading');
                btn.setAttribute('disabled', 'disabled');
            }
        });
    }

    function initPasswordToggles() {
        document.querySelectorAll('.tg-password-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var wrapper = this.closest('.tg-input-wrapper');
                var input = wrapper ? wrapper.querySelector('input') : null;
                if (!input) return;

                var icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
                input.focus();
            });
        });
    }

    function initAlertDismiss() {
        document.querySelectorAll('.tg-auth-alert').forEach(function (alert) {
            alert.addEventListener('click', function () {
                this.style.opacity = '0';
                this.style.transform = 'translateY(-8px)';
                var el = this;
                setTimeout(function () { el.style.display = 'none'; }, 200);
            });
            alert.style.cursor = 'pointer';
            alert.setAttribute('title', 'Click to dismiss');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('.tg-auth-form');
        forms.forEach(initForm);
        initPasswordToggles();
        initAlertDismiss();
    });
})();
