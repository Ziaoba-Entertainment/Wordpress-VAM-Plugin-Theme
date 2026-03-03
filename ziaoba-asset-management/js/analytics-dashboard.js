// js/analytics-dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined' || !window.ziaobaAnalyticsData) return;

    const data = window.ziaobaAnalyticsData;
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { font: { family: 'Inter, sans-serif', size: 12 } }
            },
            tooltip: {
                backgroundColor: '#1a1a1a',
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 },
                padding: 12,
                cornerRadius: 4
            }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    };

    // Store chart instances to destroy them before re-rendering
    window.ziaobaCharts = window.ziaobaCharts || {};

    const renderCharts = () => {
        try {
            // Top 10 Views Chart
            const ctxViews = document.getElementById('viewsChart');
            if (ctxViews) {
                if (window.ziaobaCharts.views) window.ziaobaCharts.views.destroy();
                window.ziaobaCharts.views = new Chart(ctxViews.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.topViews.labels,
                        datasets: [{
                            label: 'Total Views',
                            data: data.topViews.values,
                            backgroundColor: '#E50914',
                            borderRadius: 4,
                            hoverBackgroundColor: '#B20710'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y'
                    }
                });
            }

            // Trend Chart (Impressions & Dwell)
            const ctxTrend = document.getElementById('trendChart');
            if (ctxTrend) {
                if (window.ziaobaCharts.trend) window.ziaobaCharts.trend.destroy();
                window.ziaobaCharts.trend = new Chart(ctxTrend.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: data.trends.labels,
                        datasets: [
                            {
                                label: 'Ad Impressions',
                                data: data.trends.impressions,
                                borderColor: '#00C853',
                                backgroundColor: 'rgba(0, 200, 83, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Avg Dwell (s)',
                                data: data.trends.dwell,
                                borderColor: '#2196F3',
                                backgroundColor: 'rgba(33, 150, 243, 0.1)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: commonOptions
                });
            }
        } catch (error) {
            console.error('Ziaoba Analytics Chart Error:', error);
        }
    };

    renderCharts();
});
