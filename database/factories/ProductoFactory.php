<?php

namespace Database\Factories;

use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'sku' => fake()->unique()->bothify('PRD-####'),
            'nombre' => fake()->words(3, true),
            'precio_compra' => fake()->randomFloat(2, 1, 100),
            'precio_venta' => fake()->randomFloat(2, 1, 150),
            'stock_actual' => fake()->randomFloat(3, 0, 100),
            'stock_minimo' => fake()->randomFloat(3, 0, 10),
            'unidad_medida' => 'unidad',
            'activo' => true,
        ];
    }
}
