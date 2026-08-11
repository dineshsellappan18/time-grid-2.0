/**
 * Search island — vanilla replacement for typeahead/bloodhound.
 * Filters table/list rows client-side and optionally calls a search endpoint.
 */

export function initSearchIsland(inputSelector, listSelector, options = {}) {
    const input = document.querySelector(inputSelector);
    const list = document.querySelector(listSelector);
    if (!input || !list) return;

    const debounceMs = options.debounce || 250;
    let timer = null;

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => filterList(input.value, list), debounceMs);
    });
}

function filterList(query, list) {
    const filter = query.toLowerCase().trim();
    const items = list.querySelectorAll('tr, li, .list-group-item');

    items.forEach(item => {
        if (!filter) {
            item.style.display = '';
            return;
        }
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(filter) ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('.panel-filter-input, .card-filter-input, [data-search-filter]');
    const searchable = document.querySelector('.searchable, .filterable tbody');
    if (searchInput && searchable) {
        initSearchIsland(
            '.panel-filter-input, .card-filter-input, [data-search-filter]',
            '.searchable, .filterable tbody'
        );
    }
});
