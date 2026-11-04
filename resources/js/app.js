import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const data = window.dashboardChartData;

    if (!data) {
        return;
    }

    const renderTrendChart = (canvasId, label, values, color, backgroundColor) => {
        const canvas = document.getElementById(canvasId);

        if (!canvas) {
            return;
        }

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label,
                    data: values,
                    borderColor: color,
                    backgroundColor,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#0f172a',
                    pointBorderColor: color,
                    pointBorderWidth: 2,
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => 'S/ ' + new Intl.NumberFormat('es-PE', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }).format(context.parsed.y),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#94a3b8',
                        },
                        border: {
                            color: '#475569',
                        },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#94a3b8',
                            callback: (value) => 'S/ ' + Number(value).toLocaleString('es-PE'),
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.12)',
                        },
                        border: {
                            display: false,
                        },
                    },
                },
            },
        });
    };

    renderTrendChart('salesChart', 'Ventas', data.sales, '#34d399', 'rgba(52, 211, 153, 0.12)');
    renderTrendChart('rentalsChart', 'Alquileres', data.rentals, '#38bdf8', 'rgba(56, 189, 248, 0.12)');
});
