import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

// Method link handler — creates forms for DELETE/PUT/PATCH links (replaces jQuery pattern)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[data-method]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var method = this.dataset.method || 'POST';
            var confirmMsg = this.dataset.confirm;
            if (confirmMsg && !confirm(confirmMsg)) return;

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = this.getAttribute('href');
            form.style.display = 'none';

            var tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            var meta = document.querySelector('meta[name="csrf-token"]');
            tokenInput.value = meta ? meta.getAttribute('content') : '';
            form.appendChild(tokenInput);

            var methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = method;
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        });
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});

