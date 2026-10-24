<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Controla tu inventario y ventas con StockMype. Recibe alertas cuando tus productos estén por agotarse o vencer. Hecho para negocios de Tumbes, Perú.">
        <meta name="theme-color" content="#059669">
        <title>StockMype | Inventario y ventas para negocios de Tumbes</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-900 antialiased">
        <div x-data="{ menuOpen: false }" class="min-h-screen overflow-hidden">
            <header class="relative z-20 border-b border-gray-100 bg-white">
                <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8" aria-label="Navegación principal">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2" aria-label="StockMype, página de inicio">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3.5 7.7 8.5 4.2 8.5-4.2M12 12v8.5M7.5 5.3l9 4.5" />
                            </svg>
                        </span>
                        <span class="text-lg font-bold tracking-tight text-gray-900 sm:text-xl">StockMype</span>
                    </a>

                    <div class="hidden items-center gap-3 sm:flex">
                        <a href="{{ route('login') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Crear cuenta de negocio</a>
                    </div>

                    <button type="button"
                            @click="menuOpen = !menuOpen"
                            @keydown.escape.window="menuOpen = false"
                            :aria-expanded="menuOpen.toString()"
                            aria-controls="menu-movil"
                            aria-label="Abrir menú"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-lg text-gray-700 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 sm:hidden">
                        <svg x-show="!menuOpen" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-cloak x-show="menuOpen" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 18 12-12M6 6l12 12" />
                        </svg>
                    </button>
                </nav>

                <div id="menu-movil"
                     x-cloak
                     x-show="menuOpen"
                     x-transition
                     @click.outside="menuOpen = false"
                     class="absolute inset-x-0 top-full border-b border-gray-200 bg-white p-4 shadow-lg sm:hidden"
                     style="display: none;">
                    <div class="mx-auto flex max-w-7xl flex-col gap-2">
                        <a href="{{ route('login') }}" class="flex min-h-11 items-center rounded-lg px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 hover:text-emerald-700">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="flex min-h-11 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700">Crear cuenta de negocio</a>
                    </div>
                </div>
            </header>

            <main>
                <section class="relative isolate">
                    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-emerald-50/80 via-white to-white"></div>
                    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-24">
                        <div class="mx-auto max-w-2xl text-center lg:mx-0 lg:text-left">
                            <p class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-white px-3.5 py-2 text-xs font-semibold text-emerald-800 shadow-sm sm:text-sm">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Para negocios de Tumbes, Perú
                            </p>
                            <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-gray-950 sm:text-5xl lg:text-6xl">
                                Nunca más te quedes <span class="text-emerald-600">sin stock</span>
                            </h1>
                            <p class="mx-auto mt-6 max-w-xl text-base leading-7 text-gray-600 sm:text-lg sm:leading-8 lg:mx-0">
                                Controla tus productos y ventas en un solo lugar. StockMype te avisa a tiempo cuando algo está por agotarse o vencer, para que puedas atender a tus clientes con tranquilidad.
                            </p>
                            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row lg:justify-start">
                                <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                    Crear cuenta de negocio
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7" />
                                    </svg>
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Iniciar sesión</a>
                            </div>
                            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-gray-500 sm:text-sm lg:justify-start">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" />
                                    </svg>
                                    Fácil de usar
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" />
                                    </svg>
                                    Desde tu celular
                                </span>
                            </div>
                        </div>

                        <div class="mx-auto w-full max-w-xl lg:ml-auto lg:mr-0">
                            <div class="relative">
                                <div class="absolute -inset-4 -z-10 rounded-[2rem] bg-emerald-100/70 blur-2xl"></div>
                                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl shadow-emerald-900/10">
                                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3.5 sm:px-6">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3.5 7.7 8.5 4.2 8.5-4.2M12 12v8.5" />
                                                </svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">Mi negocio</p>
                                                <p class="text-[11px] text-gray-500">Resumen de inventario</p>
                                            </div>
                                        </div>
                                        <div class="relative flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 text-gray-600">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4.5-5.8V4a1.5 1.5 0 0 0-3 0v1.2A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
                                            </svg>
                                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">3</span>
                                        </div>
                                    </div>

                                    <div class="space-y-4 bg-gray-50/70 p-4 sm:p-6">
                                        <div class="grid grid-cols-3 gap-2.5 sm:gap-3">
                                            <div class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm sm:p-4">
                                                <p class="text-[10px] font-medium text-gray-500 sm:text-xs">Productos</p>
                                                <p class="mt-2 text-xl font-bold text-gray-900 sm:text-2xl">128</p>
                                                <p class="mt-1 text-[10px] text-emerald-700 sm:text-xs">En inventario</p>
                                            </div>
                                            <div class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm sm:p-4">
                                                <p class="text-[10px] font-medium text-gray-500 sm:text-xs">Ventas hoy</p>
                                                <p class="mt-2 text-xl font-bold text-gray-900 sm:text-2xl">S/ 486</p>
                                                <p class="mt-1 text-[10px] text-gray-500 sm:text-xs">12 ventas</p>
                                            </div>
                                            <div class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm sm:p-4">
                                                <p class="text-[10px] font-medium text-gray-500 sm:text-xs">Alertas</p>
                                                <p class="mt-2 text-xl font-bold text-orange-600 sm:text-2xl">3</p>
                                                <p class="mt-1 text-[10px] text-orange-700 sm:text-xs">Por atender</p>
                                            </div>
                                        </div>

                                        <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                                                <div>
                                                    <h2 class="text-xs font-semibold text-gray-900 sm:text-sm">Productos que necesitan atención</h2>
                                                    <p class="mt-0.5 text-[10px] text-gray-500 sm:text-xs">Revisa tu inventario a tiempo</p>
                                                </div>
                                                <span class="rounded-full bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-700">3 alertas</span>
                                            </div>
                                            <ul class="divide-y divide-gray-100">
                                                <li class="flex items-center justify-between gap-2 px-4 py-3">
                                                    <div class="flex min-w-0 items-center gap-2.5">
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v9m0 4h.01M5.6 20h12.8a2 2 0 0 0 1.7-3L13.7 5a2 2 0 0 0-3.4 0l-6.4 12a2 2 0 0 0 1.7 3Z" />
                                                            </svg>
                                                        </span>
                                                        <span class="min-w-0">
                                                            <span class="block truncate text-xs font-semibold text-gray-900 sm:text-sm">Arroz extra</span>
                                                            <span class="block text-[10px] text-gray-500 sm:text-xs">Stock: 0 kg</span>
                                                        </span>
                                                    </div>
                                                    <span class="shrink-0 rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-semibold text-red-700 sm:text-xs">Agotado</span>
                                                </li>
                                                <li class="flex items-center justify-between gap-2 px-4 py-3">
                                                    <div class="flex min-w-0 items-center gap-2.5">
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                            </svg>
                                                        </span>
                                                        <span class="min-w-0">
                                                            <span class="block truncate text-xs font-semibold text-gray-900 sm:text-sm">Aceite vegetal</span>
                                                            <span class="block text-[10px] text-gray-500 sm:text-xs">Stock: 2 botellas</span>
                                                        </span>
                                                    </div>
                                                    <span class="shrink-0 rounded-full bg-orange-50 px-2.5 py-1 text-[10px] font-semibold text-orange-700 sm:text-xs">Stock bajo</span>
                                                </li>
                                                <li class="flex items-center justify-between gap-2 px-4 py-3">
                                                    <div class="flex min-w-0 items-center gap-2.5">
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-yellow-50 text-yellow-700">
                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                            </svg>
                                                        </span>
                                                        <span class="min-w-0">
                                                            <span class="block truncate text-xs font-semibold text-gray-900 sm:text-sm">Leche evaporada</span>
                                                            <span class="block text-[10px] text-gray-500 sm:text-xs">Vence en 5 días</span>
                                                        </span>
                                                    </div>
                                                    <span class="shrink-0 rounded-full bg-yellow-50 px-2.5 py-1 text-[10px] font-semibold text-yellow-800 sm:text-xs">Por vencer</span>
                                                </li>
                                            </ul>
                                            <div class="border-t border-gray-100 px-4 py-2.5 text-center">
                                                <span class="text-xs font-semibold text-emerald-700">Tu inventario, siempre bajo control</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="absolute -bottom-5 -left-3 hidden items-center gap-2 rounded-xl border border-gray-100 bg-white px-4 py-3 shadow-lg sm:flex lg:-left-8">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" />
                                        </svg>
                                    </span>
                                    <span class="text-xs font-semibold text-gray-800">Todo en orden, a tiempo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="border-t border-gray-100 bg-white py-16 sm:py-20" aria-labelledby="beneficios-titulo">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <p class="text-sm font-semibold text-emerald-700">Hecho para ayudarte</p>
                            <h2 id="beneficios-titulo" class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Menos preocupaciones, más tiempo para tu negocio</h2>
                            <p class="mt-4 text-base leading-7 text-gray-600">Herramientas sencillas para que sepas qué tienes y qué necesitas reponer.</p>
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:mt-14 lg:grid-cols-4">
                            <article class="group rounded-2xl border border-gray-200 bg-white p-5 transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-900/5 sm:p-6">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 transition group-hover:bg-emerald-600 group-hover:text-white">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4.5-5.8V4a1.5 1.5 0 0 0-3 0v1.2A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
                                    </svg>
                                </span>
                                <h3 class="mt-5 font-semibold text-gray-900">Alertas automáticas</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Entérate si un producto se agota, tiene poco stock o está por vencer.</p>
                            </article>
                            <article class="group rounded-2xl border border-gray-200 bg-white p-5 transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-900/5 sm:p-6">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 transition group-hover:bg-emerald-600 group-hover:text-white">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l2.4 12.2A2 2 0 0 0 9.4 17h8.8a2 2 0 0 0 2-1.6L22 8H6m4 13h.01M18 21h.01" />
                                    </svg>
                                </span>
                                <h3 class="mt-5 font-semibold text-gray-900">Control de ventas</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Registra cada venta y descuenta el stock automáticamente.</p>
                            </article>
                            <article class="group rounded-2xl border border-gray-200 bg-white p-5 transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-900/5 sm:p-6">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 transition group-hover:bg-emerald-600 group-hover:text-white">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2V7m3 10v-5m2 9H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z" />
                                    </svg>
                                </span>
                                <h3 class="mt-5 font-semibold text-gray-900">Reportes en PDF y Excel</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Consulta tus ventas y movimientos por fechas y descarga los reportes.</p>
                            </article>
                            <article class="group rounded-2xl border border-gray-200 bg-white p-5 transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-900/5 sm:p-6">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 transition group-hover:bg-emerald-600 group-hover:text-white">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18h.01M8 21h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z" />
                                    </svg>
                                </span>
                                <h3 class="mt-5 font-semibold text-gray-900">Funciona desde tu celular</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Revisa tu negocio donde estés, sin instalar aplicaciones adicionales.</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="bg-gray-50 py-16 sm:py-20" aria-labelledby="como-funciona-titulo">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <p class="text-sm font-semibold text-emerald-700">Empieza sin complicaciones</p>
                            <h2 id="como-funciona-titulo" class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">¿Cómo funciona?</h2>
                            <p class="mt-4 text-base leading-7 text-gray-600">Tres pasos sencillos para tener tu inventario al día.</p>
                        </div>
                        <div class="mt-10 grid gap-6 md:grid-cols-3 lg:mt-14">
                            <article class="relative rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">1</span>
                                <h3 class="mt-5 text-lg font-semibold text-gray-900">Registra tus productos</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Agrega lo que vendes, cuánto tienes y cuál es tu stock mínimo.</p>
                            </article>
                            <article class="relative rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">2</span>
                                <h3 class="mt-5 text-lg font-semibold text-gray-900">Registra tus ventas</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Anota cada venta; StockMype actualizará las cantidades por ti.</p>
                            </article>
                            <article class="relative rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">3</span>
                                <h3 class="mt-5 text-lg font-semibold text-gray-900">Recibe alertas a tiempo</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Sabrás qué reponer o revisar antes de que afecte tus ventas.</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="bg-white py-16 sm:py-20" aria-labelledby="rubros-titulo">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <p class="text-sm font-semibold text-emerald-700">Cerca de tu realidad</p>
                            <h2 id="rubros-titulo" class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Para los negocios que mueven Tumbes</h2>
                            <p class="mt-4 text-base leading-7 text-gray-600">StockMype se adapta a distintos tipos de negocio, desde el primer día.</p>
                        </div>
                        <div class="mx-auto mt-10 grid max-w-4xl gap-5 md:grid-cols-2 lg:mt-14">
                            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg sm:p-8">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-700">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M8 9v.01M8 12v.01M8 15v.01M8 18v.01M14 9v.01M14 12v.01M14 15v.01M14 18v.01" />
                                    </svg>
                                </span>
                                <h3 class="mt-5 text-xl font-bold text-gray-900">Gastronomía</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Cuida tus ingredientes y evita quedarte sin lo que necesitas para atender.</p>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-orange-50 px-3 py-1.5 text-xs font-medium text-orange-800">Cevicherías</span>
                                    <span class="rounded-full bg-orange-50 px-3 py-1.5 text-xs font-medium text-orange-800">Restaurantes</span>
                                    <span class="rounded-full bg-orange-50 px-3 py-1.5 text-xs font-medium text-orange-800">Cafeterías</span>
                                </div>
                            </article>
                            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg sm:p-8">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M5 10V6l2-3h10l2 3v4m-14 0v10h14V10M9 20v-6h6v6M3 10a2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 4 0" />
                                    </svg>
                                </span>
                                <h3 class="mt-5 text-xl font-bold text-gray-900">Retail y comercio</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Organiza tus productos, conoce tus existencias y registra tus ventas fácilmente.</p>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-800">Bodegas</span>
                                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-800">Minimarkets</span>
                                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-800">Tiendas de ropa</span>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="px-4 pb-16 sm:px-6 sm:pb-20 lg:px-8" aria-labelledby="cta-titulo">
                    <div class="mx-auto max-w-7xl overflow-hidden rounded-3xl bg-emerald-700 px-6 py-12 text-center shadow-xl shadow-emerald-900/10 sm:px-10 sm:py-16">
                        <div class="mx-auto max-w-2xl">
                            <p class="text-sm font-semibold text-emerald-100">Tu negocio merece más tranquilidad</p>
                            <h2 id="cta-titulo" class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Empieza a cuidar tu inventario hoy</h2>
                            <p class="mt-4 text-base leading-7 text-emerald-50">Dedica menos tiempo a preocuparte por el stock y más tiempo a hacer crecer tu negocio.</p>
                            <a href="{{ route('register') }}" class="mt-8 inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-700">
                                Crear cuenta de negocio
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="border-t border-gray-100 bg-gray-50">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p class="text-sm text-gray-500">StockMype · Tumbes, Perú</p>
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        <a href="{{ route('login') }}" class="font-medium text-gray-600 transition hover:text-emerald-700">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="font-medium text-gray-600 transition hover:text-emerald-700">Crear cuenta de negocio</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
