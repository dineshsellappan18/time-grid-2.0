/**
 * Settings page: dirty-state tracking, save/discard bar, navigation guard.
 * Vanilla ES module — no jQuery dependency.
 */

(function () {
    'use strict';

    var initialState = {};
    var isDirty = false;

    function captureState(form) {
        var state = {};
        var inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function (el) {
            if (!el.name || el.type === 'hidden') return;
            if (el.type === 'checkbox') {
                state[el.name] = el.checked;
            } else {
                state[el.name] = el.value;
            }
        });
        return state;
    }

    function hasChanges(form) {
        var current = captureState(form);
        var keys = Object.keys(initialState);
        for (var i = 0; i < keys.length; i++) {
            if (current[keys[i]] !== initialState[keys[i]]) return true;
        }
        var newKeys = Object.keys(current);
        for (var j = 0; j < newKeys.length; j++) {
            if (!(newKeys[j] in initialState)) return true;
        }
        return false;
    }

    function showSaveBar() {
        var bar = document.getElementById('settings-savebar');
        if (bar) bar.classList.add('is-visible');
    }

    function hideSaveBar() {
        var bar = document.getElementById('settings-savebar');
        if (bar) bar.classList.remove('is-visible');
    }

    function checkDirty(form) {
        isDirty = hasChanges(form);
        if (isDirty) {
            showSaveBar();
        } else {
            hideSaveBar();
        }
    }

    function initForm(form) {
        initialState = captureState(form);

        var inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function (el) {
            el.addEventListener('input', function () { checkDirty(form); });
            el.addEventListener('change', function () { checkDirty(form); });
        });

        var discardBtn = document.getElementById('settings-discard');
        if (discardBtn) {
            discardBtn.addEventListener('click', function () {
                restoreState(form);
                checkDirty(form);
            });
        }

        form.addEventListener('submit', function () {
            isDirty = false;
            var btn = form.querySelector('.tg-auth-submit');
            if (btn) {
                btn.classList.add('is-loading');
                btn.setAttribute('disabled', 'disabled');
            }
        });
    }

    function restoreState(form) {
        var inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function (el) {
            if (!el.name || el.type === 'hidden') return;
            if (!(el.name in initialState)) return;

            if (el.type === 'checkbox') {
                el.checked = initialState[el.name];
            } else {
                el.value = initialState[el.name];
            }

            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    function initNavigationGuard() {
        window.addEventListener('beforeunload', function (e) {
            if (isDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }

    function initToggleStyling() {
        document.querySelectorAll('.tg-toggle').forEach(function (toggle) {
            var row = toggle.closest('.tg-settings-row');
            if (!row) return;

            function updateState() {
                if (toggle.checked) {
                    row.classList.add('is-active');
                } else {
                    row.classList.remove('is-active');
                }
            }

            toggle.addEventListener('change', updateState);
            updateState();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('settings-form');
        if (form) {
            initForm(form);
            initNavigationGuard();
        }
        initToggleStyling();
    });
})();
