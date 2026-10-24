<?php

namespace App\Observers;

use App\Models\Producto;
use App\Services\AlertasInventarioService;

class ProductoObserver
{
    public function __construct(private AlertasInventarioService $alertas) {}

    public function created(Producto $producto): void
    {
        $this->alertas->revisar($producto);
    }

    public function updated(Producto $producto): void
    {
        if ($producto->wasChanged(['stock_actual', 'stock_minimo', 'fecha_vencimiento', 'activo'])) {
            $this->alertas->revisar($producto);
        }
    }
}
