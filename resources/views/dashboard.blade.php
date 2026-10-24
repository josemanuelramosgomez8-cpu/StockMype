<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Panel principal</h1>
                <p class="mt-1 text-sm text-gray-500">{{ auth()->user()->negocio->nombre }} · Resumen al {{ now()->format('d/m/Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('productos.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Ver inventario</a>
                <a href="{{ route('ventas.create') }}" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600">Registrar venta</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section aria-label="Resumen del negocio" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('productos.index') }}" class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-indigo-300">
                    <p class="text-sm font-medium text-gray-500">Productos activos</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($totalProductos) }}</p>
                    <p class="mt-2 text-sm text-indigo-700">Consultar inventario</p>
                </a>
                <a href="{{ route('alertas.index') }}" class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-indigo-300">
                    <p class="text-sm font-medium text-gray-500">Alertas pendientes</p>
                    <p class="mt-2 text-3xl font-semibold {{ $alertasPendientes > 0 ? 'text-amber-700' : 'text-gray-900' }}">{{ number_format($alertasPendientes) }}</p>
                    <p class="mt-2 text-sm text-indigo-700">Revisar alertas</p>
                </a>
                <a href="{{ route('ventas.index') }}" class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-indigo-300">
                    <p class="text-sm font-medium text-gray-500">Ventas de hoy</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">S/ {{ number_format($montoVentasHoy, 2) }}</p>
                    <p class="mt-2 text-sm text-gray-500">{{ $cantidadVentasHoy }} {{ $cantidadVentasHoy === 1 ? 'venta' : 'ventas' }}</p>
                </a>
                <a href="{{ route('ventas.index') }}" class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-indigo-300">
                    <p class="text-sm font-medium text-gray-500">Ventas del mes</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">S/ {{ number_format($montoVentasMes, 2) }}</p>
                    <p class="mt-2 text-sm text-gray-500">{{ $cantidadVentasMes }} {{ $cantidadVentasMes === 1 ? 'venta' : 'ventas' }}</p>
                </a>
            </section>

            <section class="grid gap-6 lg:grid-cols-5">
                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 lg:col-span-3">
                    <div class="mb-4">
                        <h2 class="font-semibold text-gray-900">Ventas de los últimos 7 días</h2>
                        <p class="mt-1 text-sm text-gray-500">Importes de ventas completadas, en soles.</p>
                    </div>
                    <div class="relative h-72">
                        <canvas id="ventas-por-dia-chart" role="img" aria-label="Gráfico de ventas de los últimos 7 días"></canvas>
                    </div>
                    <script type="application/json" id="ventas-por-dia-data">@json($ventasPorDia)</script>
                </div>

                <x-card title="Más vendidos del mes" description="Ordenados por unidades vendidas." class="lg:col-span-2">
                    <x-slot name="action">
                        <a href="{{ route('ventas.index') }}" class="text-sm font-medium text-indigo-700 hover:underline">Ver ventas</a>
                    </x-slot>
                    <ol class="divide-y divide-gray-100">
                        @forelse ($productosMasVendidos as $indice => $producto)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-sm font-semibold text-indigo-700">{{ $indice + 1 }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900">{{ $producto->nombre_producto }}</p>
                                        <p class="text-xs text-gray-500">{{ $producto->sku ?: 'Sin SKU' }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ rtrim(rtrim(number_format((float) $producto->cantidad_vendida, 3, '.', ''), '0'), '.') }}</p>
                                    <p class="text-xs text-gray-500">unid.</p>
                                </div>
                            </li>
                        @empty
                            <li class="py-3"><x-empty-state title="Todavía no hay ventas este mes." /></li>
                        @endforelse
                    </ol>
                </x-card>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-gray-900">Productos críticos</h2>
                        <p class="mt-1 text-sm text-gray-500">Stock bajo, agotados o con vencimiento próximo.</p>
                    </div>
                    <x-badge variant="por_vencer" class="px-3 py-1 text-sm">{{ $totalProductosCriticos }} {{ $totalProductosCriticos === 1 ? 'producto' : 'productos' }}</x-badge>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-max w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">Producto</th>
                                <th class="px-5 py-3 font-medium">Stock actual</th>
                                <th class="px-5 py-3 font-medium">Stock mínimo</th>
                                <th class="px-5 py-3 font-medium">Vencimiento</th>
                                <th class="px-5 py-3 font-medium">Situación</th>
                                <th class="px-5 py-3 text-right font-medium">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($productosCriticos as $producto)
                                @php
                                    $agotado = (float) $producto->stock_actual <= 0;
                                    $stockBajo = (float) $producto->stock_actual > 0 && (float) $producto->stock_actual <= (float) $producto->stock_minimo;
                                    $porVencer = $producto->fecha_vencimiento && $producto->fecha_vencimiento->lte(today()->addDays(7));
                                @endphp
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('productos.show', $producto) }}" class="font-medium text-indigo-700 hover:underline">{{ $producto->nombre }}</a>
                                        <p class="mt-1 text-xs text-gray-500">SKU: {{ $producto->sku }}</p>
                                    </td>
                                    <td class="px-5 py-4 font-medium {{ $agotado || $stockBajo ? 'text-red-700' : 'text-gray-700' }}">{{ $producto->stock_actual }} {{ $producto->unidad_medida }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $producto->stock_minimo }} {{ $producto->unidad_medida }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $producto->fecha_vencimiento?->format('d/m/Y') ?: '—' }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @if ($agotado)
                                                <x-badge variant="agotado">Agotado</x-badge>
                                            @elseif ($stockBajo)
                                                <x-badge variant="stock_bajo">Stock bajo</x-badge>
                                            @endif
                                            @if ($producto->fecha_vencimiento && $producto->fecha_vencimiento->isBefore(today()))
                                                <x-badge variant="agotado">Vencido</x-badge>
                                            @elseif ($porVencer)
                                                <x-badge variant="por_vencer">Por vencer</x-badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if (auth()->user()->esDueno())
                                            <a href="{{ route('movimientos.create', ['producto_id' => $producto->id]) }}" class="font-medium text-indigo-700 hover:underline">Registrar entrada</a>
                                        @else
                                            <a href="{{ route('productos.show', $producto) }}" class="font-medium text-indigo-700 hover:underline">Ver producto</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-3"><x-empty-state title="No hay productos críticos." description="El inventario está en buen estado." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
