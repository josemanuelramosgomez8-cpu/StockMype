<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de {{ $tipo }} - {{ $negocio->nombre }}</title>
    <style>
        @page { margin: 28px 30px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { color: #111827; font-size: 18px; margin: 0 0 5px; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 18px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #eef2ff; color: #3730a3; font-weight: bold; text-align: left; }
        th, td { border: 1px solid #d1d5db; padding: 6px 5px; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .right { text-align: right; }
        .empty { color: #6b7280; padding: 18px; text-align: center; }
        .footer { color: #6b7280; font-size: 8px; margin-top: 14px; text-align: right; }
    </style>
</head>
<body>
    <h1>Reporte de {{ $tipo === 'ventas' ? 'ventas' : 'movimientos de inventario' }}</h1>
    <div class="meta">
        {{ $negocio->nombre }} · Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
        · Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

    @if ($tipo === 'ventas')
        <table>
            <thead>
                <tr>
                    <th>N.° venta</th>
                    <th>Fecha</th>
                    <th>Atendido por</th>
                    <th>Estado</th>
                    <th class="right">Subtotal (S/)</th>
                    <th class="right">Descuento (S/)</th>
                    <th class="right">Total (S/)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registros as $venta)
                    <tr>
                        <td>{{ $venta->numero }}</td>
                        <td>{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                        <td>{{ $venta->user?->name ?? 'Usuario eliminado' }}</td>
                        <td>{{ ucfirst($venta->estado) }}</td>
                        <td class="right">{{ number_format((float) $venta->subtotal, 2) }}</td>
                        <td class="right">{{ number_format((float) $venta->descuento, 2) }}</td>
                        <td class="right">{{ number_format((float) $venta->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No hay ventas en este período.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th>Tipo</th>
                    <th class="right">Cantidad</th>
                    <th class="right">Stock anterior</th>
                    <th class="right">Stock resultante</th>
                    <th>Motivo</th>
                    <th>Registrado por</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registros as $movimiento)
                    <tr>
                        <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</td>
                        <td>{{ $movimiento->producto?->sku ?? '—' }}</td>
                        <td>{{ ucfirst($movimiento->tipo) }}</td>
                        <td class="right">{{ $movimiento->cantidad }} {{ $movimiento->producto?->unidad_medida }}</td>
                        <td class="right">{{ $movimiento->stock_anterior }}</td>
                        <td class="right">{{ $movimiento->stock_resultante }}</td>
                        <td>{{ $movimiento->motivo }}</td>
                        <td>{{ $movimiento->user?->name ?? 'Usuario eliminado' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty">No hay movimientos en este período.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">StockMype · Reporte generado para uso interno del negocio.</div>
</body>
</html>
