<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">{{ $categoria->exists ? 'Editar categoría' : 'Nueva categoría' }}</h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $categoria->exists ? route('categorias.update', $categoria) : route('categorias.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($categoria->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" name="nombre" class="mt-1 block w-full" :value="old('nombre', $categoria->nombre)" required maxlength="255" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="descripcion" value="Descripción (opcional)" />
                    <textarea id="descripcion" name="descripcion" rows="4" maxlength="2000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('categorias.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <x-primary-button>Guardar categoría</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
