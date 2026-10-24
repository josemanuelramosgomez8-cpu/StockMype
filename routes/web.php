<?php

use App\Http\Controllers\Admin\AuditoriaController as AdminAuditoriaController;
use App\Http\Controllers\Admin\NegocioController as AdminNegocioController;
use App\Http\Controllers\Admin\UsuarioController as AdminUsuarioController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VentaController;
use App\Models\Negocio;
use App\Models\RegistroAuditoriaAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'cuenta.activa', 'negocio'])
    ->name('dashboard');

Route::middleware(['auth', 'cuenta.activa', 'negocio'])->group(function () {
    Route::get('alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::patch('alertas/{alerta}/leida', [AlertaController::class, 'marcarLeida'])->name('alertas.leida');
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/pdf', [ReporteController::class, 'pdf'])->name('reportes.pdf');
    Route::get('reportes/excel', [ReporteController::class, 'excel'])->name('reportes.excel');

    Route::resource('productos', ProductoController::class)
        ->parameters(['productos' => 'producto']);

    Route::resource('ventas', VentaController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['ventas' => 'venta']);
});

Route::middleware(['auth', 'cuenta.activa', 'negocio', 'rol:dueno'])->group(function () {
    Route::resource('movimientos', MovimientoInventarioController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['movimientos' => 'movimiento']);
});

Route::middleware(['auth', 'cuenta.activa', 'negocio', 'rol:dueno'])->group(function () {
    Route::resource('categorias', CategoriaController::class)
        ->parameters(['categorias' => 'categoria'])
        ->except('show');
    Route::resource('proveedores', ProveedorController::class)
        ->parameters(['proveedores' => 'proveedor'])
        ->except('show');
});

Route::middleware(['auth', 'cuenta.activa'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'cuenta.activa', 'rol:administrador'])
    ->group(function (): void {
        Route::get('/', function () {
            $actividadReciente = RegistroAuditoriaAdmin::query()
                ->with('actor:id,name')
                ->latest()
                ->limit(8)
                ->get();

            return view('admin.dashboard', [
                'totalNegocios' => Negocio::count(),
                'negociosActivos' => Negocio::where('activo', true)->count(),
                'totalUsuarios' => User::count(),
                'usuariosActivos' => User::where('activo', true)->count(),
                'totalDueno' => User::where('rol', User::ROL_DUENO)->count(),
                'totalEmpleados' => User::where('rol', User::ROL_EMPLEADO)->count(),
                'actividadReciente' => $actividadReciente,
            ]);
        })->name('dashboard');

        Route::get('auditoria', [AdminAuditoriaController::class, 'index'])->name('auditoria.index');
        Route::resource('negocios', AdminNegocioController::class)
            ->parameters(['negocios' => 'negocio'])
            ->except('show');
        Route::patch('negocios/{negocio}/estado', [AdminNegocioController::class, 'cambiarEstado'])->name('negocios.estado');
        Route::resource('usuarios', AdminUsuarioController::class)
            ->parameters(['usuarios' => 'usuario'])
            ->except('show');
        Route::patch('usuarios/{usuario}/estado', [AdminUsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');
    });

require __DIR__.'/auth.php';
