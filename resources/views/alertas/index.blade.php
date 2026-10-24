<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Alertas de inventario</h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="divide-y divide-gray-100">
                    @forelse ($alertas as $alerta)
                        <article class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $alerta->tipo)) }}</h2>
                                    @if ($alerta->resuelta_at)
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">Normalizada</span>
                                    @else
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $alerta->estado === 'pendiente' ? 'bg-amber-50 text-amber-800' : 'bg-green-50 text-green-700' }}">
                                            {{ $alerta->estado === 'pendiente' ? 'Pendiente' : 'Leída' }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-gray-700">{{ $alerta->mensaje }}</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $alerta->created_at->format('d/m/Y H:i') }}
                                    @if ($alerta->resuelta_at)
                                        · Se normalizó {{ $alerta->resuelta_at->format('d/m/Y H:i') }}
                                    @endif
                                </p>
                            </div>
                            @if ($alerta->estado === 'pendiente')
                                <form method="POST" action="{{ route('alertas.leida', $alerta) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="whitespace-nowrap rounded-md border border-indigo-700 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50">Marcar como leída</button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-gray-500">No hay alertas de inventario.</p>
                    @endforelse
                </div>
                @if ($alertas->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $alertas->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
