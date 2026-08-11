/**
 * Detail View: client-side tab switching, tooltips, and image error handling.
 * Vanilla ES module — no jQuery dependency.
 */

(function () {
    'use strict';

    function initTabs() {
        var tabContainers = document.querySelectorAll('.tg-detail-tabs');

        tabContainers.forEach(function (container) {
            var buttons = container.querySelectorAll('.tg-detail-tabs__btn');
            var panels = container.querySelectorAll('.tg-detail-tabs__panel');

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var tabId = this.getAttribute('data-tab');

                    buttons.forEach(function (b) {
                        b.classList.remove('is-active');
                        b.setAttribute('aria-selected', 'false');
                    });
                    this.classList.add('is-active');
                    this.setAttribute('aria-selected', 'true');

                    panels.forEach(function (panel) {
                        var panelId = panel.getAttribute('data-tab-panel');
                        if (panelId === tabId) {
                            panel.style.display = '';
                            panel.classList.add('is-active');
                        } else {
                            panel.style.display = 'none';
                            panel.classList.remove('is-active');
                        }
                    });
                });
            });
        });
    }

    function initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        }
    }

    function initImageFallbacks() {
        document.querySelectorAll('.tg-detail-header__img').forEach(function (img) {
            img.addEventListener('error', function () {
                this.style.display = 'none';
                var parent = this.closest('.tg-detail-header__avatar');
                if (parent && !parent.querySelector('.tg-detail-header__initials')) {
                    var fallback = document.createElement('div');
                    fallback.className = 'tg-detail-header__initials';
                    fallback.textContent = '?';
                    parent.appendChild(fallback);
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTabs();
        initTooltips();
        initImageFallbacks();
    });
})();
