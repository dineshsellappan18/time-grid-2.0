/**
 * Form helpers — vanilla replacements for legacy jQuery form plugins.
 * Views still call .select2() / .slugify(); provide light shims so pages do not throw.
 */
import getSlug from 'speakingurl';

function enhanceSelect(select) {
    if (!(select instanceof HTMLSelectElement)) return;
    select.classList.add('form-select');
    select.classList.remove('form-control');
}

HTMLElement.prototype.select2 = function select2Shim() {
    if (this instanceof HTMLSelectElement) {
        enhanceSelect(this);
        return this;
    }
    if (typeof this.querySelectorAll === 'function') {
        this.querySelectorAll('select.select2, select').forEach(enhanceSelect);
    }
    return this;
};

NodeList.prototype.select2 = function select2ListShim(options) {
    this.forEach(function (el) {
        if (typeof el.select2 === 'function') {
            el.select2(options);
        }
    });
    return this;
};

HTMLElement.prototype.slugify = function slugify(sourceSelector) {
    const target = this;
    const source = document.querySelector(sourceSelector);
    if (!source || !target) return this;

    const sync = function () {
        target.value = getSlug(source.value || '');
    };

    source.addEventListener('input', sync);
    source.addEventListener('change', sync);
    return this;
};

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select.select2').forEach(enhanceSelect);

    document.querySelectorAll('[data-toggle="switch"], .bootstrap-switch').forEach(function (el) {
        el.classList.add('form-check-input');
    });
});
