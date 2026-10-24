<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Productos</h1>
            @if (auth()->user()->esDueno())
                <a href="{{ route('productos.create') }}" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600">Nuevo producto</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <form method="GET" action="{{ route('productos.index') }}" class="flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm sm:flex-row">
                <label class="sr-only" for="buscar">Buscar producto</label>
                <input id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o SKU" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:flex-1">
                <label class="sr-only" for="estado">Estado del producto</label>
                <select id="estado" name="estado" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos los estados</option>
                    <option value="activos" @selected(request('estado') === 'activos')>Activos</option>
                    <option value="inactivos" @selected(request('estado') === 'inactivos')>Inactivos</option>
                </select>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700" type="submit">Buscar</button>
                @if (request()->filled('buscar') || request()->filled('estado'))
                    <a class="self-center text-sm text-indigo-700 hover:underline" href="{{ route('productos.index') }}">Limpiar</a>
                @endif
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">Producto</th>
                                <th class="px-5 py-3 font-medium">Categoría</th>
                                <th class="px-5 py-3 font-medium">Precio venta</th>
                                <th class="px-5 py-3 font-medium">Stock</th>
                                <th class="px-5 py-3 font-medium">Estado</th>
                                <th class="px-5 py-3 text-right font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($productos as $producto)
                                <tr>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('productos.show', $producto) }}" class="font-medium text-indigo-700 hover:underline">{{ $producto->nombre }}</a>
                                        <div class="mt-1 text-xs text-gray-500">SKU: {{ $producto->sku }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $producto->categoria?->nombre ?: 'Sin categoría' }}</td>
                                    <td class="px-5 py-4 text-gray-900">S/ {{ number_format((float) $producto->precio_venta, 2) }}</td>
                                    <td class="px-5 py-4">
                                        <span class="{{ (float) $producto->stock_actual <= (float) $producto->stock_minimo ? 'font-semibold text-red-700' : 'text-gray-700' }}">
                                            {{ rtrim(rtrim(number_format((float) $producto->stock_actual, 3, '.', ''), '0'), '.') }}
                                            {{ $producto->unidad_medida }}
                                        </span>
                                        <div class="mt-1 text-xs text-gray-500">Mínimo: {{ rtrim(rtrim(number_format((float) $producto->stock_minimo, 3, '.', ''), '0'), '.') }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $producto->activo ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-3">
                                            <a class="font-medium text-indigo-700 hover:underline" href="{{ route('productos.show', $producto) }}">Ver</a>
                                            @if (auth()->user()->esDueno())
                                                <a class="font-medium text-indigo-700 hover:underline" href="{{ route('productos.edit', $producto) }}">Editar</a>
                                                <form method="POST" action="{{ route('productos.destroy', $producto) }}" onsubmit="return confirm('¿Enviar este producto a la papelera?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="font-medium text-red-700 hover:underline" type="submit">Eliminar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No hay productos que coincidan. Prueba otra búsqueda o registra un producto.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($productos->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $productos->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
