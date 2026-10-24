<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Ventas</h1>
            <a href="{{ route('ventas.create') }}" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600">Registrar venta</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-max w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">N.° de venta</th>
                                <th class="px-5 py-3 font-medium">Fecha</th>
                                <th class="px-5 py-3 font-medium">Atendido por</th>
                                <th class="px-5 py-3 font-medium">Estado</th>
                                <th class="px-5 py-3 text-right font-medium">Total</th>
                                <th class="px-5 py-3 text-right font-medium">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($ventas as $venta)
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">{{ $venta->numero }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $venta->user?->name ?? 'Usuario eliminado' }}</td>
                                    <td class="px-5 py-4"><x-badge variant="success">{{ ucfirst($venta->estado) }}</x-badge></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right font-medium text-gray-900">S/ {{ number_format((float) $venta->total, 2) }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('ventas.show', $venta) }}" class="font-medium text-indigo-700 hover:underline">Ver</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-3"><x-empty-state title="Aún no hay ventas registradas." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($ventas->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $ventas->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
