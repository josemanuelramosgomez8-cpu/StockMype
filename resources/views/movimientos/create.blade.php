<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Registrar movimiento de inventario</h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if ($productos->isEmpty())
                <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-800">No tienes productos activos. Registra un producto antes de crear movimientos.</div>
            @endif

            <form method="POST" action="{{ route('movimientos.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <div>
                    <x-input-label for="producto_id" value="Producto" />
                    <select id="producto_id" name="producto_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Selecciona un producto</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}" @selected(old('producto_id', $productoSeleccionado) == $producto->id)>
                                {{ $producto->nombre }} ({{ $producto->sku }}) — stock: {{ $producto->stock_actual }} {{ $producto->unidad_medida }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('producto_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tipo" value="Tipo de movimiento" />
                    <select id="tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="entrada" @selected(old('tipo') === 'entrada')>Entrada — suma unidades al stock</option>
                        <option value="salida" @selected(old('tipo') === 'salida')>Salida — resta unidades del stock</option>
                        <option value="ajuste" @selected(old('tipo') === 'ajuste')>Ajuste — establece la cantidad final contada</option>
                    </select>
                    <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label id="cantidad-label" for="cantidad" value="Cantidad" />
                    <x-text-input id="cantidad" name="cantidad" type="number" step="0.001" min="0.001" max="999999999" class="mt-1 block w-full" :value="old('cantidad')" required />
                    <p id="cantidad-ayuda" class="mt-1 text-xs text-gray-500">Para ajustes, escribe el stock final contado, no la diferencia.</p>
                    <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="motivo" value="Motivo" />
                    <textarea id="motivo" name="motivo" rows="3" maxlength="255" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej.: compra a proveedor, merma o conteo físico">{{ old('motivo') }}</textarea>
                    <x-input-error :messages="$errors->get('motivo')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <a href="{{ route('movimientos.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit" @disabled($productos->isEmpty()) class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600 disabled:cursor-not-allowed disabled:opacity-50">Guardar movimiento</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const tipoMovimiento = document.getElementById('tipo');
        const cantidadLabel = document.getElementById('cantidad-label');
        const cantidadAyuda = document.getElementById('cantidad-ayuda');
        const actualizarAyuda = () => {
            const esAjuste = tipoMovimiento.value === 'ajuste';
            cantidadLabel.textContent = esAjuste ? 'Stock final contado' : 'Cantidad';
            cantidadAyuda.textContent = esAjuste
                ? 'Escribe la cantidad final que contaste físicamente.'
                : 'La cantidad ingresada se sumará o restará del stock actual.';
        };
        tipoMovimiento.addEventListener('change', actualizarAyuda);
        actualizarAyuda();
    </script>
</x-app-layout>
