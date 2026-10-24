<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiereRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless(
            $request->user() !== null && in_array($request->user()->rol, $roles, true),
            403,
            'No tienes permiso para acceder a esta sección.'
        );

        return $next($request);
    }
}
