<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NegociosSeeder::class,
            UsuariosDemoSeeder::class,
            ProductosSeeder::class,
            VentasSeeder::class,
            AlertasSeeder::class,
        ]);
    }
}
