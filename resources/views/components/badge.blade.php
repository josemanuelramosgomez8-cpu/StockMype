@props(['variant' => 'neutral'])

@php
    $variantes = [
        'agotado' => 'bg-red-50 text-red-700 ring-red-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        'stock_bajo' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
        'warning' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
        'por_vencer' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'normal' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'neutral' => 'bg-gray-100 text-gray-700 ring-gray-600/20',
    ];
    $clasesVariante = $variantes[$variant] ?? $variantes['neutral'];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset', $clasesVariante]) }}>
    {{ $slot }}
</span>
