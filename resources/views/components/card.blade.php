@props(['title' => null, 'description' => null, 'bodyClass' => 'p-5 sm:p-6'])

<section {{ $attributes->class(['overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm']) }}>
    @if ($title || isset($header) || isset($action))
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
            <div>
                @isset($header)
                    {{ $header }}
                @else
                    @if ($title)
                        <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
                    @endif
                    @if ($description)
                        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
                    @endif
                @endisset
            </div>
            @isset($action)
                <div class="shrink-0">{{ $action }}</div>
            @endisset
        </header>
    @endif

    <div @class([$bodyClass])>
        {{ $slot }}
    </div>

    @isset($footer)
        <footer class="border-t border-gray-100 px-5 py-4 sm:px-6">
            {{ $footer }}
        </footer>
    @endisset
</section>
