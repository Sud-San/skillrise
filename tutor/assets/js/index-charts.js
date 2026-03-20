'use strict';

/* Chart.js docs: https://www.chartjs.org/ */

window.chartColors = {
    green: '#75c181',
    gray: '#a9b5c9',
    text: '#252930',
    border: '#e7e9ed'
};

// ── ENROLLMENT CHART CONFIG (BAR) ──
var enrollChartConfig = {
    type: 'bar',
    data: {
        labels: window.chartData.enrollments.labels,
        datasets: [
            {
                label: 'New Students',
                backgroundColor: window.chartColors.green,
                borderColor: window.chartColors.green,
                borderWidth: 1,
                maxBarThickness: 32,
                data: window.chartData.enrollments.data
            }
        ]
    },
    options: {
        responsive: true,
        aspectRatio: 1.5,
        plugins: {
            legend: {
                position: 'bottom',
                align: 'end'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                padding: 16,
                borderColor: window.chartColors.border,
                borderWidth: 1,
                backgroundColor: '#fff',
                bodyColor: window.chartColors.text,
                titleColor: window.chartColors.text
            }
        },
        scales: {
            x: {
                display: true,
                grid: {
                    drawBorder: false,
                    color: window.chartColors.border
                }
            },
            y: {
                display: true,
                grid: {
                    drawBorder: false,
                    color: window.chartColors.border
                },
                beginAtZero: true
            }
        }
    }
};

// ── REVENUE CHART CONFIG (LINE) ──
var revenueChartConfig = {
    type: 'line',
    data: {
        labels: window.chartData.revenue.labels,
        datasets: [
            {
                label: 'Revenue (₹)',
                fill: true,
                backgroundColor: 'rgba(117, 193, 129, 0.1)',
                borderColor: window.chartColors.green,
                data: window.chartData.revenue.data,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        aspectRatio: 1.5,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                align: 'end'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                titleMarginBottom: 10,
                bodySpacing: 10,
                padding: 16,
                borderColor: window.chartColors.border,
                borderWidth: 1,
                backgroundColor: '#fff',
                bodyColor: window.chartColors.text,
                titleColor: window.chartColors.text,
                callbacks: {
                    label: function (tooltipItem) {
                        var value = tooltipItem.raw;
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        },
        scales: {
            x: {
                display: true,
                grid: {
                    drawBorder: false,
                    color: window.chartColors.border
                }
            },
            y: {
                display: true,
                grid: {
                    drawBorder: false,
                    color: window.chartColors.border
                },
                beginAtZero: true,
                ticks: {
                    callback: function (value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        }
    }
};

// ── COURSE POPULARITY CHART CONFIG (DOUGHNUT) ──
var popularityChartConfig = {
    type: 'pie',
    data: {
        labels: window.chartData.popularity.labels,
        datasets: [
            {
                backgroundColor: [
                    '#75c181',
                    '#5b99ea',
                    '#f77eb9',
                    '#f6c343',
                    '#8d99ae'
                ],
                data: window.chartData.popularity.data
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom'
            }
        }
    }
};


// ── GENERATE CHARTS ON LOAD ──
document.addEventListener('DOMContentLoaded', function () {

    const enrollCanvas = document.getElementById('canvas-enrollchart');
    if (enrollCanvas !== null) {
        new Chart(enrollCanvas.getContext('2d'), enrollChartConfig);
    }

    const revenueCanvas = document.getElementById('canvas-revenuechart');
    if (revenueCanvas !== null) {
        new Chart(revenueCanvas.getContext('2d'), revenueChartConfig);
    }

    const popularityCanvas = document.getElementById('canvas-coursepopularity');
    if (popularityCanvas !== null) {
        new Chart(popularityCanvas.getContext('2d'), popularityChartConfig);
    }

});
