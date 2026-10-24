<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Movimientos de inventario</h1>
            <a href="{{ route('movimientos.create') }}" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600">Registrar movimiento</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
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
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4 font-medium text-gray-900">{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</td>
                                    <td class="px-5 py-4 capitalize text-gray-700">{{ str_replace('_', ' ', $movimiento->tipo) }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $movimiento->cantidad }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $movimiento->stock_resultante }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $movimiento->user?->name ?? 'Usuario eliminado' }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('movimientos.show', $movimiento) }}" class="font-medium text-indigo-700 hover:underline">Ver</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500">Aún no hay movimientos registrados.</td></tr>
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
