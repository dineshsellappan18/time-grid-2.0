/**
 * Reports module — Chart.js wrapper for KPI visualizations.
 * Initialises bar, line, and doughnut charts from data attributes.
 */
import { Chart, BarController, LineController, DoughnutController, CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Tooltip, Legend, Filler } from 'chart.js';

Chart.register(BarController, LineController, DoughnutController, CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Tooltip, Legend, Filler);

var defaultColors = {
    primary: '#6366f1',
    success: '#22c55e',
    danger: '#ef4444',
    warning: '#f59e0b',
    info: '#3b82f6',
    muted: '#94a3b8',
};

function initChart(canvasId, type, labels, datasets, options) {
    var canvas = document.getElementById(canvasId);
    if (!canvas) return null;

    var ctx = canvas.getContext('2d');
    return new Chart(ctx, {
        type: type,
        data: { labels: labels, datasets: datasets },
        options: Object.assign({
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { size: 12 } } },
                tooltip: { backgroundColor: '#1e293b', titleFont: { size: 12 }, bodyFont: { size: 11 }, padding: 10, cornerRadius: 6 },
            },
        }, options || {}),
    });
}

function initBarChart(canvasId, labels, data, label) {
    return initChart(canvasId, 'bar', labels, [{
        label: label || 'Appointments',
        data: data,
        backgroundColor: defaultColors.primary + 'cc',
        borderColor: defaultColors.primary,
        borderWidth: 1,
        borderRadius: 4,
    }], {
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f1f5f9' } },
            x: { ticks: { font: { size: 11 } }, grid: { display: false } },
        },
    });
}

function initLineChart(canvasId, labels, datasets) {
    return initChart(canvasId, 'line', labels, datasets, {
        scales: {
            y: { beginAtZero: true, ticks: { font: { size: 11 } }, grid: { color: '#f1f5f9' } },
            x: { ticks: { font: { size: 11 } }, grid: { display: false } },
        },
        elements: { line: { tension: 0.3 } },
    });
}

function initDoughnutChart(canvasId, labels, data, colors) {
    return initChart(canvasId, 'doughnut', labels, [{
        data: data,
        backgroundColor: colors || [defaultColors.primary, defaultColors.success, defaultColors.danger, defaultColors.warning],
        borderWidth: 0,
    }], {
        cutout: '65%',
    });
}

function exportTableToCSV(tableId, filename) {
    var table = document.getElementById(tableId);
    if (!table) return;

    var csv = [];
    var rows = table.querySelectorAll('tr');
    rows.forEach(function(row) {
        var cols = row.querySelectorAll('td, th');
        var rowData = [];
        cols.forEach(function(col) { rowData.push('"' + col.textContent.trim().replace(/"/g, '""') + '"'); });
        csv.push(rowData.join(','));
    });

    var blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename || 'report.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}

window.tgReports = { initBarChart: initBarChart, initLineChart: initLineChart, initDoughnutChart: initDoughnutChart, exportTableToCSV: exportTableToCSV };
