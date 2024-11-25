// File: js/admin/reports.js

// Chart.js default configuration
Chart.defaults.color = '#9CA3AF';
Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, sans-serif";
Chart.defaults.scale.grid.color = 'rgba(255, 255, 255, 0.1)';
Chart.defaults.scale.grid.drawBorder = false;

function initializeCharts() {
    initializeUserGrowthChart();
    initializeActivityChart();
    initializeRetentionChart();
}

function initializeUserGrowthChart() {
    const ctx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: userGrowthData.map(d => formatDate(d.date)),
            datasets: [{
                label: 'Total Users',
                data: userGrowthData.map(d => d.total_users),
                borderColor: '#6366F1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2
            }, {
                label: 'Active Users',
                data: userGrowthData.map(d => d.active_users),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 13
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e1e1e',
                    titleColor: '#fff',
                    bodyColor: '#9CA3AF',
                    borderColor: '#333',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        padding: 10,
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                x: {
                    ticks: {
                        padding: 10,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

function initializeActivityChart() {
    const ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: activityData.map(d => formatDate(d.date)),
            datasets: [{
                label: 'Entries',
                data: activityData.map(d => d.entries),
                backgroundColor: '#10B981',
                borderRadius: 4
            }, {
                label: 'Collections',
                data: activityData.map(d => d.collections),
                backgroundColor: '#6366F1',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 13
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e1e1e',
                    titleColor: '#fff',
                    bodyColor: '#9CA3AF',
                    borderColor: '#333',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        padding: 10,
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                x: {
                    ticks: {
                        padding: 10,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

function initializeRetentionChart() {
    const ctx = document.getElementById('retentionChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Daily', 'Weekly', 'Monthly'],
            datasets: [{
                data: [
                    retentionData.daily_retention,
                    retentionData.weekly_retention,
                    retentionData.monthly_retention
                ],
                backgroundColor: [
                    '#10B981',
                    '#6366F1',
                    '#F59E0B'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 13
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e1e1e',
                    titleColor: '#fff',
                    bodyColor: '#9CA3AF',
                    borderColor: '#333',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
}

// Update charts when window is resized
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        initializeCharts();
    }, 250);
});

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeCharts);