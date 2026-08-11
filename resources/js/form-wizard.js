/**
 * Form Wizard: multi-step navigation, per-field validation,
 * file upload with drag-and-drop, loading states, step persistence.
 * Vanilla ES module — no jQuery dependency.
 */

(function () {
    'use strict';

    // ── Validation helpers ─────────────────────────────────────────

    function isValidEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    }

    function setFieldError(input, show) {
        var group = input.closest('.tg-form-group');
        var feedback = group ? group.querySelector('.tg-form-feedback') : null;
        if (show) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            if (feedback) feedback.style.display = '';
        } else {
            input.classList.remove('is-invalid');
            if (input.value.trim()) input.classList.add('is-valid');
            if (feedback) feedback.style.display = 'none';
        }
    }

    function validateField(input) {
        var val = input.value.trim();
        var required = input.hasAttribute('required');
        var type = input.getAttribute('type') || input.tagName.toLowerCase();
        var minLen = parseInt(input.getAttribute('minlength'), 10) || 0;

        if (required && !val) { setFieldError(input, true); return false; }
        if (type === 'email' && val && !isValidEmail(val)) { setFieldError(input, true); return false; }
        if (minLen > 0 && val.length < minLen) { setFieldError(input, true); return false; }
        if (type === 'number' && val) {
            var num = parseFloat(val);
            var min = parseFloat(input.getAttribute('min'));
            var max = parseFloat(input.getAttribute('max'));
            if (isNaN(num)) { setFieldError(input, true); return false; }
            if (!isNaN(min) && num < min) { setFieldError(input, true); return false; }
            if (!isNaN(max) && num > max) { setFieldError(input, true); return false; }
        }
        setFieldError(input, false);
        return true;
    }

    function validateStep(stepEl) {
        var inputs = stepEl.querySelectorAll('input:not([type="hidden"]):not([type="file"]), select, textarea');
        var allValid = true;
        inputs.forEach(function (input) {
            if (!validateField(input)) allValid = false;
        });
        return allValid;
    }

    // ── Step Wizard ────────────────────────────────────────────────

    function initWizard(wizard) {
        var steps = wizard.querySelectorAll('.tg-form-step');
        var indicators = wizard.querySelectorAll('.tg-step-indicator__step');
        var prevBtn = wizard.querySelector('.tg-form-wizard__prev');
        var nextBtn = wizard.querySelector('.tg-form-wizard__next');
        var submitBtn = wizard.querySelector('.tg-form-wizard__submit');
        var currentStep = 0;
        var totalSteps = steps.length;

        function showStep(idx) {
            steps.forEach(function (s, i) {
                s.style.display = i === idx ? '' : 'none';
                s.classList.toggle('is-active', i === idx);
            });
            indicators.forEach(function (ind, i) {
                ind.classList.remove('is-active', 'is-completed');
                if (i < idx) ind.classList.add('is-completed');
                if (i === idx) ind.classList.add('is-active');
            });

            if (prevBtn) prevBtn.style.display = idx > 0 ? '' : 'none';
            if (nextBtn) nextBtn.style.display = idx < totalSteps - 1 ? '' : 'none';
            if (submitBtn) submitBtn.style.display = idx === totalSteps - 1 ? '' : 'none';

            currentStep = idx;
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (currentStep > 0) showStep(currentStep - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (validateStep(steps[currentStep])) {
                    if (currentStep < totalSteps - 1) showStep(currentStep + 1);
                } else {
                    var firstInvalid = steps[currentStep].querySelector('.is-invalid');
                    if (firstInvalid) firstInvalid.focus();
                }
            });
        }

        indicators.forEach(function (ind, idx) {
            ind.addEventListener('click', function () {
                if (idx < currentStep) {
                    showStep(idx);
                } else if (idx > currentStep) {
                    var valid = true;
                    for (var i = currentStep; i < idx; i++) {
                        if (!validateStep(steps[i])) { valid = false; break; }
                    }
                    if (valid) showStep(idx);
                }
            });
        });

        showStep(0);
    }

    // ── Inline Validation Binding ──────────────────────────────────

    function initValidation(form) {
        var inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="file"]), select, textarea');
        inputs.forEach(function (input) {
            input.addEventListener('blur', function () {
                if (this.value.trim()) validateField(this);
            });
            input.addEventListener('input', function () {
                if (this.classList.contains('is-invalid')) validateField(this);
            });
        });

        form.addEventListener('submit', function (e) {
            var activeStep = form.querySelector('.tg-form-step.is-active');
            var target = activeStep || form;
            if (!validateStep(target)) {
                e.preventDefault();
                var firstInvalid = target.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
                return;
            }

            var btn = form.querySelector('.tg-form-wizard__submit');
            if (btn) {
                btn.classList.add('is-loading');
                btn.setAttribute('disabled', 'disabled');
            }
        });
    }

    // ── File Upload ────────────────────────────────────────────────

    function initFileUpload(zone) {
        var input = zone.querySelector('.tg-file-upload__input');
        var preview = zone.querySelector('.tg-file-upload__preview');
        var label = zone.querySelector('.tg-file-upload__label');

        if (!input) return;

        ['dragenter', 'dragover'].forEach(function (evt) {
            zone.addEventListener(evt, function (e) {
                e.preventDefault();
                zone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (evt) {
            zone.addEventListener(evt, function (e) {
                e.preventDefault();
                zone.classList.remove('is-dragover');
            });
        });

        zone.addEventListener('drop', function (e) {
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showPreview(input.files, preview, label);
            }
        });

        zone.addEventListener('click', function () {
            input.click();
        });

        input.addEventListener('change', function () {
            showPreview(this.files, preview, label);
        });
    }

    function showPreview(files, previewEl, labelEl) {
        if (!previewEl) return;
        previewEl.innerHTML = '';

        if (!files || files.length === 0) {
            previewEl.style.display = 'none';
            if (labelEl) labelEl.style.display = '';
            return;
        }

        previewEl.style.display = '';
        if (labelEl) labelEl.style.display = 'none';

        Array.prototype.forEach.call(files, function (file) {
            var item = document.createElement('div');
            item.className = 'tg-file-upload__item';

            if (file.type.startsWith('image/')) {
                var img = document.createElement('img');
                img.className = 'tg-file-upload__thumb';
                var reader = new FileReader();
                reader.onload = function (e) { img.src = e.target.result; };
                reader.readAsDataURL(file);
                item.appendChild(img);
            } else {
                var icon = document.createElement('i');
                icon.className = 'fa fa-file-o tg-file-upload__file-icon';
                item.appendChild(icon);
            }

            var name = document.createElement('span');
            name.className = 'tg-file-upload__name';
            name.textContent = file.name;
            item.appendChild(name);

            var size = document.createElement('span');
            size.className = 'tg-file-upload__size';
            size.textContent = formatBytes(file.size);
            item.appendChild(size);

            previewEl.appendChild(item);
        });
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // ── Init ───────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.tg-form-wizard').forEach(function (wizard) {
            initWizard(wizard);
            initValidation(wizard);
        });

        document.querySelectorAll('.tg-form-modern').forEach(function (form) {
            initValidation(form);
        });

        document.querySelectorAll('.tg-file-upload').forEach(initFileUpload);
    });
})();
