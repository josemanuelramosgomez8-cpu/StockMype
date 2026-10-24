<?php

namespace Database\Seeders;

use App\Models\Negocio;
use Illuminate\Database\Seeder;

class NegociosSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->negocios() as $datos) {
            Negocio::updateOrCreate(
                ['nombre' => $datos['nombre']],
                $datos
            );
        }
    }

    /**
     * Datos ficticios inspirados en pequeños negocios de Tumbes.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function negocios(): array
    {
        return [
            'cevicheria' => [
                'nombre' => 'Cevichería Puerto Pizarro',
                'razon_social' => 'Cevichería Puerto Pizarro Demo S.A.C.',
                'ruc' => '20987654321',
                'rubro' => 'gastronomia',
                'direccion' => 'Av. Mariscal Castilla, Tumbes (dirección ficticia)',
                'telefono' => '972345601',
                'email' => 'contacto.cevicheria@stockmype.test',
                'activo' => true,
            ],
            'bodega' => [
                'nombre' => 'Bodega Don Pepe',
                'razon_social' => 'Bodega Don Pepe Demo E.I.R.L.',
                'ruc' => '20987654322',
                'rubro' => 'retail',
                'direccion' => 'Barrio El Recreo, Tumbes (dirección ficticia)',
                'telefono' => '973456702',
                'email' => 'contacto.bodega@stockmype.test',
                'activo' => true,
            ],
            'ropa' => [
                'nombre' => 'Moda Norteña',
                'razon_social' => 'Moda Norteña Demo S.A.C.',
                'ruc' => '20987654323',
                'rubro' => 'retail',
                'direccion' => 'Centro comercial de Tumbes (dirección ficticia)',
                'telefono' => '974567803',
                'email' => 'contacto.moda@stockmype.test',
                'activo' => true,
            ],
        ];
    }
}
