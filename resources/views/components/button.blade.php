@props(['variant' => 'primary', 'type' => 'button'])

@php
    $variantes = [
        'primary' => 'border border-transparent bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 focus-visible:ring-emerald-500',
        'secondary' => 'border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:ring-emerald-500',
        'danger' => 'border border-transparent bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:ring-red-500',
    ];
    $clasesVariante = $variantes[$variant] ?? $variantes['primary'];
@endphp

<button {{ $attributes->class(['inline-flex min-h-11 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50', $clasesVariante])->merge(['type' => $type]) }}>
    {{ $slot }}
</button>
