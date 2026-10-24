<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Alertas de inventario</h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="divide-y divide-gray-100">
                    @forelse ($alertas as $alerta)
                        <article class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $alerta->tipo)) }}</h2>
                                    <x-badge :variant="$alerta->tipo">{{ $alerta->tipo === 'stock_bajo' ? 'Stock bajo' : ($alerta->tipo === 'agotado' ? 'Agotado' : 'Por vencer') }}</x-badge>
                                    @if ($alerta->resuelta_at)
                                        <x-badge variant="neutral">Normalizada</x-badge>
                                    @else
                                        <x-badge :variant="$alerta->estado === 'pendiente' ? 'warning' : 'success'">
                                            {{ $alerta->estado === 'pendiente' ? 'Pendiente' : 'Leída' }}
                                        </x-badge>
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
                                    <x-button type="submit" variant="secondary" class="whitespace-nowrap">Marcar como leída</x-button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <div class="p-3"><x-empty-state title="No hay alertas de inventario." description="Las alertas nuevas aparecerán aquí cuando el inventario requiera atención." /></div>
                    @endforelse
                </div>
                @if ($alertas->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $alertas->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
