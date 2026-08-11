/**
 * DataTable: sortable columns, search/filter bar, pagination,
 * bulk selection, loading skeleton, empty state, mobile card layout.
 * Vanilla ES module — no jQuery dependency.
 */

(function () {
    'use strict';

    var DEBOUNCE_MS = 300;
    var MOBILE_BREAKPOINT = 768;

    // ── Debounce utility ───────────────────────────────────────────

    function debounce(fn, ms) {
        var timer;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(ctx, args); }, ms);
        };
    }

    // ── Sort ────────────────────────────────────────────────────────

    function initSortableHeaders(table) {
        var headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach(function (th) {
            th.classList.add('tg-table__th--sortable');
            th.setAttribute('role', 'columnheader');
            th.setAttribute('aria-sort', 'none');
            th.addEventListener('click', function () {
                sortByColumn(table, th);
            });
        });
    }

    function sortByColumn(table, th) {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr:not(.tg-table__empty-row):not(.tg-table__skeleton-row)'));
        var colIndex = Array.prototype.indexOf.call(th.parentNode.children, th);
        var currentDir = th.getAttribute('aria-sort');
        var newDir = currentDir === 'ascending' ? 'descending' : 'ascending';

        table.querySelectorAll('th[data-sortable]').forEach(function (h) {
            h.setAttribute('aria-sort', 'none');
            h.classList.remove('tg-table__th--asc', 'tg-table__th--desc');
        });

        th.setAttribute('aria-sort', newDir);
        th.classList.add(newDir === 'ascending' ? 'tg-table__th--asc' : 'tg-table__th--desc');

        rows.sort(function (a, b) {
            var aVal = getCellText(a, colIndex);
            var bVal = getCellText(b, colIndex);
            var aNum = parseFloat(aVal);
            var bNum = parseFloat(bVal);

            if (!isNaN(aNum) && !isNaN(bNum)) {
                return newDir === 'ascending' ? aNum - bNum : bNum - aNum;
            }
            var cmp = aVal.localeCompare(bVal, undefined, { sensitivity: 'base' });
            return newDir === 'ascending' ? cmp : -cmp;
        });

        rows.forEach(function (row) { tbody.appendChild(row); });
    }

    function getCellText(row, index) {
        var cell = row.children[index];
        if (!cell) return '';
        return (cell.getAttribute('data-sort-value') || cell.textContent || '').trim();
    }

    // ── Search / Filter ────────────────────────────────────────────

    function initSearchFilter(wrapper) {
        var input = wrapper.querySelector('.tg-table-search__input');
        var filterDropdowns = wrapper.querySelectorAll('.tg-table-filter__select');
        var table = wrapper.querySelector('.tg-table');
        if (!input || !table) return;

        var debouncedFilter = debounce(function () {
            filterRows(table, input, filterDropdowns);
        }, DEBOUNCE_MS);

        input.addEventListener('input', debouncedFilter);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                input.value = '';
                filterRows(table, input, filterDropdowns);
            }
        });

        filterDropdowns.forEach(function (sel) {
            sel.addEventListener('change', function () {
                filterRows(table, input, filterDropdowns);
            });
        });
    }

    function filterRows(table, searchInput, filterDropdowns) {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var rows = tbody.querySelectorAll('tr:not(.tg-table__empty-row):not(.tg-table__skeleton-row)');
        var term = (searchInput.value || '').toLowerCase().trim();
        var visibleCount = 0;

        var filters = {};
        filterDropdowns.forEach(function (sel) {
            var col = sel.getAttribute('data-filter-column');
            var val = sel.value;
            if (col && val) filters[col] = val.toLowerCase();
        });

        rows.forEach(function (row) {
            var textMatch = !term || row.textContent.toLowerCase().indexOf(term) !== -1;
            var filterMatch = true;

            Object.keys(filters).forEach(function (col) {
                var colIdx = parseInt(col, 10);
                var cell = row.children[colIdx];
                if (cell) {
                    var cellVal = (cell.getAttribute('data-filter-value') || cell.textContent || '').toLowerCase().trim();
                    if (cellVal.indexOf(filters[col]) === -1) filterMatch = false;
                }
            });

            if (textMatch && filterMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var emptyRow = tbody.querySelector('.tg-table__empty-row');
        if (emptyRow) {
            emptyRow.style.display = visibleCount === 0 ? '' : 'none';
        }

        updatePaginationAfterFilter(table, rows);
    }

    // ── Pagination ─────────────────────────────────────────────────

    function initPagination(wrapper) {
        var table = wrapper.querySelector('.tg-table');
        var paginationEl = wrapper.querySelector('.tg-table-pagination');
        if (!table || !paginationEl) return;

        var perPage = parseInt(paginationEl.getAttribute('data-per-page'), 10) || 10;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        paginationEl._perPage = perPage;
        paginationEl._currentPage = 1;

        renderPagination(wrapper);
    }

    function renderPagination(wrapper) {
        var table = wrapper.querySelector('.tg-table');
        var paginationEl = wrapper.querySelector('.tg-table-pagination');
        if (!table || !paginationEl) return;

        var tbody = table.querySelector('tbody');
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr:not(.tg-table__empty-row):not(.tg-table__skeleton-row)'));
        var visibleRows = rows.filter(function (r) { return r.style.display !== 'none'; });

        var perPage = paginationEl._perPage || 10;
        var currentPage = paginationEl._currentPage || 1;
        var totalPages = Math.max(1, Math.ceil(visibleRows.length / perPage));

        if (currentPage > totalPages) currentPage = totalPages;
        paginationEl._currentPage = currentPage;

        visibleRows.forEach(function (row, idx) {
            var page = Math.floor(idx / perPage) + 1;
            row.style.display = page === currentPage ? '' : 'none';
        });

        rows.filter(function (r) { return r.style.display === 'none' && !visibleRows.includes(r); }).forEach(function (r) {
            r.style.display = 'none';
        });

        paginationEl.innerHTML = '';

        if (totalPages <= 1) return;

        var nav = document.createElement('nav');
        nav.setAttribute('aria-label', 'Table pagination');
        var ul = document.createElement('ul');
        ul.className = 'pagination pagination-sm mb-0';

        // Prev
        var prevLi = createPageItem('&laquo;', currentPage > 1 ? currentPage - 1 : null, currentPage <= 1);
        ul.appendChild(prevLi);

        for (var p = 1; p <= totalPages; p++) {
            var li = createPageItem(p, p, false, p === currentPage);
            ul.appendChild(li);
        }

        // Next
        var nextLi = createPageItem('&raquo;', currentPage < totalPages ? currentPage + 1 : null, currentPage >= totalPages);
        ul.appendChild(nextLi);

        nav.appendChild(ul);
        paginationEl.appendChild(nav);

        var info = document.createElement('span');
        info.className = 'tg-table-pagination__info';
        var start = (currentPage - 1) * perPage + 1;
        var end = Math.min(currentPage * perPage, visibleRows.length);
        info.textContent = start + '–' + end + ' of ' + visibleRows.length;
        paginationEl.appendChild(info);

        ul.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-page]');
            if (!btn) return;
            e.preventDefault();
            var page = parseInt(btn.getAttribute('data-page'), 10);
            if (page && page !== currentPage) {
                paginationEl._currentPage = page;
                renderPagination(wrapper);
            }
        });
    }

    function createPageItem(label, page, disabled, active) {
        var li = document.createElement('li');
        li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
        var a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.innerHTML = label;
        if (page) a.setAttribute('data-page', page);
        if (active) {
            var sr = document.createElement('span');
            sr.className = 'visually-hidden';
            sr.textContent = '(current)';
            a.appendChild(sr);
        }
        li.appendChild(a);
        return li;
    }

    function updatePaginationAfterFilter(table, allRows) {
        var wrapper = table.closest('.tg-table-wrapper');
        if (!wrapper) return;
        var paginationEl = wrapper.querySelector('.tg-table-pagination');
        if (!paginationEl) return;
        paginationEl._currentPage = 1;
        renderPagination(wrapper);
    }

    // ── Bulk Selection ─────────────────────────────────────────────

    function initBulkSelection(wrapper) {
        var selectAll = wrapper.querySelector('.tg-table__select-all');
        var table = wrapper.querySelector('.tg-table');
        var actionBar = wrapper.querySelector('.tg-bulk-actions');
        if (!selectAll || !table) return;

        selectAll.addEventListener('change', function () {
            var checked = this.checked;
            var checkboxes = table.querySelectorAll('.tg-table__row-select');
            checkboxes.forEach(function (cb) {
                var row = cb.closest('tr');
                if (row && row.style.display !== 'none') {
                    cb.checked = checked;
                }
            });
            updateBulkBar(wrapper);
        });

        table.addEventListener('change', function (e) {
            if (e.target.classList.contains('tg-table__row-select')) {
                updateBulkBar(wrapper);
                updateSelectAllState(wrapper);
            }
        });
    }

    function updateBulkBar(wrapper) {
        var actionBar = wrapper.querySelector('.tg-bulk-actions');
        if (!actionBar) return;

        var checked = wrapper.querySelectorAll('.tg-table__row-select:checked');
        var countEl = actionBar.querySelector('.tg-bulk-actions__count');

        if (checked.length > 0) {
            actionBar.classList.add('is-visible');
            if (countEl) countEl.textContent = checked.length + ' selected';
        } else {
            actionBar.classList.remove('is-visible');
        }
    }

    function updateSelectAllState(wrapper) {
        var selectAll = wrapper.querySelector('.tg-table__select-all');
        if (!selectAll) return;
        var total = wrapper.querySelectorAll('.tg-table__row-select');
        var checked = wrapper.querySelectorAll('.tg-table__row-select:checked');
        selectAll.checked = total.length > 0 && total.length === checked.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < total.length;
    }

    // ── Loading Skeleton ───────────────────────────────────────────

    function showSkeleton(wrapper) {
        var skeleton = wrapper.querySelector('.tg-table-skeleton');
        var table = wrapper.querySelector('.tg-table');
        if (skeleton) skeleton.style.display = '';
        if (table) table.style.display = 'none';
    }

    function hideSkeleton(wrapper) {
        var skeleton = wrapper.querySelector('.tg-table-skeleton');
        var table = wrapper.querySelector('.tg-table');
        if (skeleton) skeleton.style.display = 'none';
        if (table) table.style.display = '';
    }

    // ── Responsive Card Layout ─────────────────────────────────────

    function initResponsive(wrapper) {
        var table = wrapper.querySelector('.tg-table');
        if (!table) return;

        function checkWidth() {
            if (window.innerWidth < MOBILE_BREAKPOINT) {
                wrapper.classList.add('tg-table-wrapper--cards');
            } else {
                wrapper.classList.remove('tg-table-wrapper--cards');
            }
        }

        window.addEventListener('resize', debounce(checkWidth, 150));
        checkWidth();
    }

    // ── Init ───────────────────────────────────────────────────────

    function initDataTable(wrapper) {
        var table = wrapper.querySelector('.tg-table');
        if (!table) return;

        initSortableHeaders(table);
        initSearchFilter(wrapper);
        initPagination(wrapper);
        initBulkSelection(wrapper);
        initResponsive(wrapper);

        hideSkeleton(wrapper);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var wrappers = document.querySelectorAll('.tg-table-wrapper');
        wrappers.forEach(initDataTable);
    });

    window.TgDataTable = {
        showSkeleton: showSkeleton,
        hideSkeleton: hideSkeleton,
        refresh: function (wrapper) {
            var paginationEl = wrapper.querySelector('.tg-table-pagination');
            if (paginationEl) {
                paginationEl._currentPage = 1;
                renderPagination(wrapper);
            }
        }
    };
})();
