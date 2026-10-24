<?php

namespace Database\Seeders;

use App\Models\Negocio;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsuariosDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@stockmype.test'],
            [
                'name' => 'Administrador Demo',
                'password' => 'password',
                'negocio_id' => null,
                'rol' => User::ROL_ADMINISTRADOR,
                'activo' => true,
                'email_verified_at' => now(),
            ]
        );

        $cuentas = [
            'cevicheria' => [
                ['name' => 'María Fernanda Ruiz', 'email' => 'dueno.cevicheria@stockmype.test', 'rol' => User::ROL_DUENO],
                ['name' => 'José Luis Ordinola', 'email' => 'empleado.cevicheria@stockmype.test', 'rol' => User::ROL_EMPLEADO],
            ],
            'bodega' => [
                ['name' => 'Rosa Elena Panta', 'email' => 'dueno.bodega@stockmype.test', 'rol' => User::ROL_DUENO],
                ['name' => 'Carlos Alberto Silva', 'email' => 'empleado.bodega@stockmype.test', 'rol' => User::ROL_EMPLEADO],
            ],
            'ropa' => [
                ['name' => 'Ana Milagros Cruz', 'email' => 'dueno.ropa@stockmype.test', 'rol' => User::ROL_DUENO],
                ['name' => 'Miguel Ángel Feijoo', 'email' => 'empleado.ropa@stockmype.test', 'rol' => User::ROL_EMPLEADO],
            ],
        ];

        foreach ($cuentas as $claveNegocio => $usuarios) {
            $negocio = Negocio::query()
                ->where('nombre', NegociosSeeder::negocios()[$claveNegocio]['nombre'])
                ->firstOrFail();

            foreach ($usuarios as $datos) {
                User::updateOrCreate(
                    ['email' => $datos['email']],
                    [
                        'name' => $datos['name'],
                        'password' => 'password',
                        'negocio_id' => $negocio->id,
                        'rol' => $datos['rol'],
                        'activo' => true,
                        'email_verified_at' => now(),
                    ]
                );
            }
        }
    }
}
