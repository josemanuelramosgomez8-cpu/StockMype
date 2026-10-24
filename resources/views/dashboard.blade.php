@php
    // Nombre corto para el saludo (primer nombre). No depende de columnas nuevas.
    $nombreUsuario = \Illuminate\Support\Str::of(auth()->user()->name ?? auth()->user()->nombre ?? '')->before(' ')->toString();
    $hora = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    {{ $saludo }}{{ $nombreUsuario ? ', ' . $nombreUsuario : '' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ auth()->user()->negocio->nombre }} · Resumen al {{ now()->format('d/m/Y') }}
                    @if ($alertasPendientes > 0)
                        · <a href="{{ route('alertas.index') }}" class="font-medium text-amber-700 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600">
                            Tienes {{ $alertasPendientes }} {{ $alertasPendientes === 1 ? 'alerta pendiente' : 'alertas pendientes' }}
                        </a>
                    @else
                        · Todo en orden, sin alertas pendientes
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('productos.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Ver inventario</a>
                <a href="{{ route('ventas.create') }}" class="rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Registrar venta</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Resumen: los contadores son el único movimiento de entrada del panel --}}
            <section aria-label="Resumen del negocio" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('productos.index') }}" class="group rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 transition-colors hover:ring-emerald-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600">
                    <p class="text-sm font-medium text-gray-500">Productos activos</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-gray-900"
                       x-data="animatedCounter({{ $totalProductos }})"
                       x-init="start()"
                       x-text="formatted">{{ number_format($totalProductos) }}</p>
                    <p class="mt-2 text-sm font-medium text-emerald-700 group-hover:underline">Consultar inventario</p>
                </a>

                <a href="{{ route('alertas.index') }}" class="group rounded-lg bg-white p-5 shadow-sm ring-1 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600 {{ $alertasPendientes > 0 ? 'ring-amber-300 hover:ring-amber-400' : 'ring-gray-200 hover:ring-emerald-400' }}">
                    <p class="text-sm font-medium text-gray-500">Alertas pendientes</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums {{ $alertasPendientes > 0 ? 'text-amber-700' : 'text-gray-900' }}"
                       x-data="animatedCounter({{ $alertasPendientes }})"
                       x-init="start()"
                       x-text="formatted">{{ number_format($alertasPendientes) }}</p>
                    <p class="mt-2 text-sm font-medium {{ $alertasPendientes > 0 ? 'text-amber-700' : 'text-emerald-700' }} group-hover:underline">
                        {{ $alertasPendientes > 0 ? 'Revisar alertas' : 'Ver historial de alertas' }}
                    </p>
                </a>

                <a href="{{ route('ventas.index') }}" class="group rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 transition-colors hover:ring-emerald-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600">
                    <p class="text-sm font-medium text-gray-500">Ventas de hoy</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-gray-900"
                       x-data="animatedCounter({{ (float) $montoVentasHoy }}, 2, 'S/ ')"
                       x-init="start()"
                       x-text="formatted">S/ {{ number_format($montoVentasHoy, 2) }}</p>
                    <p class="mt-2 text-sm text-gray-500">
                        <span x-data="animatedCounter({{ $cantidadVentasHoy }})" x-init="start()" x-text="formatted">{{ $cantidadVentasHoy }}</span>
                        {{ $cantidadVentasHoy === 1 ? 'venta' : 'ventas' }}
                    </p>
                </a>

                <a href="{{ route('ventas.index') }}" class="group rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 transition-colors hover:ring-emerald-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600">
                    <p class="text-sm font-medium text-gray-500">Ventas del mes</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-gray-900"
                       x-data="animatedCounter({{ (float) $montoVentasMes }}, 2, 'S/ ')"
                       x-init="start()"
                       x-text="formatted">S/ {{ number_format($montoVentasMes, 2) }}</p>
                    <p class="mt-2 text-sm text-gray-500">
                        <span x-data="animatedCounter({{ $cantidadVentasMes }})" x-init="start()" x-text="formatted">{{ $cantidadVentasMes }}</span>
                        {{ $cantidadVentasMes === 1 ? 'venta' : 'ventas' }}
                    </p>
                </a>
            </section>

            {{-- Gráfico y ranking --}}
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
                        <a href="{{ route('ventas.index') }}" class="text-sm font-medium text-emerald-700 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600">Ver ventas</a>
                    </x-slot>
                    <ol class="divide-y divide-gray-100">
                        @forelse ($productosMasVendidos as $indice => $producto)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    {{-- El primer puesto se destaca; el resto queda en neutro --}}
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold {{ $indice === 0 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $indice + 1 }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900">{{ $producto->nombre_producto }}</p>
                                        <p class="text-xs text-gray-500">{{ $producto->sku ?: 'Sin SKU' }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold tabular-nums text-gray-900">{{ rtrim(rtrim(number_format((float) $producto->cantidad_vendida, 3, '.', ''), '0'), '.') }}</p>
                                    <p class="text-xs text-gray-500">unid.</p>
                                </div>
                            </li>
                        @empty
                            <li class="py-3"><x-empty-state title="Todavía no hay ventas este mes." /></li>
                        @endforelse
                    </ol>
                </x-card>
            </section>

            {{-- Productos críticos --}}
            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-gray-900">Productos críticos</h2>
                        <p class="mt-1 text-sm text-gray-500">Stock bajo, agotados o con vencimiento próximo.</p>
                    </div>
                    <x-badge variant="por_vencer" class="px-3 py-1 text-sm">{{ $totalProductosCriticos }} {{ $totalProductosCriticos === 1 ? 'producto' : 'productos' }}</x-badge>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-max divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th scope="col" class="min-w-56 px-5 py-3 font-medium">Producto</th>
                                <th scope="col" class="min-w-44 px-5 py-3 font-medium">Stock actual</th>
                                <th scope="col" class="min-w-32 px-5 py-3 font-medium">Stock mínimo</th>
                                <th scope="col" class="min-w-32 px-5 py-3 font-medium">Vencimiento</th>
                                <th scope="col" class="min-w-40 px-5 py-3 font-medium">Situación</th>
                                <th scope="col" class="min-w-40 px-5 py-3 text-right font-medium">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($productosCriticos as $producto)
                                @php
                                    $agotado = (float) $producto->stock_actual <= 0;
                                    $stockBajo = (float) $producto->stock_actual > 0 && (float) $producto->stock_actual <= (float) $producto->stock_minimo;
                                    $porVencer = $producto->fecha_vencimiento && $producto->fecha_vencimiento->lte(today()->addDays(7));
                                    $stockPorcentaje = (float) $producto->stock_minimo > 0
                                        ? min(100, max(0, (float) $producto->stock_actual / (float) $producto->stock_minimo * 100))
                                        : ((float) $producto->stock_actual > 0 ? 100 : 0);
                                @endphp
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('productos.show', $producto) }}" class="font-medium text-emerald-700 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600">{{ $producto->nombre }}</a>
                                        <p class="mt-1 text-xs text-gray-500">SKU: {{ $producto->sku }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="font-medium tabular-nums {{ $agotado ? 'text-red-700' : ($stockBajo ? 'text-orange-700' : 'text-gray-700') }}">{{ $producto->stock_actual }} {{ $producto->unidad_medida }}</span>
                                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100"
                                             x-data="{ llenado: false }"
                                             x-init="requestAnimationFrame(() => llenado = true)"
                                             role="progressbar"
                                             aria-label="Nivel de stock de {{ $producto->nombre }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             aria-valuenow="{{ round($stockPorcentaje) }}">
                                            <span class="block h-full rounded-full transition-[width] duration-300 ease-out motion-reduce:transition-none {{ $agotado ? 'bg-red-500' : ($stockBajo ? 'bg-orange-500' : 'bg-emerald-500') }}"
                                                  :style="`width: ${llenado ? {{ $stockPorcentaje }} : 0}%`"></span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 tabular-nums text-gray-700">{{ $producto->stock_minimo }} {{ $producto->unidad_medida }}</td>
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
                                            <a href="{{ route('movimientos.create', ['producto_id' => $producto->id]) }}" class="inline-flex rounded-md border border-emerald-600 px-3 py-1.5 font-medium text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Registrar entrada</a>
                                        @else
                                            <a href="{{ route('productos.show', $producto) }}" class="font-medium text-emerald-700 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600">Ver producto</a>
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