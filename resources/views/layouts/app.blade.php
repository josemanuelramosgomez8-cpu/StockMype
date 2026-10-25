<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'StockMype') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900">
        <div x-data="{ sidebarOpen: false, userMenuOpen: false, alertsOpen: false }" class="min-h-screen bg-gray-50">
            <x-toast />
            @include('layouts.navigation')

            <div class="flex min-h-screen flex-col lg:pl-72">
                <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 shadow-sm backdrop-blur">
                    <div class="flex min-h-16 items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <button type="button"
                                @click="sidebarOpen = true"
                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 lg:hidden"
                                aria-label="Abrir menú de navegación">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="min-w-0 flex-1">
                            @isset($header)
                                {{ $header }}
                            @else
                                <h1 class="truncate text-lg font-semibold text-gray-900">StockMype</h1>
                            @endisset
                        </div>

                        @unless (Auth::user()->esAdministrador())
                            @php
                                // Mismo cálculo que el dashboard: alertas pendientes del negocio
                                $alertasSinLeer = Auth::user()->negocio->alertas()->where('estado', 'pendiente')->count();
                                $ultimasNotificaciones = Auth::user()->notifications()->latest()->limit(5)->get();
                            @endphp
                            <div class="relative shrink-0">
                                <button type="button"
                                        @click="alertsOpen = !alertsOpen"
                                        @click.outside="alertsOpen = false"
                                        @keydown.escape.window="alertsOpen = false"
                                        class="relative inline-flex h-11 w-11 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        aria-label="Abrir notificaciones"
                                        :aria-expanded="alertsOpen.toString()">
                                    <svg class="h-6 w-6 {{ $alertasSinLeer > 0 ? 'bell-shake-once motion-reduce:animate-none' : '' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4.5-5.8V4a1.5 1.5 0 0 0-3 0v1.2A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
                                    </svg>
                                    @if ($alertasSinLeer > 0)
                                        <span class="absolute right-1 top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-semibold leading-none text-white">{{ $alertasSinLeer }}</span>
                                    @endif
                                </button>

                                <div x-cloak x-show="alertsOpen"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="translate-y-1 opacity-0"
                                     x-transition:enter-end="translate-y-0 opacity-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="translate-y-0 opacity-100"
                                     x-transition:leave-end="translate-y-1 opacity-0"
                                     class="absolute right-0 z-50 mt-2 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
                                     style="display: none;">
                                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                                        <h2 class="text-sm font-semibold text-gray-900">Notificaciones</h2>
                                        <span class="text-xs text-gray-500">{{ $alertasSinLeer }} sin leer</span>
                                    </div>
                                    <div class="max-h-80 overflow-y-auto">
                                        @forelse ($ultimasNotificaciones as $notificacion)
                                            <a href="{{ route('alertas.index') }}" class="block border-b border-gray-100 px-4 py-3 transition hover:bg-emerald-50 {{ $notificacion->read_at ? 'bg-white' : 'bg-emerald-50/50' }}">
                                                <span class="flex items-start gap-3">
                                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notificacion->read_at ? 'bg-gray-300' : 'bg-emerald-600' }}"></span>
                                                    <span class="min-w-0">
                                                        <span class="block text-sm font-medium text-gray-800">{{ $notificacion->data['mensaje'] ?? 'Tienes una alerta de inventario.' }}</span>
                                                        <span class="mt-1 block text-xs text-gray-500">{{ $notificacion->created_at->diffForHumans() }}</span>
                                                    </span>
                                                </span>
                                            </a>
                                        @empty
                                            <p class="px-4 py-8 text-center text-sm text-gray-500">No tienes notificaciones todavía.</p>
                                        @endforelse
                                    </div>
                                    <a href="{{ route('alertas.index') }}" class="block border-t border-gray-100 px-4 py-3 text-center text-sm font-semibold text-emerald-700 transition hover:bg-gray-50">Ver todas las alertas</a>
                                </div>
                            </div>
                        @endunless

                        <div class="relative shrink-0">
                            <button type="button"
                                    @click="userMenuOpen = !userMenuOpen"
                                    @click.outside="userMenuOpen = false"
                                    @keydown.escape.window="userMenuOpen = false"
                                    class="flex min-h-11 max-w-48 items-center gap-2 rounded-lg px-2 text-left transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 sm:px-3"
                                    aria-label="Abrir menú de usuario"
                                    :aria-expanded="userMenuOpen.toString()">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-800">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                                <span class="hidden min-w-0 sm:block">
                                    <span class="block truncate text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                                    <span class="block truncate text-xs text-gray-500">{{ Auth::user()->esAdministrador() ? 'Administrador' : (Auth::user()->esDueno() ? 'Dueño' : 'Empleado') }}</span>
                                </span>
                                <svg class="hidden h-4 w-4 shrink-0 text-gray-500 sm:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-cloak x-show="userMenuOpen"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="translate-y-1 opacity-0"
                                 x-transition:enter-end="translate-y-0 opacity-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="translate-y-0 opacity-100"
                                 x-transition:leave-end="translate-y-1 opacity-0"
                                 class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl"
                                 style="display: none;">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-3 text-sm text-gray-700 transition hover:bg-emerald-50 hover:text-emerald-800">Mi perfil</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-3 text-left text-sm font-medium text-red-700 transition hover:bg-red-50">Cerrar sesión</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>