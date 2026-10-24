<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroAuditoriaAdmin extends Model
{
    protected $table = 'registros_auditoria_admin';

    protected $fillable = [
        'actor_id',
        'actor_nombre',
        'evento',
        'entidad',
        'entidad_id',
        'descripcion',
        'detalles',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'detalles' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
