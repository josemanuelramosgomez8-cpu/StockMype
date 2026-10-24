<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Movimientos de inventario</h1>
            <a href="{{ route('movimientos.create') }}" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Registrar movimiento</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-max w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">Fecha</th>
                                <th class="px-5 py-3 font-medium">Producto</th>
                                <th class="px-5 py-3 font-medium">Tipo</th>
                                <th class="px-5 py-3 font-medium">Cantidad</th>
                                <th class="px-5 py-3 font-medium">Stock resultante</th>
                                <th class="px-5 py-3 font-medium">Registrado por</th>
                                <th class="px-5 py-3 text-right font-medium">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($movimientos as $movimiento)
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4 font-medium text-gray-900">{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</td>
                                    <td class="px-5 py-4"><x-badge :variant="$movimiento->tipo === 'entrada' ? 'success' : ($movimiento->tipo === 'salida' ? 'warning' : 'neutral')">{{ str_replace('_', ' ', $movimiento->tipo) }}</x-badge></td>
                                    <td class="px-5 py-4 text-gray-700">{{ $movimiento->cantidad }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $movimiento->stock_resultante }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $movimiento->user?->name ?? 'Usuario eliminado' }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('movimientos.show', $movimiento) }}" class="font-medium text-emerald-700 hover:underline">Ver</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-3"><x-empty-state title="Aún no hay movimientos registrados." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($movimientos->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $movimientos->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
