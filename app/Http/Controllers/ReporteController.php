<?php

namespace App\Http\Controllers;

use App\Exports\ReporteInventarioExport;
use App\Exports\ReporteVentasExport;
use App\Http\Requests\ReporteRequest;
use App\Models\MovimientoInventario;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteController extends Controller
{
    public function index(ReporteRequest $request): View
    {
        $filtros = $this->filtros($request);
        $negocio = $request->user()->negocio;
        $tipo = $filtros['tipo'];
        $query = $this->consulta($tipo, $negocio->id, $filtros['desde'], $filtros['hasta']);

        $resumen = $tipo === 'ventas'
            ? [
                'cantidad' => (clone $query)->count(),
                'total' => (float) (clone $query)->sum('total'),
            ]
            : [
                'cantidad' => (clone $query)->count(),
                'entradas' => (float) (clone $query)->where('tipo', 'entrada')->sum('cantidad'),
                'salidas' => (float) (clone $query)->where('tipo', 'salida')->sum('cantidad'),
                'ajustes' => (clone $query)->where('tipo', 'ajuste')->count(),
            ];
        $registros = $query->paginate(25)->withQueryString();

        return view('reportes.index', [
            'negocio' => $negocio,
            'tipo' => $tipo,
            'desde' => $filtros['desde'],
            'hasta' => $filtros['hasta'],
            'registros' => $registros,
            'resumen' => $resumen,
        ]);
    }

    public function pdf(ReporteRequest $request): Response
    {
        $filtros = $this->filtros($request);
        $negocio = $request->user()->negocio;
        $registros = $this->consulta($filtros['tipo'], $negocio->id, $filtros['desde'], $filtros['hasta'])
            ->get();

        $pdf = Pdf::loadView('reportes.pdf', [
            'negocio' => $negocio,
            'tipo' => $filtros['tipo'],
            'desde' => $filtros['desde'],
            'hasta' => $filtros['hasta'],
            'registros' => $registros,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->nombreArchivo($filtros['tipo'], 'pdf'));
    }

    public function excel(ReporteRequest $request): BinaryFileResponse
    {
        $filtros = $this->filtros($request);
        $negocioId = $request->user()->negocio_id;
        $export = $filtros['tipo'] === 'ventas'
            ? new ReporteVentasExport($negocioId, $filtros['desde'], $filtros['hasta'])
            : new ReporteInventarioExport($negocioId, $filtros['desde'], $filtros['hasta']);

        return Excel::download($export, $this->nombreArchivo($filtros['tipo'], 'xlsx'));
    }

    private function filtros(ReporteRequest $request): array
    {
        $datos = $request->validated();
        $hoy = CarbonImmutable::today();

        return [
            'tipo' => $datos['tipo'],
            'desde' => $datos['desde'] ?? $hoy->startOfMonth()->toDateString(),
            'hasta' => $datos['hasta'] ?? $hoy->toDateString(),
        ];
    }

    private function consulta(string $tipo, int $negocioId, string $desde, string $hasta): Builder
    {
        if ($tipo === 'ventas') {
            return Venta::query()
                ->with('user:id,name')
                ->where('negocio_id', $negocioId)
                ->whereBetween('fecha_venta', [$desde.' 00:00:00', $hasta.' 23:59:59'])
                ->orderByDesc('fecha_venta');
        }

        return MovimientoInventario::query()
            ->with(['producto:id,nombre,sku,unidad_medida', 'user:id,name'])
            ->where('negocio_id', $negocioId)
            ->whereBetween('created_at', [$desde.' 00:00:00', $hasta.' 23:59:59'])
            ->orderByDesc('created_at');
    }

    private function nombreArchivo(string $tipo, string $extension): string
    {
        return "reporte-{$tipo}-".now()->format('Ymd-His').".{$extension}";
    }
}
