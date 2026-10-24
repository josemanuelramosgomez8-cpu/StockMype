@php
    $mensajeExito = session('success') ?? session('status');
    $mensajeError = session('error');
    $mensajesConocidos = [
        'profile-updated' => 'Perfil actualizado correctamente.',
        'password-updated' => 'Contraseña actualizada correctamente.',
        'verification-link-sent' => 'Se envió un nuevo enlace de verificación.',
    ];
    $mensajeExito = $mensajesConocidos[$mensajeExito] ?? $mensajeExito;
    $mensaje = $mensajeError ?: $mensajeExito;
    $esError = (bool) $mensajeError;
@endphp

@if ($mensaje)
    <div class="pointer-events-none fixed inset-x-0 top-4 z-[60] flex justify-center px-4 sm:justify-end sm:px-6 lg:px-8"
         x-data="{ visible: true }"
         x-init="setTimeout(() => visible = false, 4000)">
        <div x-cloak
             x-show="visible"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-2 opacity-0"
             role="{{ $esError ? 'alert' : 'status' }}"
             class="pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-xl border p-4 shadow-lg {{ $esError ? 'border-red-200 bg-white text-red-800' : 'border-emerald-200 bg-white text-emerald-800' }}">
            <span class="mt-0.5 shrink-0">
                @if ($esError)
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                    </svg>
                @else
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" />
                    </svg>
                @endif
            </span>
            <p class="min-w-0 flex-1 text-sm font-medium leading-5">{{ $mensaje }}</p>
            <button type="button"
                    @click="visible = false"
                    class="-mr-1 -mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                    aria-label="Cerrar mensaje">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    </div>
@endif
