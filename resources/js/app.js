import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;

Alpine.start();

const ventasCanvas = document.getElementById('ventas-por-dia-chart');
const ventasData = document.getElementById('ventas-por-dia-data');

if (ventasCanvas && ventasData) {
    const ventasPorDia = JSON.parse(ventasData.textContent);
    const formatoSoles = new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
    });

    new Chart(ventasCanvas, {
        type: 'line',
        data: {
            labels: ventasPorDia.map((dia) => dia.fecha),
            datasets: [{
                label: 'Ventas',
                data: ventasPorDia.map((dia) => dia.total),
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                        label: (context) => formatoSoles.format(context.parsed.y),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatoSoles.format(value),
                    },
                },
            },
        },
    });
}
