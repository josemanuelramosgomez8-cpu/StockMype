import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;

// Contador animado para las tarjetas del dashboard
Alpine.data('animatedCounter', (target, decimals = 0, prefix = '') => ({
    value: target,

    start() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            this.value = target;

            return;
        }

        const duration = 800;
        const startedAt = performance.now();
        this.value = 0;

        const update = (now) => {
            const progress = Math.min((now - startedAt) / duration, 1);
            this.value = target * progress;

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                this.value = target;
            }
        };

        requestAnimationFrame(update);
    },

    get formatted() {
        return prefix + new Intl.NumberFormat('es-PE', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(this.value);
    },
}));

Alpine.start();

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Aparición suave de secciones de la landing al hacer scroll
if (!reducedMotion && 'IntersectionObserver' in window) {
    document.body.classList.add('landing-reveal-ready');

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('[data-reveal]').forEach((section) => {
        revealObserver.observe(section);
    });
}

// Imágenes con respaldo (placeholder) si el archivo no existe
document.querySelectorAll('[data-image-slot]').forEach((slot) => {
    const image = slot.querySelector('[data-slot-image]');
    const fallback = slot.querySelector('[data-image-fallback]');

    if (!image || !fallback) {
        return;
    }

    const showImage = () => {
        fallback.classList.add('hidden');
        image.classList.remove('opacity-0');
        image.classList.add('opacity-100');
    };

    const showFallback = () => {
        image.classList.add('hidden');
        fallback.classList.remove('hidden');
    };

    image.addEventListener('load', showImage, { once: true });
    image.addEventListener('error', showFallback, { once: true });

    if (image.complete) {
        image.naturalWidth > 0 ? showImage() : showFallback();
    }
});

// Gráfico de ventas de los últimos 7 días (verde esmeralda)
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
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.12)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#059669',
                pointBorderWidth: 2,
                pointHoverBackgroundColor: '#059669',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches
                ? false
                : { duration: 600, easing: 'easeOutQuart' },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: '#064e3b',
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: (context) => formatoSoles.format(context.parsed.y),
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                },
                y: {
                    beginAtZero: true,
                    border: {
                        display: false,
                    },
                    grid: {
                        color: '#f3f4f6',
                    },
                    ticks: {
                        maxTicksLimit: 5,
                        callback: (value) => formatoSoles.format(value),
                    },
                },
            },
        },
    });
}