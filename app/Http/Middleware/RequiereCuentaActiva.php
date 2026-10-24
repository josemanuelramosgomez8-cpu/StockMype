<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequiereCuentaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();
        $usuarioSuspendido = $usuario?->activo === false;
        $negocioSuspendido = $usuario?->negocio_id !== null
            && $usuario->negocio?->activo === false;

        if ($usuario && ($usuarioSuspendido || $negocioSuspendido)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta o negocio se encuentra suspendido. Contacta al administrador de StockMype.',
            ]);
        }

        return $next($request);
    }
}
