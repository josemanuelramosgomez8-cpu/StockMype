<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CrearAdministrador extends Command
{
    protected $signature = 'stockmype:crear-administrador';

    protected $description = 'Crea un administrador del sistema de forma interactiva';

    public function handle(): int
    {
        $nombre = trim((string) $this->ask('Nombre del administrador'));
        $correo = strtolower(trim((string) $this->ask('Correo electrónico')));
        $contrasena = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
        $confirmacion = (string) $this->secret('Confirma la contraseña');

        $validador = Validator::make([
            'name' => $nombre,
            'email' => $correo,
            'password' => $contrasena,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validador->fails()) {
            foreach ($validador->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($contrasena !== $confirmacion) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        User::create([
            'name' => $nombre,
            'email' => $correo,
            'password' => $contrasena,
            'rol' => User::ROL_ADMINISTRADOR,
            'negocio_id' => null,
        ]);

        $this->info('Administrador creado correctamente.');

        return self::SUCCESS;
    }
}
