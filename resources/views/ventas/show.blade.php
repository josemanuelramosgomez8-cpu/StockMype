<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Venta {{ $venta->numero }}</h1>
            <a href="{{ route('ventas.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Volver a ventas</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-5 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 rounded-lg bg-white p-5 shadow-sm sm:grid-cols-3">
                <div><p class="text-sm text-gray-500">Fecha</p><p class="mt-1 font-medium text-gray-900">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</p></div>
                <div><p class="text-sm text-gray-500">Atendido por</p><p class="mt-1 font-medium text-gray-900">{{ $venta->user?->name ?? 'Usuario eliminado' }}</p></div>
                <div><p class="text-sm text-gray-500">Estado</p><p class="mt-1 font-medium capitalize text-gray-900">{{ $venta->estado }}</p></div>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-max w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">Producto</th>
                                <th class="px-5 py-3 text-right font-medium">Cantidad</th>
                                <th class="px-5 py-3 text-right font-medium">Precio unitario</th>
                                <th class="px-5 py-3 text-right font-medium">Descuento</th>
                                <th class="px-5 py-3 text-right font-medium">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach ($venta->detalles as $detalle)
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-gray-900">{{ $detalle->nombre_producto }}</p>
                                        @if ($detalle->sku)
                                            <p class="mt-1 text-xs text-gray-500">SKU: {{ $detalle->sku }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right text-gray-700">{{ $detalle->cantidad }}</td>
                                    <td class="px-5 py-4 text-right text-gray-700">S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                                    <td class="px-5 py-4 text-right text-gray-700">S/ {{ number_format((float) $detalle->descuento, 2) }}</td>
                                    <td class="px-5 py-4 text-right font-medium text-gray-900">S/ {{ number_format((float) $detalle->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 bg-gray-50 px-5 py-4">
                    <dl class="ml-auto max-w-xs space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd class="font-medium text-gray-900">S/ {{ number_format((float) $venta->subtotal, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Descuento</dt><dd class="font-medium text-gray-900">S/ {{ number_format((float) $venta->descuento, 2) }}</dd></div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 text-base"><dt class="font-semibold text-gray-900">Total</dt><dd class="font-semibold text-gray-900">S/ {{ number_format((float) $venta->total, 2) }}</dd></div>
                    </dl>
                </div>
            </section>

            @if ($venta->observaciones)
                <section class="rounded-lg bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Observaciones</h2>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $venta->observaciones }}</p>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
