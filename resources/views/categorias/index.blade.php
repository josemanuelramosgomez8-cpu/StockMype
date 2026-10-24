<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Categorías</h1>
            <a href="{{ route('categorias.create') }}" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600">Nueva categoría</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            @if ($errors->has('categoria'))
                <div role="alert" class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('categoria') }}</div>
            @endif

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">Nombre</th>
                                <th class="px-5 py-3 font-medium">Descripción</th>
                                <th class="px-5 py-3 font-medium">Productos</th>
                                <th class="px-5 py-3 text-right font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($categorias as $categoria)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-gray-900">{{ $categoria->nombre }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $categoria->descripcion ?: '—' }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $categoria->productos_count }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-3">
                                            <a class="font-medium text-indigo-700 hover:underline" href="{{ route('categorias.edit', $categoria) }}">Editar</a>
                                            @if ($categoria->productos_count === 0)
                                                <form method="POST" action="{{ route('categorias.destroy', $categoria) }}" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="font-medium text-red-700 hover:underline" type="submit">Eliminar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Aún no tienes categorías. Crea una para organizar tus productos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($categorias->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $categorias->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
