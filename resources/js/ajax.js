/**
 * Vanilla fetch wrapper that replaces jQuery $.ajax for appointment actions.
 * Preserves the existing session CSRF semantics and HTML fragment swap behaviour.
 */

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

/**
 * Perform an AJAX request using native fetch.
 * @param {Object} options
 * @param {string} options.url - Request URL
 * @param {string} [options.method='POST'] - HTTP method
 * @param {Object|string} [options.data] - Request body (form-encoded or JSON)
 * @param {string} [options.dataType='html'] - Expected response type: 'html', 'json'
 * @param {Object} [options.headers] - Additional headers
 * @returns {Promise<{ok: boolean, status: number, data: any}>}
 */
export async function ajax(options) {
    const method = (options.method || 'POST').toUpperCase();
    const headers = {
        'X-CSRF-TOKEN': getCsrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers || {}),
    };

    let body = null;

    if (options.data && method !== 'GET') {
        if (typeof options.data === 'string') {
            headers['Content-Type'] = 'application/x-www-form-urlencoded';
            body = options.data;
        } else {
            headers['Content-Type'] = 'application/x-www-form-urlencoded';
            body = new URLSearchParams(options.data).toString();
        }
    }

    let url = options.url;
    if (options.data && method === 'GET') {
        const params = new URLSearchParams(options.data).toString();
        url += (url.includes('?') ? '&' : '?') + params;
    }

    const response = await fetch(url, { method, headers, body, credentials: 'same-origin' });

    const dataType = options.dataType || 'html';
    let data;
    if (dataType === 'json') {
        data = await response.json();
    } else {
        data = await response.text();
    }

    return { ok: response.ok, status: response.status, data };
}

/**
 * Perform an appointment action (confirm, cancel, serve) and swap the HTML fragment.
 * @param {HTMLElement} button - The action button that was clicked
 * @param {string} postUrl - The endpoint URL
 * @param {Object} postData - The form data to send
 * @param {string} targetSelector - CSS selector of the element to replace with the response
 * @param {string} [errorMessage] - User-visible error message on failure
 */
export async function appointmentAction(button, postUrl, postData, targetSelector, errorMessage) {
    const originalContent = button.innerHTML;
    const originalDisabled = button.disabled;
    button.disabled = true;
    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

    try {
        const result = await ajax({ url: postUrl, method: 'POST', data: postData });

        if (result.ok) {
            const target = document.querySelector(targetSelector);
            if (target) {
                target.outerHTML = result.data;
            } else {
                document.location.reload();
            }
        } else {
            button.innerHTML = originalContent;
            button.disabled = originalDisabled;
            const msg = errorMessage || 'An error occurred. Please try again.';
            showFlashError(msg);
        }
    } catch (err) {
        button.innerHTML = originalContent;
        button.disabled = originalDisabled;
        const msg = errorMessage || 'Network error. Please check your connection.';
        showFlashError(msg);
        console.error('[timegrid] appointmentAction failed:', err.message);
    }
}

function showFlashError(message) {
    const existing = document.querySelector('.tg-flash-error');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show tg-flash-error';
    alert.setAttribute('role', 'alert');
    alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;

    const content = document.querySelector('.content') || document.querySelector('main') || document.body;
    content.prepend(alert);

    setTimeout(() => alert.remove(), 8000);
}

window.tgAjax = ajax;
window.tgAppointmentAction = appointmentAction;
