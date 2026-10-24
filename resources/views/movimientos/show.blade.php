<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Detalle del movimiento</h1>
            <a href="{{ route('movimientos.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Volver al historial</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <dl class="grid gap-px overflow-hidden rounded-lg bg-gray-200 shadow-sm sm:grid-cols-2">
                @foreach ([
                    'Producto' => $movimiento->producto?->nombre ?? 'Producto eliminado',
                    'Tipo' => ucfirst($movimiento->tipo),
                    'Cantidad del movimiento' => $movimiento->cantidad,
                    'Stock anterior' => $movimiento->stock_anterior,
                    'Stock resultante' => $movimiento->stock_resultante,
                    'Fecha' => $movimiento->created_at->format('d/m/Y H:i'),
                    'Registrado por' => $movimiento->user?->name ?? 'Usuario eliminado',
                    'Motivo' => $movimiento->motivo,
                ] as $etiqueta => $valor)
                    <div class="bg-white px-5 py-4">
                        <dt class="text-sm text-gray-500">{{ $etiqueta }}</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $valor }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</x-app-layout>
