<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Detalle del producto</h1>
            <div class="flex gap-3">
                @if (auth()->user()->esDueno())
                    <a href="{{ route('productos.edit', $producto) }}" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Editar</a>
                @endif
                <a href="{{ route('productos.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Volver al inventario</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <dl class="grid gap-px overflow-hidden rounded-lg bg-gray-200 shadow-sm sm:grid-cols-2">
                @foreach ([
                    'Producto' => $producto->nombre,
                    'SKU' => $producto->sku,
                    'Categoría' => $producto->categoria?->nombre ?: 'Sin categoría',
                    'Proveedor' => $producto->proveedor?->nombre ?: 'Sin proveedor',
                    'Precio de compra' => 'S/ '.number_format((float) $producto->precio_compra, 2),
                    'Precio de venta' => 'S/ '.number_format((float) $producto->precio_venta, 2),
                    'Stock actual' => $producto->stock_actual.' '.$producto->unidad_medida,
                    'Stock mínimo' => $producto->stock_minimo.' '.$producto->unidad_medida,
                    'Vencimiento' => $producto->fecha_vencimiento?->format('d/m/Y') ?: 'No aplica',
                    'Estado' => $producto->activo ? 'Activo' : 'Inactivo',
                ] as $etiqueta => $valor)
                    <div class="bg-white px-5 py-4">
                        <dt class="text-sm text-gray-500">{{ $etiqueta }}</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $valor }}</dd>
                    </div>
                @endforeach
                <div class="bg-white px-5 py-4 sm:col-span-2">
                    <dt class="text-sm text-gray-500">Descripción</dt>
                    <dd class="mt-1 whitespace-pre-line font-medium text-gray-900">{{ $producto->descripcion ?: 'Sin descripción' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>
