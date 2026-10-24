<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">{{ $proveedor->exists ? 'Editar proveedor' : 'Nuevo proveedor' }}</h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $proveedor->exists ? route('proveedores.update', $proveedor) : route('proveedores.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($proveedor->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="nombre" value="Nombre del proveedor" />
                    <x-text-input id="nombre" name="nombre" class="mt-1 block w-full" :value="old('nombre', $proveedor->nombre)" required maxlength="255" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="ruc" value="RUC (opcional)" />
                    <x-text-input id="ruc" name="ruc" class="mt-1 block w-full" :value="old('ruc', $proveedor->ruc)" inputmode="numeric" maxlength="11" />
                    <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="contacto" value="Persona de contacto (opcional)" />
                    <x-text-input id="contacto" name="contacto" class="mt-1 block w-full" :value="old('contacto', $proveedor->contacto)" maxlength="255" />
                    <x-input-error :messages="$errors->get('contacto')" class="mt-2" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="telefono" value="Teléfono (opcional)" />
                        <x-text-input id="telefono" name="telefono" class="mt-1 block w-full" :value="old('telefono', $proveedor->telefono)" maxlength="30" />
                        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Correo (opcional)" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $proveedor->email)" maxlength="255" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="direccion" value="Dirección (opcional)" />
                    <x-text-input id="direccion" name="direccion" class="mt-1 block w-full" :value="old('direccion', $proveedor->direccion)" maxlength="255" />
                    <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('proveedores.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <x-primary-button>Guardar proveedor</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
