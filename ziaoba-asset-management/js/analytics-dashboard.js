/**
 * Analytics Dashboard JS
 */
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined' || typeof ziaobaAnalyticsData === 'undefined') {
        return;
    }

    const viewsCtx = document.getElementById('viewsChart');
    if (viewsCtx) {
        new Chart(viewsCtx, {
            type: 'bar',
            data: {
                labels: ziaobaAnalyticsData.topViews.labels,
                datasets: [{
                    label: 'Total Views',
                    data: ziaobaAnalyticsData.topViews.values,
                    backgroundColor: '#E50914',
                    borderColor: '#E50914',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }
});
