<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class CatalogoDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalogosDemo() as $datosNegocio) {
            $negocio = Negocio::firstOrCreate(
                ['nombre' => $datosNegocio['nombre'], 'rubro' => $datosNegocio['rubro']],
                ['direccion' => $datosNegocio['direccion']]
            );

            $categorias = [];

            foreach ($datosNegocio['categorias'] as $nombre) {
                $categorias[$nombre] = Categoria::firstOrCreate([
                    'negocio_id' => $negocio->id,
                    'nombre' => $nombre,
                ]);
            }

            foreach ($datosNegocio['proveedores'] as $datosProveedor) {
                Proveedor::firstOrCreate(
                    ['negocio_id' => $negocio->id, 'nombre' => $datosProveedor['nombre']],
                    ['telefono' => $datosProveedor['telefono'], 'direccion' => $datosProveedor['direccion']]
                );
            }

            foreach ($datosNegocio['productos'] as $datosProducto) {
                $categoria = $categorias[$datosProducto['categoria']];
                $proveedor = Proveedor::query()
                    ->where('negocio_id', $negocio->id)
                    ->where('nombre', $datosProducto['proveedor'])
                    ->firstOrFail();

                Producto::firstOrCreate(
                    ['negocio_id' => $negocio->id, 'sku' => $datosProducto['sku']],
                    [
                        'categoria_id' => $categoria->id,
                        'proveedor_id' => $proveedor->id,
                        'nombre' => $datosProducto['nombre'],
                        'precio_compra' => $datosProducto['precio_compra'],
                        'precio_venta' => $datosProducto['precio_venta'],
                        'stock_actual' => $datosProducto['stock_actual'],
                        'stock_minimo' => $datosProducto['stock_minimo'],
                        'fecha_vencimiento' => $datosProducto['fecha_vencimiento'] ?? null,
                        'unidad_medida' => $datosProducto['unidad_medida'],
                        'activo' => true,
                    ]
                );
            }
        }
    }

    /**
     * Catálogos ficticios para probar rubros comunes de pequeños negocios tumbesinos.
     *
     * @return array<int, array<string, mixed>>
     */
    private function catalogosDemo(): array
    {
        return [
            [
                'nombre' => 'Restaurante Sabor Tumbesino (Demo)',
                'rubro' => 'gastronomia',
                'direccion' => 'Centro de Tumbes (dirección de demostración)',
                'categorias' => ['Pescados y mariscos', 'Abarrotes', 'Bebidas'],
                'proveedores' => [
                    ['nombre' => 'Mercado local Demo', 'telefono' => '900000001', 'direccion' => 'Mercado de Tumbes (Demo)'],
                    ['nombre' => 'Distribuidora Costa Demo', 'telefono' => '900000002', 'direccion' => 'Tumbes (Demo)'],
                ],
                'productos' => [
                    [
                        'sku' => 'DEMO-GAS-001',
                        'nombre' => 'Pescado fresco',
                        'categoria' => 'Pescados y mariscos',
                        'proveedor' => 'Mercado local Demo',
                        'precio_compra' => 18.00,
                        'precio_venta' => 28.00,
                        'stock_actual' => 8,
                        'stock_minimo' => 3,
                        'fecha_vencimiento' => now()->addDays(2)->toDateString(),
                        'unidad_medida' => 'kg',
                    ],
                    [
                        'sku' => 'DEMO-GAS-002',
                        'nombre' => 'Arroz extra',
                        'categoria' => 'Abarrotes',
                        'proveedor' => 'Distribuidora Costa Demo',
                        'precio_compra' => 3.80,
                        'precio_venta' => 5.00,
                        'stock_actual' => 30,
                        'stock_minimo' => 10,
                        'unidad_medida' => 'kg',
                    ],
                    [
                        'sku' => 'DEMO-GAS-003',
                        'nombre' => 'Gaseosa personal',
                        'categoria' => 'Bebidas',
                        'proveedor' => 'Distribuidora Costa Demo',
                        'precio_compra' => 2.00,
                        'precio_venta' => 3.50,
                        'stock_actual' => 24,
                        'stock_minimo' => 6,
                        'unidad_medida' => 'botella',
                    ],
                ],
            ],
            [
                'nombre' => 'Bodega El Vecino Tumbes (Demo)',
                'rubro' => 'retail',
                'direccion' => 'Barrio residencial de Tumbes (dirección de demostración)',
                'categorias' => ['Abarrotes', 'Bebidas', 'Limpieza'],
                'proveedores' => [
                    ['nombre' => 'Mayorista Frontera Demo', 'telefono' => '900000003', 'direccion' => 'Tumbes (Demo)'],
                    ['nombre' => 'Comercial del Norte Demo', 'telefono' => '900000004', 'direccion' => 'Tumbes (Demo)'],
                ],
                'productos' => [
                    [
                        'sku' => 'DEMO-BOD-001',
                        'nombre' => 'Aceite vegetal 1 L',
                        'categoria' => 'Abarrotes',
                        'proveedor' => 'Mayorista Frontera Demo',
                        'precio_compra' => 7.50,
                        'precio_venta' => 9.00,
                        'stock_actual' => 3,
                        'stock_minimo' => 8,
                        'unidad_medida' => 'botella',
                    ],
                    [
                        'sku' => 'DEMO-BOD-002',
                        'nombre' => 'Agua de mesa 625 ml',
                        'categoria' => 'Bebidas',
                        'proveedor' => 'Comercial del Norte Demo',
                        'precio_compra' => 1.00,
                        'precio_venta' => 1.50,
                        'stock_actual' => 48,
                        'stock_minimo' => 12,
                        'unidad_medida' => 'botella',
                    ],
                    [
                        'sku' => 'DEMO-BOD-003',
                        'nombre' => 'Detergente en bolsa',
                        'categoria' => 'Limpieza',
                        'proveedor' => 'Comercial del Norte Demo',
                        'precio_compra' => 4.20,
                        'precio_venta' => 5.50,
                        'stock_actual' => 16,
                        'stock_minimo' => 4,
                        'unidad_medida' => 'unidad',
                    ],
                ],
            ],
            [
                'nombre' => 'Tienda Norteña de Ropa (Demo)',
                'rubro' => 'retail',
                'direccion' => 'Zona comercial de Tumbes (dirección de demostración)',
                'categorias' => ['Polos', 'Pantalones', 'Accesorios'],
                'proveedores' => [
                    ['nombre' => 'Confecciones Perú Demo', 'telefono' => '900000005', 'direccion' => 'Proveedor ficticio de demostración'],
                    ['nombre' => 'Accesorios Norte Demo', 'telefono' => '900000006', 'direccion' => 'Proveedor ficticio de demostración'],
                ],
                'productos' => [
                    [
                        'sku' => 'DEMO-ROP-001',
                        'nombre' => 'Polo de algodón',
                        'categoria' => 'Polos',
                        'proveedor' => 'Confecciones Perú Demo',
                        'precio_compra' => 18.00,
                        'precio_venta' => 32.00,
                        'stock_actual' => 14,
                        'stock_minimo' => 4,
                        'unidad_medida' => 'unidad',
                    ],
                    [
                        'sku' => 'DEMO-ROP-002',
                        'nombre' => 'Pantalón de mezclilla',
                        'categoria' => 'Pantalones',
                        'proveedor' => 'Confecciones Perú Demo',
                        'precio_compra' => 38.00,
                        'precio_venta' => 65.00,
                        'stock_actual' => 7,
                        'stock_minimo' => 2,
                        'unidad_medida' => 'unidad',
                    ],
                    [
                        'sku' => 'DEMO-ROP-003',
                        'nombre' => 'Gorra de tela',
                        'categoria' => 'Accesorios',
                        'proveedor' => 'Accesorios Norte Demo',
                        'precio_compra' => 10.00,
                        'precio_venta' => 18.00,
                        'stock_actual' => 10,
                        'stock_minimo' => 3,
                        'unidad_medida' => 'unidad',
                    ],
                ],
            ],
        ];
    }
}
