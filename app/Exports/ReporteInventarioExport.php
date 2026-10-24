<?php

namespace App\Exports;

use App\Models\MovimientoInventario;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteInventarioExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly int $negocioId,
        private readonly string $desde,
        private readonly string $hasta
    ) {}

    public function query(): Builder
    {
        return MovimientoInventario::query()
            ->with(['producto:id,nombre,sku,unidad_medida', 'user:id,name'])
            ->where('negocio_id', $this->negocioId)
            ->whereBetween('created_at', [
                $this->desde.' 00:00:00',
                $this->hasta.' 23:59:59',
            ])
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Producto',
            'SKU',
            'Tipo',
            'Cantidad',
            'Unidad',
            'Stock anterior',
            'Stock resultante',
            'Motivo',
            'Registrado por',
        ];
    }

    public function map($movimiento): array
    {
        return [
            $movimiento->created_at->format('d/m/Y H:i'),
            $this->textoSeguro($movimiento->producto?->nombre ?? 'Producto eliminado'),
            $this->textoSeguro($movimiento->producto?->sku),
            ucfirst($movimiento->tipo),
            (float) $movimiento->cantidad,
            $this->textoSeguro($movimiento->producto?->unidad_medida),
            (float) $movimiento->stock_anterior,
            (float) $movimiento->stock_resultante,
            $this->textoSeguro($movimiento->motivo),
            $this->textoSeguro($movimiento->user?->name ?? 'Usuario eliminado'),
        ];
    }

    private function textoSeguro(?string $valor): ?string
    {
        return $valor !== null && preg_match('/^\s*[=+\-@]/u', $valor)
            ? "'".$valor
            : $valor;
    }
}
