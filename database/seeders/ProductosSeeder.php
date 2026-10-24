<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProductosSeeder extends Seeder
{
    private const PREFIJOS = [
        'cevicheria' => 'CEV',
        'bodega' => 'BOD',
        'ropa' => 'ROP',
    ];

    private const STOCKS_FINALES = [
        0, 0, 2, 3, 4, 20, 18, 18, 18, 15,
        18, 20, 10, 12, 8, 10, 9, 12, 8, 8,
    ];

    public function run(): void
    {
        foreach (NegociosSeeder::negocios() as $clave => $datosNegocio) {
            $negocio = Negocio::query()->where('nombre', $datosNegocio['nombre'])->firstOrFail();
            $catalogo = $this->catalogos()[$clave];
            $categorias = [];

            foreach ($catalogo['categorias'] as $nombre => $descripcion) {
                $categorias[$nombre] = Categoria::updateOrCreate(
                    ['negocio_id' => $negocio->id, 'nombre' => $nombre],
                    ['descripcion' => $descripcion]
                );
            }

            $proveedores = [];
            foreach ($catalogo['proveedores'] as $proveedor) {
                $proveedores[$proveedor['nombre']] = Proveedor::updateOrCreate(
                    ['negocio_id' => $negocio->id, 'nombre' => $proveedor['nombre']],
                    [
                        'contacto' => $proveedor['contacto'],
                        'telefono' => $proveedor['telefono'],
                        'email' => $proveedor['email'],
                        'direccion' => $proveedor['direccion'],
                    ]
                );
            }

            foreach ($catalogo['productos'] as $indice => $producto) {
                $sku = self::PREFIJOS[$clave].'-'.str_pad((string) ($indice + 1), 3, '0', STR_PAD_LEFT);
                $categoria = $categorias[$producto['categoria']];
                $proveedor = $proveedores[$producto['proveedor']];

                $registroProducto = Producto::withTrashed()->updateOrCreate(
                    ['negocio_id' => $negocio->id, 'sku' => $sku],
                    [
                        'categoria_id' => $categoria->id,
                        'proveedor_id' => $proveedor->id,
                        'nombre' => $producto['nombre'],
                        'descripcion' => $producto['descripcion'] ?? null,
                        'precio_compra' => $producto['precio_compra'],
                        'precio_venta' => $producto['precio_venta'],
                        'stock_actual' => 50,
                        'stock_minimo' => $producto['stock_minimo'],
                        'fecha_vencimiento' => isset($producto['vence_en_dias'])
                            ? now()->addDays($producto['vence_en_dias'])->toDateString()
                            : null,
                        'unidad_medida' => $producto['unidad_medida'],
                        'activo' => true,
                    ]
                );

                if ($registroProducto->trashed()) {
                    $registroProducto->restore();
                }

                if ((float) $registroProducto->stock_actual !== 50.0) {
                    $registroProducto->stock_actual = 50;
                    $registroProducto->save();
                }
            }
        }
    }

    public static function stockFinalParaSku(string $sku): float
    {
        $separador = strrpos($sku, '-');
        $indice = $separador === false ? 0 : (int) substr($sku, $separador + 1);

        return self::STOCKS_FINALES[$indice - 1] ?? 10;
    }

    /**
     * Catálogos de demostración; cada teléfono usa el formato peruano 9XXXXXXXX.
     *
     * @return array<string, array<string, mixed>>
     */
    private function catalogos(): array
    {
        return [
            'cevicheria' => [
                'categorias' => [
                    'Pescados y mariscos' => 'Productos frescos del mar para la cocina.',
                    'Verduras y tubérculos' => 'Verduras y acompañamientos de preparación diaria.',
                    'Abarrotes' => 'Insumos secos y condimentos.',
                    'Bebidas' => 'Bebidas personales y para compartir.',
                    'Insumos de cocina' => 'Productos complementarios para el servicio.',
                ],
                'proveedores' => [
                    ['nombre' => 'Pescadores de Puerto Pizarro', 'contacto' => 'Víctor Córdova', 'telefono' => '912345601', 'email' => 'ventas.pescadores@stockmype.test', 'direccion' => 'Puerto Pizarro, Tumbes (ficticia)'],
                    ['nombre' => 'Mercado Modelo Tumbes', 'contacto' => 'Carmen Dioses', 'telefono' => '923456702', 'email' => 'pedidos.mercado@stockmype.test', 'direccion' => 'Centro de Tumbes (ficticia)'],
                    ['nombre' => 'Distribuidora Frontera Norte', 'contacto' => 'Pedro Saldarriaga', 'telefono' => '934567803', 'email' => 'ventas.frontera@stockmype.test', 'direccion' => 'Tumbes (ficticia)'],
                ],
                'productos' => [
                    ['nombre' => 'Corvina fresca', 'categoria' => 'Pescados y mariscos', 'proveedor' => 'Pescadores de Puerto Pizarro', 'precio_compra' => 26, 'precio_venta' => 38, 'unidad_medida' => 'kg', 'stock_minimo' => 5],
                    ['nombre' => 'Lenguado fresco', 'categoria' => 'Pescados y mariscos', 'proveedor' => 'Pescadores de Puerto Pizarro', 'precio_compra' => 32, 'precio_venta' => 48, 'unidad_medida' => 'kg', 'stock_minimo' => 5],
                    ['nombre' => 'Pota fresca', 'categoria' => 'Pescados y mariscos', 'proveedor' => 'Pescadores de Puerto Pizarro', 'precio_compra' => 10, 'precio_venta' => 16, 'unidad_medida' => 'kg', 'stock_minimo' => 6],
                    ['nombre' => 'Conchas negras', 'categoria' => 'Pescados y mariscos', 'proveedor' => 'Pescadores de Puerto Pizarro', 'precio_compra' => 18, 'precio_venta' => 28, 'unidad_medida' => 'docena', 'stock_minimo' => 7],
                    ['nombre' => 'Limón sutil', 'categoria' => 'Verduras y tubérculos', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 2.50, 'precio_venta' => 4, 'unidad_medida' => 'kg', 'stock_minimo' => 8],
                    ['nombre' => 'Ají limo', 'categoria' => 'Verduras y tubérculos', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 6, 'precio_venta' => 10, 'unidad_medida' => 'kg', 'stock_minimo' => 5, 'vence_en_dias' => 5],
                    ['nombre' => 'Cebolla roja', 'categoria' => 'Verduras y tubérculos', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 2.50, 'precio_venta' => 4, 'unidad_medida' => 'kg', 'stock_minimo' => 8, 'vence_en_dias' => 7],
                    ['nombre' => 'Camote amarillo', 'categoria' => 'Verduras y tubérculos', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 1.80, 'precio_venta' => 3, 'unidad_medida' => 'kg', 'stock_minimo' => 8],
                    ['nombre' => 'Choclo desgranado', 'categoria' => 'Verduras y tubérculos', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 2, 'precio_venta' => 4, 'unidad_medida' => 'kg', 'stock_minimo' => 7],
                    ['nombre' => 'Cancha serrana', 'categoria' => 'Abarrotes', 'proveedor' => 'Distribuidora Frontera Norte', 'precio_compra' => 8, 'precio_venta' => 13, 'unidad_medida' => 'kg', 'stock_minimo' => 5],
                    ['nombre' => 'Cerveza rubia 620 ml', 'categoria' => 'Bebidas', 'proveedor' => 'Distribuidora Frontera Norte', 'precio_compra' => 4.20, 'precio_venta' => 6.50, 'unidad_medida' => 'botella', 'stock_minimo' => 6],
                    ['nombre' => 'Gaseosa familiar 1.5 L', 'categoria' => 'Bebidas', 'proveedor' => 'Distribuidora Frontera Norte', 'precio_compra' => 4, 'precio_venta' => 6, 'unidad_medida' => 'botella', 'stock_minimo' => 6],
                    ['nombre' => 'Culantro fresco', 'categoria' => 'Verduras y tubérculos', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 2, 'precio_venta' => 4, 'unidad_medida' => 'manojo', 'stock_minimo' => 3],
                    ['nombre' => 'Lechuga regional', 'categoria' => 'Verduras y tubérculos', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 2.50, 'precio_venta' => 4, 'unidad_medida' => 'unidad', 'stock_minimo' => 3],
                    ['nombre' => 'Yuca fresca', 'categoria' => 'Verduras y tubérculos', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 2, 'precio_venta' => 3.50, 'unidad_medida' => 'kg', 'stock_minimo' => 5],
                    ['nombre' => 'Kion', 'categoria' => 'Verduras y tubérculos', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 8, 'precio_venta' => 14, 'unidad_medida' => 'kg', 'stock_minimo' => 3],
                    ['nombre' => 'Ajo molido', 'categoria' => 'Abarrotes', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 10, 'precio_venta' => 17, 'unidad_medida' => 'kg', 'stock_minimo' => 4],
                    ['nombre' => 'Ají amarillo', 'categoria' => 'Abarrotes', 'proveedor' => 'Mercado Modelo Tumbes', 'precio_compra' => 5, 'precio_venta' => 9, 'unidad_medida' => 'kg', 'stock_minimo' => 4],
                    ['nombre' => 'Chicha morada 1 L', 'categoria' => 'Bebidas', 'proveedor' => 'Distribuidora Frontera Norte', 'precio_compra' => 4.50, 'precio_venta' => 7, 'unidad_medida' => 'botella', 'stock_minimo' => 5],
                    ['nombre' => 'Sal yodada 1 kg', 'categoria' => 'Insumos de cocina', 'proveedor' => 'Distribuidora Frontera Norte', 'precio_compra' => 1.80, 'precio_venta' => 3, 'unidad_medida' => 'bolsa', 'stock_minimo' => 4],
                ],
            ],
            'bodega' => [
                'categorias' => [
                    'Abarrotes' => 'Productos básicos de despensa.',
                    'Bebidas' => 'Aguas y bebidas para el hogar.',
                    'Lácteos y conservas' => 'Productos envasados de consumo diario.',
                    'Limpieza' => 'Artículos para limpieza del hogar.',
                    'Cuidado personal' => 'Productos de higiene y cuidado diario.',
                ],
                'proveedores' => [
                    ['nombre' => 'Mayorista Frontera', 'contacto' => 'Luis Alberto Peña', 'telefono' => '925678904', 'email' => 'pedidos.mayorista@stockmype.test', 'direccion' => 'Tumbes (ficticia)'],
                    ['nombre' => 'Distribuciones El Norte', 'contacto' => 'Elena Seminario', 'telefono' => '936789015', 'email' => 'ventas.elnorte@stockmype.test', 'direccion' => 'Aguas Verdes, Tumbes (ficticia)'],
                    ['nombre' => 'Comercial San José', 'contacto' => 'Jorge Castillo', 'telefono' => '947890126', 'email' => 'pedidos.sanjose@stockmype.test', 'direccion' => 'Tumbes (ficticia)'],
                ],
                'productos' => [
                    ['nombre' => 'Arroz extra bolsa 5 kg', 'categoria' => 'Abarrotes', 'proveedor' => 'Mayorista Frontera', 'precio_compra' => 18, 'precio_venta' => 22, 'unidad_medida' => 'bolsa', 'stock_minimo' => 8],
                    ['nombre' => 'Azúcar rubia bolsa 1 kg', 'categoria' => 'Abarrotes', 'proveedor' => 'Mayorista Frontera', 'precio_compra' => 3.20, 'precio_venta' => 4.20, 'unidad_medida' => 'bolsa', 'stock_minimo' => 8],
                    ['nombre' => 'Aceite vegetal botella 1 L', 'categoria' => 'Abarrotes', 'proveedor' => 'Mayorista Frontera', 'precio_compra' => 7.30, 'precio_venta' => 9.50, 'unidad_medida' => 'botella', 'stock_minimo' => 7],
                    ['nombre' => 'Fideos spaghetti 500 g', 'categoria' => 'Abarrotes', 'proveedor' => 'Mayorista Frontera', 'precio_compra' => 2.20, 'precio_venta' => 3.20, 'unidad_medida' => 'bolsa', 'stock_minimo' => 7],
                    ['nombre' => 'Leche evaporada lata 400 g', 'categoria' => 'Lácteos y conservas', 'proveedor' => 'Distribuciones El Norte', 'precio_compra' => 3.20, 'precio_venta' => 4.30, 'unidad_medida' => 'lata', 'stock_minimo' => 8, 'vence_en_dias' => 5],
                    ['nombre' => 'Atún en agua lata 170 g', 'categoria' => 'Lácteos y conservas', 'proveedor' => 'Distribuciones El Norte', 'precio_compra' => 4.80, 'precio_venta' => 6.50, 'unidad_medida' => 'lata', 'stock_minimo' => 8, 'vence_en_dias' => 7],
                    ['nombre' => 'Galletas de vainilla paquete', 'categoria' => 'Abarrotes', 'proveedor' => 'Comercial San José', 'precio_compra' => 2, 'precio_venta' => 3, 'unidad_medida' => 'paquete', 'stock_minimo' => 6],
                    ['nombre' => 'Gaseosa cola botella 1.5 L', 'categoria' => 'Bebidas', 'proveedor' => 'Distribuciones El Norte', 'precio_compra' => 4.20, 'precio_venta' => 6, 'unidad_medida' => 'botella', 'stock_minimo' => 8],
                    ['nombre' => 'Agua de mesa botella 625 ml', 'categoria' => 'Bebidas', 'proveedor' => 'Distribuciones El Norte', 'precio_compra' => 0.80, 'precio_venta' => 1.50, 'unidad_medida' => 'botella', 'stock_minimo' => 12],
                    ['nombre' => 'Papel higiénico paquete x 4', 'categoria' => 'Cuidado personal', 'proveedor' => 'Comercial San José', 'precio_compra' => 5, 'precio_venta' => 7, 'unidad_medida' => 'paquete', 'stock_minimo' => 5],
                    ['nombre' => 'Detergente en polvo bolsa 1 kg', 'categoria' => 'Limpieza', 'proveedor' => 'Comercial San José', 'precio_compra' => 7.50, 'precio_venta' => 10, 'unidad_medida' => 'bolsa', 'stock_minimo' => 5],
                    ['nombre' => 'Sal yodada bolsa 1 kg', 'categoria' => 'Abarrotes', 'proveedor' => 'Mayorista Frontera', 'precio_compra' => 1.20, 'precio_venta' => 2, 'unidad_medida' => 'bolsa', 'stock_minimo' => 6],
                    ['nombre' => 'Café soluble frasco 200 g', 'categoria' => 'Abarrotes', 'proveedor' => 'Comercial San José', 'precio_compra' => 8, 'precio_venta' => 11, 'unidad_medida' => 'frasco', 'stock_minimo' => 4],
                    ['nombre' => 'Avena en hojuelas bolsa 1 kg', 'categoria' => 'Abarrotes', 'proveedor' => 'Mayorista Frontera', 'precio_compra' => 4, 'precio_venta' => 6, 'unidad_medida' => 'bolsa', 'stock_minimo' => 5],
                    ['nombre' => 'Mayonesa frasco 500 g', 'categoria' => 'Lácteos y conservas', 'proveedor' => 'Comercial San José', 'precio_compra' => 5.50, 'precio_venta' => 8, 'unidad_medida' => 'frasco', 'stock_minimo' => 4],
                    ['nombre' => 'Jabón de lavar barra', 'categoria' => 'Limpieza', 'proveedor' => 'Comercial San José', 'precio_compra' => 2.20, 'precio_venta' => 3.50, 'unidad_medida' => 'unidad', 'stock_minimo' => 5],
                    ['nombre' => 'Fósforos caja', 'categoria' => 'Cuidado personal', 'proveedor' => 'Mayorista Frontera', 'precio_compra' => 1, 'precio_venta' => 1.50, 'unidad_medida' => 'caja', 'stock_minimo' => 8],
                    ['nombre' => 'Chocolate para taza tableta', 'categoria' => 'Abarrotes', 'proveedor' => 'Comercial San José', 'precio_compra' => 2.50, 'precio_venta' => 4, 'unidad_medida' => 'tableta', 'stock_minimo' => 5],
                    ['nombre' => 'Cereal de maíz caja 300 g', 'categoria' => 'Abarrotes', 'proveedor' => 'Distribuciones El Norte', 'precio_compra' => 5, 'precio_venta' => 7, 'unidad_medida' => 'caja', 'stock_minimo' => 4],
                    ['nombre' => 'Huevos frescos por docena', 'categoria' => 'Lácteos y conservas', 'proveedor' => 'Mayorista Frontera', 'precio_compra' => 7, 'precio_venta' => 9, 'unidad_medida' => 'docena', 'stock_minimo' => 4],
                ],
            ],
            'ropa' => [
                'categorias' => [
                    'Polos' => 'Polos para dama y caballero en distintas tallas.',
                    'Jeans y pantalones' => 'Prendas de vestir inferiores por talla.',
                    'Casacas' => 'Casacas y prendas para temporada.',
                    'Zapatillas' => 'Calzado casual por talla.',
                    'Medias y accesorios' => 'Complementos y accesorios de vestir.',
                ],
                'proveedores' => [
                    ['nombre' => 'Confecciones del Norte', 'contacto' => 'Patricia Córdova', 'telefono' => '956781237', 'email' => 'pedidos.confecciones@stockmype.test', 'direccion' => 'Tumbes (ficticia)'],
                    ['nombre' => 'Calzados Perú Norte', 'contacto' => 'Raúl Feijoo', 'telefono' => '967812348', 'email' => 'ventas.calzados@stockmype.test', 'direccion' => 'Tumbes (ficticia)'],
                    ['nombre' => 'Distribuidora Moda Joven', 'contacto' => 'Mónica Saavedra', 'telefono' => '978123459', 'email' => 'pedidos.modajoven@stockmype.test', 'direccion' => 'Tumbes (ficticia)'],
                ],
                'productos' => [
                    ['nombre' => 'Polo básico unisex talla S', 'categoria' => 'Polos', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 16, 'precio_venta' => 29, 'unidad_medida' => 'unidad', 'stock_minimo' => 5],
                    ['nombre' => 'Polo básico unisex talla M', 'categoria' => 'Polos', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 16, 'precio_venta' => 29, 'unidad_medida' => 'unidad', 'stock_minimo' => 5],
                    ['nombre' => 'Polo básico unisex talla L', 'categoria' => 'Polos', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 16, 'precio_venta' => 29, 'unidad_medida' => 'unidad', 'stock_minimo' => 5],
                    ['nombre' => 'Polo estampado talla XL', 'categoria' => 'Polos', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 20, 'precio_venta' => 36, 'unidad_medida' => 'unidad', 'stock_minimo' => 5],
                    ['nombre' => 'Jean dama skinny talla 28', 'categoria' => 'Jeans y pantalones', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 45, 'precio_venta' => 75, 'unidad_medida' => 'unidad', 'stock_minimo' => 4],
                    ['nombre' => 'Jean dama skinny talla 30', 'categoria' => 'Jeans y pantalones', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 45, 'precio_venta' => 75, 'unidad_medida' => 'unidad', 'stock_minimo' => 4, 'vence_en_dias' => 5, 'descripcion' => 'Artículo de temporada para probar el aviso de fin de campaña.'],
                    ['nombre' => 'Jean caballero recto talla 32', 'categoria' => 'Jeans y pantalones', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 48, 'precio_venta' => 82, 'unidad_medida' => 'unidad', 'stock_minimo' => 4, 'vence_en_dias' => 7, 'descripcion' => 'Artículo de temporada para probar el aviso de fin de campaña.'],
                    ['nombre' => 'Jean caballero recto talla 34', 'categoria' => 'Jeans y pantalones', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 48, 'precio_venta' => 82, 'unidad_medida' => 'unidad', 'stock_minimo' => 4],
                    ['nombre' => 'Casaca liviana dama talla S', 'categoria' => 'Casacas', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 58, 'precio_venta' => 95, 'unidad_medida' => 'unidad', 'stock_minimo' => 3],
                    ['nombre' => 'Casaca liviana dama talla M', 'categoria' => 'Casacas', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 58, 'precio_venta' => 95, 'unidad_medida' => 'unidad', 'stock_minimo' => 3],
                    ['nombre' => 'Casaca cortaviento caballero talla L', 'categoria' => 'Casacas', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 64, 'precio_venta' => 105, 'unidad_medida' => 'unidad', 'stock_minimo' => 3],
                    ['nombre' => 'Casaca cortaviento caballero talla XL', 'categoria' => 'Casacas', 'proveedor' => 'Confecciones del Norte', 'precio_compra' => 64, 'precio_venta' => 105, 'unidad_medida' => 'unidad', 'stock_minimo' => 3],
                    ['nombre' => 'Zapatillas urbanas talla 38', 'categoria' => 'Zapatillas', 'proveedor' => 'Calzados Perú Norte', 'precio_compra' => 72, 'precio_venta' => 115, 'unidad_medida' => 'par', 'stock_minimo' => 3],
                    ['nombre' => 'Zapatillas urbanas talla 39', 'categoria' => 'Zapatillas', 'proveedor' => 'Calzados Perú Norte', 'precio_compra' => 72, 'precio_venta' => 115, 'unidad_medida' => 'par', 'stock_minimo' => 3],
                    ['nombre' => 'Zapatillas urbanas talla 40', 'categoria' => 'Zapatillas', 'proveedor' => 'Calzados Perú Norte', 'precio_compra' => 72, 'precio_venta' => 115, 'unidad_medida' => 'par', 'stock_minimo' => 3],
                    ['nombre' => 'Zapatillas urbanas talla 42', 'categoria' => 'Zapatillas', 'proveedor' => 'Calzados Perú Norte', 'precio_compra' => 76, 'precio_venta' => 125, 'unidad_medida' => 'par', 'stock_minimo' => 3],
                    ['nombre' => 'Medias deportivas talla estándar', 'categoria' => 'Medias y accesorios', 'proveedor' => 'Distribuidora Moda Joven', 'precio_compra' => 4, 'precio_venta' => 8, 'unidad_medida' => 'par', 'stock_minimo' => 8],
                    ['nombre' => 'Medias escolares talla estándar', 'categoria' => 'Medias y accesorios', 'proveedor' => 'Distribuidora Moda Joven', 'precio_compra' => 3.50, 'precio_venta' => 7, 'unidad_medida' => 'par', 'stock_minimo' => 8],
                    ['nombre' => 'Gorra urbana talla estándar', 'categoria' => 'Medias y accesorios', 'proveedor' => 'Distribuidora Moda Joven', 'precio_compra' => 12, 'precio_venta' => 22, 'unidad_medida' => 'unidad', 'stock_minimo' => 4],
                    ['nombre' => 'Correa casual talla estándar', 'categoria' => 'Medias y accesorios', 'proveedor' => 'Distribuidora Moda Joven', 'precio_compra' => 14, 'precio_venta' => 25, 'unidad_medida' => 'unidad', 'stock_minimo' => 4],
                ],
            ],
        ];
    }
}
