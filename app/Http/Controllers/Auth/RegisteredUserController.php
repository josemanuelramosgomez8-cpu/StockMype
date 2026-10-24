<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistroNegocioRequest;
use App\Models\Negocio;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegistroNegocioRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        [, $user] = DB::transaction(function () use ($datos): array {
            $negocio = Negocio::create([
                'nombre' => $datos['negocio_nombre'],
                'rubro' => $datos['rubro'],
                'ruc' => $datos['ruc'] ?? null,
            ]);

            $user = $negocio->users()->create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => $datos['password'],
                'rol' => User::ROL_DUENO,
            ]);

            return [$negocio, $user];
        });

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect(route('dashboard', absolute: false));
    }
}
