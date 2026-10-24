<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">{{ $producto->exists ? 'Editar producto' : 'Nuevo producto' }}</h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $producto->exists ? route('productos.update', $producto) : route('productos.store') }}" class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($producto->exists)
                    @method('PUT')
                @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="nombre" value="Nombre del producto" />
                        <x-text-input id="nombre" name="nombre" class="mt-1 block w-full" :value="old('nombre', $producto->nombre)" required maxlength="255" />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="sku" value="SKU / código" />
                        <x-text-input id="sku" name="sku" class="mt-1 block w-full uppercase" :value="old('sku', $producto->sku)" required maxlength="80" />
                        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="descripcion" value="Descripción (opcional)" />
                    <textarea id="descripcion" name="descripcion" rows="3" maxlength="5000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="categoria_id" value="Categoría" />
                        <select id="categoria_id" name="categoria_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Sin categoría</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" @selected((string) old('categoria_id', $producto->categoria_id) === (string) $categoria->id)>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('categoria_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="proveedor_id" value="Proveedor" />
                        <select id="proveedor_id" name="proveedor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Sin proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" @selected((string) old('proveedor_id', $producto->proveedor_id) === (string) $proveedor->id)>{{ $proveedor->nombre }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="precio_compra" value="Precio de compra (S/)" />
                        <x-text-input id="precio_compra" name="precio_compra" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('precio_compra', $producto->precio_compra ?? '0.00')" required />
                        <x-input-error :messages="$errors->get('precio_compra')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="precio_venta" value="Precio de venta (S/)" />
                        <x-text-input id="precio_venta" name="precio_venta" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('precio_venta', $producto->precio_venta)" required />
                        <x-input-error :messages="$errors->get('precio_venta')" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <x-input-label for="stock_actual" value="Stock actual" />
                        <x-text-input id="stock_actual" name="stock_actual" type="number" step="0.001" min="0" class="mt-1 block w-full" :value="$producto->exists ? $producto->stock_actual : old('stock_actual', '0.000')" :disabled="$producto->exists" :required="! $producto->exists" />
                        <x-input-error :messages="$errors->get('stock_actual')" class="mt-2" />
                        @if ($producto->exists)
                            <p class="mt-1 text-xs text-gray-500">Para modificar existencias, registra un movimiento de inventario. Así se conserva el historial.</p>
                        @else
                            <p class="mt-1 text-xs text-gray-500">El stock inicial quedará registrado como una entrada.</p>
                        @endif
                    </div>
                    <div>
                        <x-input-label for="stock_minimo" value="Stock mínimo" />
                        <x-text-input id="stock_minimo" name="stock_minimo" type="number" step="0.001" min="0" class="mt-1 block w-full" :value="old('stock_minimo', $producto->stock_minimo ?? '0.000')" required />
                        <x-input-error :messages="$errors->get('stock_minimo')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="unidad_medida" value="Unidad de medida" />
                        <x-text-input id="unidad_medida" name="unidad_medida" class="mt-1 block w-full" :value="old('unidad_medida', $producto->unidad_medida ?? 'unidad')" required maxlength="30" placeholder="unidad, kg, litro..." />
                        <x-input-error :messages="$errors->get('unidad_medida')" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="fecha_vencimiento" value="Fecha de vencimiento (opcional)" />
                        <x-text-input id="fecha_vencimiento" name="fecha_vencimiento" type="date" class="mt-1 block w-full" :value="old('fecha_vencimiento', $producto->fecha_vencimiento?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('fecha_vencimiento')" class="mt-2" />
                    </div>
                    <label class="mt-7 inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $producto->exists ? $producto->activo : true)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        Producto activo
                    </label>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <a href="{{ route('productos.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <x-primary-button>Guardar producto</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
