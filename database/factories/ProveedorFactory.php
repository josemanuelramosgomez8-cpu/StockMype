<?php

namespace Database\Factories;

use App\Models\Negocio;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'nombre' => fake()->company(),
            'ruc' => fake()->unique()->numerify('###########'),
            'contacto' => fake()->name(),
            'telefono' => fake()->numerify('9########'),
            'email' => fake()->companyEmail(),
            'direccion' => fake()->address(),
        ];
    }
}
