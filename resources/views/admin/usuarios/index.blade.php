<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Usuarios del sistema</h1>
            <a href="{{ route('admin.usuarios.create') }}" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600">Nuevo usuario</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            @if ($errors->has('usuario'))
                <div role="alert" class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('usuario') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
                <label class="sr-only" for="buscar">Buscar usuario</label>
                <input id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar nombre o correo" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <label class="sr-only" for="rol">Filtrar por rol</label>
                <select id="rol" name="rol" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos los roles</option>
                    <option value="administrador" @selected(request('rol') === 'administrador')>Administrador</option>
                    <option value="dueno" @selected(request('rol') === 'dueno')>Dueño</option>
                    <option value="empleado" @selected(request('rol') === 'empleado')>Empleado</option>
                </select>
                <label class="sr-only" for="negocio_id">Filtrar por negocio</label>
                <select id="negocio_id" name="negocio_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos los negocios</option>
                    @foreach ($negocios as $negocio)
                        <option value="{{ $negocio->id }}" @selected((string) request('negocio_id') === (string) $negocio->id)>{{ $negocio->nombre }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="estado">Filtrar por estado</label>
                <select id="estado" name="estado" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                    <option value="suspendido" @selected(request('estado') === 'suspendido')>Suspendidos</option>
                </select>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700" type="submit">Filtrar</button>
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">Usuario</th>
                                <th class="px-5 py-3 font-medium">Rol</th>
                                <th class="px-5 py-3 font-medium">Negocio</th>
                                <th class="px-5 py-3 font-medium">Registro</th>
                                <th class="px-5 py-3 font-medium">Estado</th>
                                <th class="px-5 py-3 text-right font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($usuarios as $usuario)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-gray-900">{{ $usuario->name }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $usuario->email }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-700">
                                        {{ match ($usuario->rol) {
                                            'administrador' => 'Administrador',
                                            'dueno' => 'Dueño',
                                            default => 'Empleado',
                                        } }}
                                    </td>
                                    <td class="px-5 py-4 text-gray-700">{{ $usuario->negocio?->nombre ?: 'Sin negocio' }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $usuario->created_at?->format('d/m/Y') }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $usuario->activo ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                            {{ $usuario->activo ? 'Activo' : 'Suspendido' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="font-medium text-indigo-700 hover:underline">Editar</a>
                                            @if (! auth()->user()->is($usuario))
                                                <form method="POST" action="{{ route('admin.usuarios.estado', $usuario) }}" onsubmit="return confirm('¿{{ $usuario->activo ? 'Suspender' : 'activar' }} esta cuenta?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="activo" value="{{ $usuario->activo ? 0 : 1 }}">
                                                    <button class="font-medium {{ $usuario->activo ? 'text-amber-700' : 'text-green-700' }} hover:underline" type="submit">{{ $usuario->activo ? 'Suspender' : 'Activar' }}</button>
                                                </form>
                                            @endif
                                            @if (! auth()->user()->is($usuario))
                                                <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}" onsubmit="return confirm('¿Eliminar este usuario?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="font-medium text-red-700 hover:underline" type="submit">Eliminar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No hay usuarios que coincidan con los filtros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($usuarios->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $usuarios->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
