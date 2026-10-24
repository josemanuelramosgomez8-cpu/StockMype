<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiereNegocio
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->negocio_id !== null, 403, 'Tu usuario no está asociado a un negocio.');
        abort_unless($request->user()->negocio?->activo, 403, 'El negocio se encuentra suspendido.');

        return $next($request);
    }
}
