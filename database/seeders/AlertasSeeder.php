<?php

namespace Database\Seeders;

use App\Models\Negocio;
use App\Models\Producto;
use App\Services\AlertasInventarioService;
use Illuminate\Database\Seeder;

class AlertasSeeder extends Seeder
{
    private const PREFIJOS = [
        'cevicheria' => 'CEV',
        'bodega' => 'BOD',
        'ropa' => 'ROP',
    ];

    public function run(): void
    {
        $servicio = app(AlertasInventarioService::class);

        foreach (NegociosSeeder::negocios() as $clave => $datosNegocio) {
            $negocio = Negocio::query()->where('nombre', $datosNegocio['nombre'])->firstOrFail();
            $productos = Producto::query()
                ->where('negocio_id', $negocio->id)
                ->where('sku', 'like', self::PREFIJOS[$clave].'-%')
                ->where('activo', true)
                ->get();

            foreach ($productos as $producto) {
                $servicio->revisar($producto);
            }
        }
    }
}
