@props(['title', 'description' => null])

<div {{ $attributes->class(['flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center']) }}>
    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
        @isset($icon)
            {{ $icon }}
        @else
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7m7-7h-8m0 0 3-3m-3 3 3 3" />
            </svg>
        @endisset
    </div>
    <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
    @if ($description)
        <p class="mt-2 max-w-md text-sm leading-6 text-gray-500">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
