<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">{{ $negocio->exists ? 'Editar negocio' : 'Registrar negocio' }}</h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $negocio->exists ? route('admin.negocios.update', $negocio) : route('admin.negocios.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($negocio->exists)
                    @method('PUT')
                @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="nombre" value="Nombre comercial" />
                        <x-text-input id="nombre" name="nombre" class="mt-1 block w-full" :value="old('nombre', $negocio->nombre)" required maxlength="255" />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="razon_social" value="Razón social (opcional)" />
                        <x-text-input id="razon_social" name="razon_social" class="mt-1 block w-full" :value="old('razon_social', $negocio->razon_social)" maxlength="255" />
                        <x-input-error :messages="$errors->get('razon_social')" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="rubro" value="Rubro" />
                        <select id="rubro" name="rubro" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="gastronomia" @selected(old('rubro', $negocio->rubro) === 'gastronomia')>Gastronomía</option>
                            <option value="retail" @selected(old('rubro', $negocio->rubro) === 'retail')>Retail</option>
                        </select>
                        <x-input-error :messages="$errors->get('rubro')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="ruc" value="RUC (opcional)" />
                        <x-text-input id="ruc" name="ruc" class="mt-1 block w-full" :value="old('ruc', $negocio->ruc)" inputmode="numeric" maxlength="11" />
                        <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="direccion" value="Dirección (opcional)" />
                    <x-text-input id="direccion" name="direccion" class="mt-1 block w-full" :value="old('direccion', $negocio->direccion)" maxlength="255" />
                    <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="telefono" value="Teléfono (opcional)" />
                        <x-text-input id="telefono" name="telefono" class="mt-1 block w-full" :value="old('telefono', $negocio->telefono)" maxlength="30" />
                        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Correo del negocio (opcional)" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $negocio->email)" maxlength="255" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <a href="{{ route('admin.negocios.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <x-primary-button>Guardar negocio</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
