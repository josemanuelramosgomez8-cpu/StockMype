<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Auditoría administrativa</h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('admin.auditoria.index') }}" class="grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-3">
                <label class="sr-only" for="buscar">Buscar en la auditoría</label>
                <input id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar descripción o administrador" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <label class="sr-only" for="evento">Filtrar por evento</label>
                <select id="evento" name="evento" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos los eventos</option>
                    @foreach ($eventos as $evento)
                        <option value="{{ $evento }}" @selected(request('evento') === $evento)>{{ str_replace('.', ' / ', $evento) }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700" type="submit">Filtrar</button>
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-max w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">Fecha</th>
                                <th class="px-5 py-3 font-medium">Administrador</th>
                                <th class="px-5 py-3 font-medium">Evento</th>
                                <th class="px-5 py-3 font-medium">Descripción</th>
                                <th class="px-5 py-3 font-medium">Dirección IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($registros as $registro)
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $registro->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $registro->actor_nombre }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ str_replace('.', ' / ', $registro->evento) }}</td>
                                    <td class="px-5 py-4 text-gray-900">
                                        {{ $registro->descripcion }}
                                        @if ($registro->detalles)
                                            <details class="mt-1 text-xs text-gray-500">
                                                <summary class="cursor-pointer">Ver campos registrados</summary>
                                                <pre class="mt-1 whitespace-pre-wrap">{{ json_encode($registro->detalles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </details>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $registro->ip ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-3"><x-empty-state title="No hay eventos que coincidan con los filtros." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($registros->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $registros->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
