<?php

namespace Database\Factories;

use App\Models\Negocio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Negocio>
 */
class NegocioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'rubro' => fake()->randomElement(['gastronomia', 'retail']),
            'direccion' => fake()->address(),
            'telefono' => fake()->numerify('9########'),
            'activo' => true,
        ];
    }
}
