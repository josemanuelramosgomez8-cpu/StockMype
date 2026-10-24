<?php

namespace App\Providers;

use App\Models\Alerta;
use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use App\Observers\ProductoObserver;
use App\Policies\AlertaPolicy;
use App\Policies\DatosNegocioPolicy;
use App\Policies\MovimientoInventarioPolicy;
use App\Policies\NegocioPolicy;
use App\Policies\ProductoPolicy;
use App\Policies\UsuarioPolicy;
use App\Policies\VentaPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Negocio::class, NegocioPolicy::class);
        Gate::policy(User::class, UsuarioPolicy::class);
        Gate::policy(Producto::class, ProductoPolicy::class);
        Gate::policy(Alerta::class, AlertaPolicy::class);
        Gate::policy(Categoria::class, DatosNegocioPolicy::class);
        Gate::policy(Proveedor::class, DatosNegocioPolicy::class);
        Gate::policy(MovimientoInventario::class, MovimientoInventarioPolicy::class);
        Gate::policy(Venta::class, VentaPolicy::class);

        Producto::observe(ProductoObserver::class);

        foreach ([DetalleVenta::class] as $modelo) {
            Gate::policy($modelo, DatosNegocioPolicy::class);
        }
    }
}
