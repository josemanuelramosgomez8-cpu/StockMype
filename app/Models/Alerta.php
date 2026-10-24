<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    use HasFactory;

    public const TIPO_STOCK_BAJO = 'stock_bajo';

    public const TIPO_AGOTADO = 'agotado';

    public const TIPO_POR_VENCER = 'por_vencer';

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_LEIDA = 'leida';

    protected $fillable = [
        'negocio_id',
        'producto_id',
        'tipo',
        'estado',
        'mensaje',
        'leida_at',
        'resuelta_at',
    ];

    protected function casts(): array
    {
        return [
            'leida_at' => 'datetime',
            'resuelta_at' => 'datetime',
        ];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
