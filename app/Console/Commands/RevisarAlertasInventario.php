<?php

namespace App\Console\Commands;

use App\Models\Producto;
use App\Services\AlertasInventarioService;
use Illuminate\Console\Command;

class RevisarAlertasInventario extends Command
{
    protected $signature = 'inventario:revisar-alertas';

    protected $description = 'Revisa stock crítico y productos próximos a vencer.';

    public function handle(AlertasInventarioService $alertas): int
    {
        $revisados = 0;

        Producto::query()
            ->where('activo', true)
            ->whereHas('negocio', fn ($query) => $query->where('activo', true))
            ->chunkById(200, function ($productos) use ($alertas, &$revisados): void {
                foreach ($productos as $producto) {
                    $alertas->revisar($producto);
                    $revisados++;
                }
            });

        $this->info("Revisión completada. Productos revisados: {$revisados}.");

        return self::SUCCESS;
    }
}
