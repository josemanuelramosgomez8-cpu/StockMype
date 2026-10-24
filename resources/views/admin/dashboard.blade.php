<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Administración de StockMype
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                <p class="text-gray-600">Gestiona el acceso y revisa la actividad administrativa de StockMype.</p>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['Negocios activos', $negociosActivos.' / '.$totalNegocios, route('admin.negocios.index', ['estado' => 'activo'])],
                        ['Usuarios activos', $usuariosActivos.' / '.$totalUsuarios, route('admin.usuarios.index', ['estado' => 'activo'])],
                        ['Dueños', $totalDueno, route('admin.usuarios.index', ['rol' => 'dueno'])],
                        ['Empleados', $totalEmpleados, route('admin.usuarios.index', ['rol' => 'empleado'])],
                    ] as [$titulo, $total, $enlace])
                        <a href="{{ $enlace }}" class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-emerald-300">
                            <p class="text-sm font-medium text-gray-500">{{ $titulo }}</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $total }}</p>
                            <p class="mt-2 text-sm text-emerald-700">Ver listado</p>
                        </a>
                    @endforeach
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <a href="{{ route('admin.negocios.create') }}" class="rounded-lg bg-emerald-700 p-5 font-semibold text-white hover:bg-emerald-600">
                        Registrar un negocio
                    </a>
                    <a href="{{ route('admin.usuarios.create') }}" class="rounded-lg bg-white p-5 font-semibold text-emerald-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50">
                        Crear un usuario
                    </a>
                </div>

                <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                        <div>
                            <h2 class="font-semibold text-gray-900">Actividad administrativa reciente</h2>
                            <p class="mt-1 text-sm text-gray-500">Cambios de cuentas y negocios; no incluye datos comerciales.</p>
                        </div>
                        <a href="{{ route('admin.auditoria.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Ver auditoría completa</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($actividadReciente as $registro)
                            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $registro->descripcion }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $registro->actor_nombre }} · {{ str_replace('.', ' / ', $registro->evento) }}</p>
                                </div>
                                <time class="text-xs text-gray-500">{{ $registro->created_at->format('d/m/Y H:i') }}</time>
                            </div>
                        @empty
                            <div class="p-3"><x-empty-state title="Aún no hay actividad administrativa registrada." /></div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
