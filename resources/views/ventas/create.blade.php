<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Registrar venta</h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if ($productos->isEmpty())
                <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-800">No hay productos activos disponibles para vender.</div>
            @endif

            @if ($errors->has('productos'))
                <div role="alert" class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('productos') }}</div>
            @endif

            <form method="POST" action="{{ route('ventas.store') }}" class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <div>
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-gray-900">Productos de la venta</h2>
                            <p class="text-sm text-gray-500">El precio se toma del catálogo y el stock se valida al confirmar.</p>
                        </div>
                        <button id="agregar-linea" type="button" class="rounded-md border border-indigo-700 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50" @disabled($productos->isEmpty())>Agregar producto</button>
                    </div>
                    <div id="lineas-productos" class="space-y-3"></div>
                    <x-input-error :messages="$errors->get('productos.*.producto_id')" class="mt-2" />
                    <x-input-error :messages="$errors->get('productos.*.cantidad')" class="mt-2" />
                    <x-input-error :messages="$errors->get('productos.*.descuento')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="observaciones" value="Observaciones (opcional)" />
                    <textarea id="observaciones" name="observaciones" rows="3" maxlength="2000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('observaciones') }}</textarea>
                    <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <a href="{{ route('ventas.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit" @disabled($productos->isEmpty()) class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600 disabled:cursor-not-allowed disabled:opacity-50">Confirmar venta</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const productosVenta = @json($productosParaFormulario);
        const lineasContainer = document.getElementById('lineas-productos');
        let siguienteLinea = 0;

        const agregarLinea = (linea = {}) => {
            const indice = siguienteLinea++;
            const contenedor = document.createElement('div');
            contenedor.className = 'grid gap-3 rounded-md border border-gray-200 p-4 sm:grid-cols-12 sm:items-end';

            const productoWrap = document.createElement('div');
            productoWrap.className = 'sm:col-span-5';
            const productoLabel = document.createElement('label');
            productoLabel.className = 'mb-1 block text-sm font-medium text-gray-700';
            productoLabel.textContent = 'Producto';
            const select = document.createElement('select');
            select.name = `productos[${indice}][producto_id]`;
            select.required = true;
            select.className = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
            const opcionInicial = document.createElement('option');
            opcionInicial.value = '';
            opcionInicial.textContent = 'Selecciona un producto';
            select.append(opcionInicial);
            productosVenta.forEach((producto) => {
                const opcion = document.createElement('option');
                opcion.value = producto.id;
                opcion.textContent = `${producto.nombre} (${producto.sku}) — S/ ${Number(producto.precio).toFixed(2)}`;
                select.append(opcion);
            });
            select.value = linea.producto_id ?? '';
            productoLabel.append(select);
            productoWrap.append(productoLabel);

            const detalle = document.createElement('p');
            detalle.className = 'mt-1 text-xs text-gray-500';
            const actualizarDetalle = () => {
                const producto = productosVenta.find((item) => String(item.id) === select.value);
                detalle.textContent = producto
                    ? `Disponible: ${producto.stock} ${producto.unidad}`
                    : '';
            };
            select.addEventListener('change', actualizarDetalle);
            actualizarDetalle();
            productoWrap.append(detalle);

            const crearCampo = (titulo, nombre, tipo, valor, paso, requerido) => {
                const wrap = document.createElement('div');
                wrap.className = 'sm:col-span-2';
                const label = document.createElement('label');
                label.className = 'mb-1 block text-sm font-medium text-gray-700';
                label.textContent = titulo;
                const input = document.createElement('input');
                input.name = `productos[${indice}][${nombre}]`;
                input.type = tipo;
                input.min = '0';
                input.step = paso;
                input.value = valor;
                input.required = requerido;
                input.className = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
                if (nombre === 'cantidad') {
                    input.min = '0.001';
                }
                label.append(input);
                wrap.append(label);
                return wrap;
            };

            const quitarWrap = document.createElement('div');
            quitarWrap.className = 'sm:col-span-3';
            const quitar = document.createElement('button');
            quitar.type = 'button';
            quitar.textContent = 'Quitar';
            quitar.className = 'rounded-md px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50';
            quitar.addEventListener('click', () => contenedor.remove());
            quitarWrap.append(quitar);

            contenedor.append(
                productoWrap,
                crearCampo('Cantidad', 'cantidad', 'number', linea.cantidad ?? '1', '0.001', true),
                crearCampo('Descuento (S/)', 'descuento', 'number', linea.descuento ?? '0.00', '0.01', false),
                quitarWrap
            );
            lineasContainer.append(contenedor);
        };

        document.getElementById('agregar-linea').addEventListener('click', () => agregarLinea());
        const lineasAnteriores = @json(array_values(old('productos', [])));
        if (lineasAnteriores.length) {
            lineasAnteriores.forEach((linea) => agregarLinea(linea));
        } else if (productosVenta.length) {
            agregarLinea();
        }
    </script>
</x-app-layout>
