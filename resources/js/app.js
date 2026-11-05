import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const themeSwitchers = document.querySelectorAll('[data-theme-switcher]');

    if (themeSwitchers.length > 0) {
        const preferenceKey = document.documentElement.dataset.themeStorageKey;

        const updateThemeSwitcher = () => {
            const isCobaltTheme = document.documentElement.dataset.theme === 'cobalt';

            themeSwitchers.forEach((themeSwitcher) => {
                themeSwitcher.setAttribute('aria-pressed', String(isCobaltTheme));
                themeSwitcher.setAttribute('aria-label', isCobaltTheme
                    ? 'Cambiar de tema. Restaurar tema predeterminado'
                    : 'Cambiar de tema. Activar tema cobalto');
                themeSwitcher.querySelector('[data-theme-description]').textContent = isCobaltTheme
                    ? 'Restaurar tema predeterminado'
                    : 'Activar tema cobalto';
            });
        };

        themeSwitchers.forEach((themeSwitcher) => {
            themeSwitcher.addEventListener('click', () => {
                const isCobaltTheme = document.documentElement.dataset.theme !== 'cobalt';

                if (isCobaltTheme) {
                    document.documentElement.dataset.theme = 'cobalt';
                } else {
                    delete document.documentElement.dataset.theme;
                }

                try {
                    if (isCobaltTheme) {
                        window.localStorage.setItem(preferenceKey, 'cobalt');
                    } else {
                        window.localStorage.removeItem(preferenceKey);
                    }
                } catch (error) {
                    console.error('No se pudo guardar la preferencia de tema:', error);
                }

                updateThemeSwitcher();
            });
        });

        updateThemeSwitcher();
    }

    const productSearchForm = document.querySelector('[data-product-search]');

    if (productSearchForm) {
        const searchInput = productSearchForm.querySelector('[name="q"]');
        const searchStatus = document.getElementById('product-search-status');
        let searchTimeout;
        let searchController;
        let searchRequest = 0;

        searchInput.addEventListener('input', () => {
            window.clearTimeout(searchTimeout);
            searchController?.abort();

            const request = ++searchRequest;
            searchTimeout = window.setTimeout(async () => {
                const url = new URL(productSearchForm.action, window.location.origin);
                const currentUrl = new URL(window.location.href);

                for (const [name, value] of new FormData(productSearchForm)) {
                    if (value !== '') {
                        url.searchParams.set(name, value);
                    }
                }

                if (searchInput.value === (currentUrl.searchParams.get('q') ?? '')) {
                    searchStatus.textContent = '';
                    return;
                }

                searchController = new AbortController();
                searchStatus.textContent = 'Buscando productos...';

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: searchController.signal,
                    });

                    if (!response.ok) {
                        throw new Error(`La búsqueda respondió con estado ${response.status}.`);
                    }

                    const responseDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
                    const updatedResults = responseDocument.getElementById('catalog-results');
                    const currentResults = document.getElementById('catalog-results');

                    if (request !== searchRequest) {
                        return;
                    }

                    if (!updatedResults || !currentResults) {
                        throw new Error('No se pudieron actualizar los resultados del catálogo.');
                    }

                    currentResults.replaceWith(updatedResults);
                    window.history.replaceState({}, '', url);
                    document.getElementById('clear-catalog-search').classList.toggle('hidden', searchInput.value === '');
                    searchStatus.textContent = 'Resultados actualizados.';
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    console.error('Error al buscar productos:', error);
                    searchStatus.textContent = 'No se pudieron actualizar los resultados. Intenta nuevamente.';
                }
            }, 300);
        });
    }

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
