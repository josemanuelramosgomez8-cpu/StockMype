<?php

namespace App\Exports;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteVentasExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly int $negocioId,
        private readonly string $desde,
        private readonly string $hasta
    ) {}

    public function query(): Builder
    {
        return Venta::query()
            ->with('user:id,name')
            ->where('negocio_id', $this->negocioId)
            ->whereBetween('fecha_venta', [
                $this->desde.' 00:00:00',
                $this->hasta.' 23:59:59',
            ])
            ->orderBy('fecha_venta')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Número de venta',
            'Fecha',
            'Atendido por',
            'Estado',
            'Subtotal (S/)',
            'Descuento (S/)',
            'Total (S/)',
            'Observaciones',
        ];
    }

    public function map($venta): array
    {
        return [
            $venta->numero,
            $venta->fecha_venta->format('d/m/Y H:i'),
            $this->textoSeguro($venta->user?->name ?? 'Usuario eliminado'),
            ucfirst($venta->estado),
            (float) $venta->subtotal,
            (float) $venta->descuento,
            (float) $venta->total,
            $this->textoSeguro($venta->observaciones),
        ];
    }

    private function textoSeguro(?string $valor): ?string
    {
        return $valor !== null && preg_match('/^\s*[=+\-@]/u', $valor)
            ? "'".$valor
            : $valor;
    }
}
