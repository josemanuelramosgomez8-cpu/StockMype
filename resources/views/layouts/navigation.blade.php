@php
    $usuario = Auth::user();
    $enlaceSidebar = 'flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-150';
    $enlaceActivo = 'bg-emerald-600 text-white shadow-sm';
    $enlaceInactivo = 'text-gray-300 hover:bg-gray-800 hover:text-white';

    // Mismo cálculo que el dashboard y la campanita: alertas pendientes del negocio
    $alertasPendientesSidebar = $usuario->esAdministrador()
        ? 0
        : $usuario->negocio->alertas()->where('estado', 'pendiente')->count();
@endphp

<div x-cloak
     x-show="sidebarOpen"
     x-transition.opacity
     @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-gray-950/60 lg:hidden"
     aria-hidden="true"
     style="display: none;"></div>

<aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-gray-800 bg-gray-900 text-white shadow-xl transition-transform duration-300 ease-in-out lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       aria-label="Navegación principal">
    <div class="flex h-20 shrink-0 items-center justify-between border-b border-gray-800 px-5">
        <a href="{{ $usuario->esAdministrador() ? route('admin.dashboard') : route('dashboard') }}" class="flex min-w-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3.5 7.7 8.5 4.2 8.5-4.2M12 12v8.5M7.5 5.3l9 4.5" />
                </svg>
            </span>
            <span class="truncate text-lg font-bold tracking-tight">StockMype</span>
        </a>
        <button type="button"
                @click="sidebarOpen = false"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400 lg:hidden"
                aria-label="Cerrar menú de navegación">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 18 12-12M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5" aria-label="Secciones">
        @if ($usuario->esAdministrador())
            <a href="{{ route('admin.dashboard') }}" @class([$enlaceSidebar, request()->routeIs('admin.dashboard') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.5h8V3H3v10.5ZM13 21h8V10h-8v11ZM13 3v5h8V3h-8ZM3 21h8v-5H3v5Z" />
                </svg>
                <span>Administración</span>
            </a>
            <a href="{{ route('admin.negocios.index') }}" @class([$enlaceSidebar, request()->routeIs('admin.negocios.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M8 9v.01M8 12v.01M8 15v.01M8 18v.01M14 9v.01M14 12v.01M14 15v.01M14 18v.01M17 14v.01M17 17v.01" />
                </svg>
                <span>Negocios</span>
            </a>
            <a href="{{ route('admin.usuarios.index') }}" @class([$enlaceSidebar, request()->routeIs('admin.usuarios.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m12-13a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm2 2a4 4 0 0 1 0 8m2 3v-2a4 4 0 0 0-3-3.87" />
                </svg>
                <span>Usuarios</span>
            </a>
            <a href="{{ route('admin.auditoria.index') }}" @class([$enlaceSidebar, request()->routeIs('admin.auditoria.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Auditoría</span>
            </a>
        @else
            <a href="{{ route('dashboard') }}" @class([$enlaceSidebar, request()->routeIs('dashboard') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" />
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('productos.index') }}" @class([$enlaceSidebar, request()->routeIs('productos.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4 7 8-4 8 4-8 4-8-4Zm0 5 8 4 8-4m-16 5 8 4 8-4M4 7v10m16-10v10m-8-6v10" />
                </svg>
                <span>Productos</span>
            </a>
            @if ($usuario->esDueno())
                <a href="{{ route('categorias.index') }}" @class([$enlaceSidebar, request()->routeIs('categorias.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                    <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7m7-7h-8m0 0 3-3m-3 3 3 3m3 4h.01M8 8h.01M8 12h.01M8 16h.01" />
                    </svg>
                    <span>Categorías</span>
                </a>
                <a href="{{ route('proveedores.index') }}" @class([$enlaceSidebar, request()->routeIs('proveedores.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                    <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h11v11H3V7Zm11 4h4l3 3v4h-7v-7Zm-7 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                    </svg>
                    <span>Proveedores</span>
                </a>
                <a href="{{ route('movimientos.index') }}" @class([$enlaceSidebar, request()->routeIs('movimientos.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                    <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h14m0 0-4-4m4 4-4 4M17 17H3m0 0 4 4m-4-4 4-4" />
                    </svg>
                    <span>Movimientos</span>
                </a>
            @endif
            <a href="{{ route('ventas.index') }}" @class([$enlaceSidebar, request()->routeIs('ventas.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l2.4 12.2A2 2 0 0 0 9.4 17h8.8a2 2 0 0 0 2-1.6L22 8H6m4 13h.01M18 21h.01" />
                </svg>
                <span>Ventas</span>
            </a>
            <a href="{{ route('alertas.index') }}" @class([$enlaceSidebar, request()->routeIs('alertas.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4.5-5.8V4a1.5 1.5 0 0 0-3 0v1.2A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
                </svg>
                <span class="flex-1">Alertas</span>
                @if ($alertasPendientesSidebar > 0)
                    <span class="inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-semibold leading-none text-white">{{ $alertasPendientesSidebar }}</span>
                @endif
            </a>
            <a href="{{ route('reportes.index', ['tipo' => 'ventas']) }}" @class([$enlaceSidebar, request()->routeIs('reportes.*') ? $enlaceActivo : $enlaceInactivo]) @click="sidebarOpen = false">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2V7m3 10v-5m2 9H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z" />
                </svg>
                <span>Reportes</span>
            </a>
        @endif
    </nav>

    <div class="shrink-0 border-t border-gray-800 p-4">
        <div class="mb-3 flex min-w-0 items-center gap-3 px-1">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-700 text-sm font-semibold text-white">{{ mb_strtoupper(mb_substr($usuario->name, 0, 1)) }}</span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold text-white">{{ $usuario->name }}</span>
                <span class="block text-xs text-gray-400">{{ $usuario->esAdministrador() ? 'Administrador' : ($usuario->esDueno() ? 'Dueño' : 'Empleado') }}</span>
            </span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex min-h-11 w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4m-4-4 4-5-4-5m4 5H3" />
                </svg>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>