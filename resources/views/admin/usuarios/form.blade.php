<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">{{ $usuario->exists ? 'Editar usuario' : 'Crear usuario' }}</h1>
    </x-slot>

    @php($esUsuarioActual = $usuario->exists && auth()->user()->is($usuario))

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $usuario->exists ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($usuario->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="name" value="Nombre completo" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $usuario->name)" required maxlength="255" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="Correo electrónico" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $usuario->email)" required maxlength="255" autocomplete="email" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                @if ($esUsuarioActual)
                    <input type="hidden" name="rol" value="{{ $usuario->rol }}">
                    @if ($usuario->negocio_id)
                        <input type="hidden" name="negocio_id" value="{{ $usuario->negocio_id }}">
                    @endif
                    <div class="rounded-md bg-blue-50 p-3 text-sm text-blue-800">Por seguridad, no puedes cambiar tu propio rol o negocio desde esta pantalla.</div>
                @else
                    <div>
                        <x-input-label for="rol" value="Rol del usuario" />
                        <select id="rol" name="rol" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="administrador" @selected(old('rol', $usuario->rol) === 'administrador')>Administrador del sistema</option>
                            <option value="dueno" @selected(old('rol', $usuario->rol) === 'dueno')>Dueño de negocio</option>
                            <option value="empleado" @selected(old('rol', $usuario->rol) === 'empleado')>Empleado</option>
                        </select>
                        <x-input-error :messages="$errors->get('rol')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="negocio_id" value="Negocio" />
                        <select id="negocio_id" name="negocio_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Sin negocio (solo administradores)</option>
                            @foreach ($negocios as $negocio)
                                <option value="{{ $negocio->id }}" @selected((string) old('negocio_id', $usuario->negocio_id) === (string) $negocio->id)>{{ $negocio->nombre }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('negocio_id')" class="mt-2" />
                    </div>
                @endif

                <div>
                    <x-input-label for="password" :value="$usuario->exists ? 'Nueva contraseña (opcional)' : 'Contraseña inicial'" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! $usuario->exists" autocomplete="new-password" />
                    <p class="mt-1 text-xs text-gray-500">{{ $usuario->exists ? 'Déjalo en blanco para conservar la contraseña actual.' : 'Usa una contraseña segura; el usuario podrá cambiarla luego.' }}</p>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <a href="{{ route('admin.usuarios.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <x-primary-button>Guardar usuario</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
