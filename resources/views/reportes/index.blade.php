<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Reportes</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $negocio->nombre }} · consulta y exportación de información</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div role="alert" class="rounded-md bg-red-50 p-4 text-sm text-red-800">
                    Revisa los filtros ingresados. La fecha final debe ser igual o posterior a la fecha inicial.
                </div>
            @endif

            <form method="GET" action="{{ route('reportes.index') }}" class="grid gap-4 rounded-lg bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo de reporte</label>
                    <select id="tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="ventas" @selected($tipo === 'ventas')>Ventas</option>
                        <option value="inventario" @selected($tipo === 'inventario')>Movimientos de inventario</option>
                    </select>
                </div>
                <div>
                    <label for="desde" class="block text-sm font-medium text-gray-700">Desde</label>
                    <input id="desde" name="desde" type="date" value="{{ $desde }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="hasta" class="block text-sm font-medium text-gray-700">Hasta</label>
                    <input id="hasta" name="hasta" type="date" value="{{ $hasta }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button type="submit" class="rounded-md bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">Consultar</button>
                <div class="flex gap-2">
                    <a href="{{ route('reportes.pdf', ['tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta]) }}" class="flex-1 rounded-md border border-indigo-700 px-3 py-2.5 text-center text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Descargar PDF</a>
                    <a href="{{ route('reportes.excel', ['tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta]) }}" class="flex-1 rounded-md bg-green-700 px-3 py-2.5 text-center text-sm font-semibold text-white hover:bg-green-600">Excel</a>
                </div>
            </form>

            @if ($tipo === 'ventas')
                <section aria-label="Resumen de ventas" class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm font-medium text-gray-500">Ventas en el período</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($resumen['cantidad']) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm font-medium text-gray-500">Total vendido</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">S/ {{ number_format($resumen['total'], 2) }}</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="font-semibold text-gray-900">Detalle de ventas</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-max w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-left text-sm text-gray-600">
                                <tr>
                                    <th class="px-5 py-3 font-medium">N.° venta</th>
                                    <th class="px-5 py-3 font-medium">Fecha</th>
                                    <th class="px-5 py-3 font-medium">Atendido por</th>
                                    <th class="px-5 py-3 font-medium">Estado</th>
                                    <th class="px-5 py-3 text-right font-medium">Subtotal</th>
                                    <th class="px-5 py-3 text-right font-medium">Descuento</th>
                                    <th class="px-5 py-3 text-right font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @forelse ($registros as $venta)
                                    <tr class="transition-colors hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">{{ $venta->numero }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                                        <td class="px-5 py-4 text-gray-700">{{ $venta->user?->name ?? 'Usuario eliminado' }}</td>
                                        <td class="px-5 py-4"><x-badge variant="success">{{ ucfirst($venta->estado) }}</x-badge></td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right text-gray-700">S/ {{ number_format((float) $venta->subtotal, 2) }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right text-gray-700">S/ {{ number_format((float) $venta->descuento, 2) }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-gray-900">S/ {{ number_format((float) $venta->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="p-3"><x-empty-state title="No hay ventas en este período." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($registros->hasPages())
                        <div class="border-t border-gray-100 px-5 py-4">{{ $registros->links() }}</div>
                    @endif
                </section>
            @else
                <section aria-label="Resumen de inventario" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm font-medium text-gray-500">Movimientos en el período</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($resumen['cantidad']) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm font-medium text-gray-500">Unidades ingresadas</p>
                        <p class="mt-2 text-3xl font-semibold text-green-700">{{ rtrim(rtrim(number_format($resumen['entradas'], 3, '.', ''), '0'), '.') }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm font-medium text-gray-500">Unidades retiradas</p>
                        <p class="mt-2 text-3xl font-semibold text-red-700">{{ rtrim(rtrim(number_format($resumen['salidas'], 3, '.', ''), '0'), '.') }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm font-medium text-gray-500">Ajustes realizados</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($resumen['ajustes']) }}</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="font-semibold text-gray-900">Movimientos de inventario</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-max w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-left text-sm text-gray-600">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Fecha</th>
                                    <th class="px-5 py-3 font-medium">Producto</th>
                                    <th class="px-5 py-3 font-medium">Tipo</th>
                                    <th class="px-5 py-3 text-right font-medium">Cantidad</th>
                                    <th class="px-5 py-3 text-right font-medium">Stock anterior</th>
                                    <th class="px-5 py-3 text-right font-medium">Stock resultante</th>
                                    <th class="px-5 py-3 font-medium">Motivo</th>
                                    <th class="px-5 py-3 font-medium">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @forelse ($registros as $movimiento)
                                    <tr class="transition-colors hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-5 py-4">
                                            <p class="font-medium text-gray-900">{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</p>
                                            <p class="text-xs text-gray-500">{{ $movimiento->producto?->sku ?: 'Sin SKU' }}</p>
                                        </td>
                                        <td class="px-5 py-4"><x-badge :variant="$movimiento->tipo === 'entrada' ? 'success' : ($movimiento->tipo === 'salida' ? 'warning' : 'neutral')">{{ ucfirst(str_replace('_', ' ', $movimiento->tipo)) }}</x-badge></td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right text-gray-700">{{ $movimiento->cantidad }} {{ $movimiento->producto?->unidad_medida }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right text-gray-700">{{ $movimiento->stock_anterior }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right font-medium text-gray-900">{{ $movimiento->stock_resultante }}</td>
                                        <td class="px-5 py-4 text-gray-700">{{ $movimiento->motivo }}</td>
                                        <td class="px-5 py-4 text-gray-600">{{ $movimiento->user?->name ?? 'Usuario eliminado' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="p-3"><x-empty-state title="No hay movimientos en este período." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($registros->hasPages())
                        <div class="border-t border-gray-100 px-5 py-4">{{ $registros->links() }}</div>
                    @endif
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
