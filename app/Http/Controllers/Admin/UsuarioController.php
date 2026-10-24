<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsuarioRequest;
use App\Models\Negocio;
use App\Models\User;
use App\Services\RegistrarAuditoriaAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $usuarios = User::query()
            ->with('negocio:id,nombre')
            ->when($request->filled('buscar'), function ($query) use ($request): void {
                $termino = trim((string) $request->input('buscar'));
                $query->where(function ($consulta) use ($termino): void {
                    $consulta->where('name', 'like', "%{$termino}%")
                        ->orWhere('email', 'like', "%{$termino}%");
                });
            })
            ->when($request->filled('rol'), fn ($query) => $query->where('rol', $request->input('rol')))
            ->when($request->filled('negocio_id'), fn ($query) => $query->where('negocio_id', $request->integer('negocio_id')))
            ->when($request->input('estado') === 'activo', fn ($query) => $query->where('activo', true))
            ->when($request->input('estado') === 'suspendido', fn ($query) => $query->where('activo', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'negocios' => Negocio::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.usuarios.form', [
            'usuario' => new User,
            'negocios' => Negocio::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(UsuarioRequest $request, RegistrarAuditoriaAdmin $auditoria): RedirectResponse
    {
        $this->authorize('create', User::class);
        $datos = $request->validated();
        $datos['negocio_id'] = $datos['rol'] === User::ROL_ADMINISTRADOR
            ? null
            : $datos['negocio_id'];

        $usuario = User::create($datos);
        $auditoria->registrar(
            $request,
            'usuario.creado',
            "Creó la cuenta {$usuario->name} ({$usuario->email}).",
            $usuario,
            ['rol' => $usuario->rol, 'negocio_id' => $usuario->negocio_id]
        );

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        $this->authorize('update', $usuario);

        return view('admin.usuarios.form', [
            'usuario' => $usuario,
            'negocios' => Negocio::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(UsuarioRequest $request, User $usuario, RegistrarAuditoriaAdmin $auditoria): RedirectResponse
    {
        $this->authorize('update', $usuario);
        $datos = $request->validated();

        if ($request->user()->is($usuario)) {
            $datos['rol'] = $usuario->rol;
            $datos['negocio_id'] = $usuario->negocio_id;
        } elseif ($datos['rol'] === User::ROL_ADMINISTRADOR) {
            $datos['negocio_id'] = null;
        }

        if (empty($datos['password'])) {
            unset($datos['password']);
        } else {
            $datos['password'] = Hash::make($datos['password']);
        }

        $campos = array_keys($datos);
        $usuario->update($datos);
        $auditoria->registrar(
            $request,
            'usuario.actualizado',
            "Actualizó la cuenta {$usuario->name} ({$usuario->email}).",
            $usuario,
            ['campos' => $campos]
        );

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function cambiarEstado(Request $request, User $usuario, RegistrarAuditoriaAdmin $auditoria): RedirectResponse
    {
        $this->authorize('update', $usuario);
        $datos = $request->validate(['activo' => ['required', 'boolean']]);

        if ($request->user()->is($usuario) && ! $datos['activo']) {
            return back()->withErrors([
                'usuario' => 'No puedes suspender la cuenta de administrador que estás usando.',
            ]);
        }

        if ($usuario->esAdministrador()
            && ! $datos['activo']
            && User::query()->where('rol', User::ROL_ADMINISTRADOR)->where('activo', true)->count() <= 1) {
            return back()->withErrors([
                'usuario' => 'Debe existir al menos un administrador activo del sistema.',
            ]);
        }

        $estadoAnterior = $usuario->activo;
        $usuario->update(['activo' => $datos['activo']]);
        $auditoria->registrar(
            $request,
            $usuario->activo ? 'usuario.activado' : 'usuario.suspendido',
            ($usuario->activo ? 'Activó' : 'Suspendió')." la cuenta {$usuario->name} ({$usuario->email}).",
            $usuario,
            ['activo_anterior' => $estadoAnterior, 'activo_nuevo' => $usuario->activo]
        );

        return redirect()->route('admin.usuarios.index')->with(
            'status',
            $usuario->activo ? 'Usuario activado correctamente.' : 'Usuario suspendido correctamente.'
        );
    }

    public function destroy(Request $request, User $usuario, RegistrarAuditoriaAdmin $auditoria): RedirectResponse
    {
        $this->authorize('delete', $usuario);

        if ($request->user()->is($usuario)) {
            return back()->withErrors([
                'usuario' => 'No puedes eliminar la cuenta de administrador que estás usando.',
            ]);
        }

        if ($usuario->esAdministrador() && User::query()->where('rol', User::ROL_ADMINISTRADOR)->count() <= 1) {
            return back()->withErrors([
                'usuario' => 'Debe existir al menos un administrador del sistema.',
            ]);
        }

        $nombre = $usuario->name;
        $email = $usuario->email;
        $auditoria->registrar(
            $request,
            'usuario.eliminado',
            "Eliminó la cuenta {$nombre} ({$email}).",
            $usuario
        );
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuario eliminado correctamente.');
    }
}
