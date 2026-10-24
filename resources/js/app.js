import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;

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
            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches
                ? false
                : { duration: 400 },
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
