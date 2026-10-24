<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Negocios registrados</h1>
            <a href="{{ route('admin.negocios.create') }}" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600">Nuevo negocio</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            @if ($errors->has('negocio'))
                <div role="alert" class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('negocio') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.negocios.index') }}" class="flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm sm:flex-row">
                <label class="sr-only" for="buscar">Buscar negocio por nombre o RUC</label>
                <input id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por negocio o RUC" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:flex-1">
                <label class="sr-only" for="rubro">Filtrar por rubro</label>
                <select id="rubro" name="rubro" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos los rubros</option>
                    <option value="gastronomia" @selected(request('rubro') === 'gastronomia')>Gastronomía</option>
                    <option value="retail" @selected(request('rubro') === 'retail')>Retail</option>
                </select>
                <label class="sr-only" for="estado">Filtrar por estado</label>
                <select id="estado" name="estado" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                    <option value="suspendido" @selected(request('estado') === 'suspendido')>Suspendidos</option>
                </select>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700" type="submit">Filtrar</button>
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">Negocio</th>
                                <th class="px-5 py-3 font-medium">Rubro</th>
                                <th class="px-5 py-3 font-medium">RUC</th>
                                <th class="px-5 py-3 font-medium">Usuarios</th>
                                <th class="px-5 py-3 font-medium">Productos</th>
                                <th class="px-5 py-3 font-medium">Estado</th>
                                <th class="px-5 py-3 text-right font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($negocios as $negocio)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-gray-900">{{ $negocio->nombre }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $negocio->email ?: $negocio->direccion ?: 'Sin datos de contacto' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-700">{{ $negocio->rubro === 'gastronomia' ? 'Gastronomía' : 'Retail' }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $negocio->ruc ?: '—' }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $negocio->users_count }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $negocio->productos_count }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $negocio->activo ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                            {{ $negocio->activo ? 'Activo' : 'Suspendido' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.negocios.edit', $negocio) }}" class="font-medium text-indigo-700 hover:underline">Editar</a>
                                            <form method="POST" action="{{ route('admin.negocios.estado', $negocio) }}" onsubmit="return confirm('¿{{ $negocio->activo ? 'Suspender' : 'activar' }} este negocio?')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="activo" value="{{ $negocio->activo ? 0 : 1 }}">
                                                <button class="font-medium {{ $negocio->activo ? 'text-amber-700' : 'text-green-700' }} hover:underline" type="submit">{{ $negocio->activo ? 'Suspender' : 'Activar' }}</button>
                                            </form>
                                            @if ($negocio->users_count === 0 && $negocio->productos_count === 0)
                                                <form method="POST" action="{{ route('admin.negocios.destroy', $negocio) }}" onsubmit="return confirm('¿Eliminar este negocio? Esta acción no se puede deshacer.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="font-medium text-red-700 hover:underline" type="submit">Eliminar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500">No hay negocios registrados que coincidan con la búsqueda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($negocios->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $negocios->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
