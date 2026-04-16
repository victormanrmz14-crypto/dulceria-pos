<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * IDs de categorías (en el orden que inserta CategoriaSeeder):
     *  1 Bombones | 2 Botanas   | 3 Chocolates   | 4 Caramelos
     *  5 Gomitas  | 6 Chicles   | 7 Dulces Ácidos | 8 Dulces Típicos
     *  9 Dulces Picosos | 10 Paletas | 11 Pulpas | 12 Chiclosos
     *
     * IDs de marcas (en el orden que inserta MarcaSeeder):
     *  1 Barcel        | 2 Coronado      | 3 De la Rosa   | 4 Lucky Gummys
     *  5 Ferrero       | 6 Hershey       | 7 Pelon Pelo Rico | 8 Sabritas
     *  9 Sonrics       | 10 Turin        | 11 Dulces Vero | 12 Ricolino
     * 13 Tama-Roca     | 14 Lucas        | 15 Miguelito   | 16 Arcor
     *
     * IDs de proveedores (en el orden que inserta ProveedorSeeder):
     *  1 Distribuidora Dulce México
     *  2 Proveedor Ricolino Norte
     *  3 Importadora Candy SA
     *
     * Productos con stock < stock_minimo (alertas de demo):
     *  Rollo de Gomitas Lucky  (stock 6  / min 10)
     *  Lucas Muecas            (stock 8  / min 20)
     *  Tama-Roca bolsa         (stock 3  / min 10)
     *  Paleta Cristal Sonrics  (stock 5  / min 15)
     */
    public function run(): void
    {
        $ahora = now();

        $productos = [
            // ── Dulces Típicos ───────────────────────────────────────────────
            [
                'nombre'        => 'Mazapán De la Rosa',
                'categoria_id'  => 8,
                'marca_id'      => 3,
                'proveedor_id'  => 1,
                'precio'        => 5.00,
                'stock'         => 150.000,
                'stock_minimo'  => 20.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Glorias Coronado',
                'categoria_id'  => 8,
                'marca_id'      => 2,
                'proveedor_id'  => 1,
                'precio'        => 8.00,
                'stock'         => 120.000,
                'stock_minimo'  => 20.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Tama-Roca bolsa 50 pzas',
                'categoria_id'  => 8,
                'marca_id'      => 13,
                'proveedor_id'  => 1,
                'precio'        => 45.00,
                'stock'         => 3.000,   // ← stock bajo
                'stock_minimo'  => 10.000,
                'unidad_medida' => 'bolsa',
            ],
            // ── Pulpas ───────────────────────────────────────────────────────
            [
                'nombre'        => 'Pulparindo Rojo',
                'categoria_id'  => 11,
                'marca_id'      => 3,
                'proveedor_id'  => 1,
                'precio'        => 4.00,
                'stock'         => 200.000,
                'stock_minimo'  => 30.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Pulparindo Negro',
                'categoria_id'  => 11,
                'marca_id'      => 3,
                'proveedor_id'  => 1,
                'precio'        => 4.00,
                'stock'         => 175.000,
                'stock_minimo'  => 30.000,
                'unidad_medida' => 'pieza',
            ],
            // ── Dulces Picosos ───────────────────────────────────────────────
            [
                'nombre'        => 'Pelón Pelo Rico Original',
                'categoria_id'  => 9,
                'marca_id'      => 7,
                'proveedor_id'  => 1,
                'precio'        => 6.00,
                'stock'         => 120.000,
                'stock_minimo'  => 20.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Miguelito Chamoy',
                'categoria_id'  => 9,
                'marca_id'      => 15,
                'proveedor_id'  => 1,
                'precio'        => 5.00,
                'stock'         => 180.000,
                'stock_minimo'  => 30.000,
                'unidad_medida' => 'sobrecito',
            ],
            // ── Dulces Ácidos ────────────────────────────────────────────────
            [
                'nombre'        => 'Lucas Muecas Fresa',
                'categoria_id'  => 7,
                'marca_id'      => 14,
                'proveedor_id'  => 1,
                'precio'        => 6.00,
                'stock'         => 8.000,   // ← stock bajo
                'stock_minimo'  => 20.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Lucas Muecas Sandía',
                'categoria_id'  => 7,
                'marca_id'      => 14,
                'proveedor_id'  => 1,
                'precio'        => 6.00,
                'stock'         => 45.000,
                'stock_minimo'  => 20.000,
                'unidad_medida' => 'pieza',
            ],
            // ── Chocolates ───────────────────────────────────────────────────
            [
                'nombre'        => 'Paleta Payaso',
                'categoria_id'  => 3,
                'marca_id'      => 12,
                'proveedor_id'  => 2,
                'precio'        => 8.00,
                'stock'         => 150.000,
                'stock_minimo'  => 25.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Kit Kat 42g',
                'categoria_id'  => 3,
                'marca_id'      => 6,
                'proveedor_id'  => 3,
                'precio'        => 18.00,
                'stock'         => 80.000,
                'stock_minimo'  => 15.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Ferrero Rocher x3',
                'categoria_id'  => 3,
                'marca_id'      => 5,
                'proveedor_id'  => 3,
                'precio'        => 45.00,
                'stock'         => 40.000,
                'stock_minimo'  => 10.000,
                'unidad_medida' => 'caja',
            ],
            [
                'nombre'        => 'Chocolate Turín Menta',
                'categoria_id'  => 3,
                'marca_id'      => 10,
                'proveedor_id'  => 3,
                'precio'        => 22.00,
                'stock'         => 55.000,
                'stock_minimo'  => 12.000,
                'unidad_medida' => 'pieza',
            ],
            // ── Gomitas ──────────────────────────────────────────────────────
            [
                'nombre'        => 'Rollo de Gomitas Lucky',
                'categoria_id'  => 5,
                'marca_id'      => 4,
                'proveedor_id'  => 3,
                'precio'        => 12.00,
                'stock'         => 6.000,   // ← stock bajo
                'stock_minimo'  => 10.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Gummies Lucky Bears 100g',
                'categoria_id'  => 5,
                'marca_id'      => 4,
                'proveedor_id'  => 3,
                'precio'        => 25.00,
                'stock'         => 70.000,
                'stock_minimo'  => 15.000,
                'unidad_medida' => 'bolsa',
            ],
            // ── Bombones ─────────────────────────────────────────────────────
            [
                'nombre'        => 'Bombón Coronado Leche',
                'categoria_id'  => 1,
                'marca_id'      => 2,
                'proveedor_id'  => 1,
                'precio'        => 5.50,
                'stock'         => 200.000,
                'stock_minimo'  => 30.000,
                'unidad_medida' => 'pieza',
            ],
            // ── Chiclosos ────────────────────────────────────────────────────
            [
                'nombre'        => 'Duvalin Avellana',
                'categoria_id'  => 12,
                'marca_id'      => 12,
                'proveedor_id'  => 2,
                'precio'        => 7.00,
                'stock'         => 180.000,
                'stock_minimo'  => 25.000,
                'unidad_medida' => 'pieza',
            ],
            // ── Caramelos ────────────────────────────────────────────────────
            [
                'nombre'        => 'Caramelos Arcor Surtidos',
                'categoria_id'  => 4,
                'marca_id'      => 16,
                'proveedor_id'  => 3,
                'precio'        => 3.00,
                'stock'         => 500.000,
                'stock_minimo'  => 50.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Paleta Cristal Sonrics',
                'categoria_id'  => 4,
                'marca_id'      => 9,
                'proveedor_id'  => 3,
                'precio'        => 4.00,
                'stock'         => 5.000,   // ← stock bajo
                'stock_minimo'  => 15.000,
                'unidad_medida' => 'pieza',
            ],
            // ── Paletas ──────────────────────────────────────────────────────
            [
                'nombre'        => 'Paleta Vero Mango',
                'categoria_id'  => 10,
                'marca_id'      => 11,
                'proveedor_id'  => 1,
                'precio'        => 5.00,
                'stock'         => 250.000,
                'stock_minimo'  => 40.000,
                'unidad_medida' => 'pieza',
            ],
            [
                'nombre'        => 'Paleta Granada Turín',
                'categoria_id'  => 10,
                'marca_id'      => 10,
                'proveedor_id'  => 3,
                'precio'        => 6.00,
                'stock'         => 200.000,
                'stock_minimo'  => 30.000,
                'unidad_medida' => 'pieza',
            ],
            // ── Chicles ──────────────────────────────────────────────────────
            [
                'nombre'        => 'Chicle Blanco Sonrics',
                'categoria_id'  => 6,
                'marca_id'      => 9,
                'proveedor_id'  => 1,
                'precio'        => 2.00,
                'stock'         => 300.000,
                'stock_minimo'  => 50.000,
                'unidad_medida' => 'pieza',
            ],
            // ── Botanas ──────────────────────────────────────────────────────
            [
                'nombre'        => 'Micheladas Barcel 65g',
                'categoria_id'  => 2,
                'marca_id'      => 1,
                'proveedor_id'  => 1,
                'precio'        => 14.00,
                'stock'         => 100.000,
                'stock_minimo'  => 20.000,
                'unidad_medida' => 'bolsa',
            ],
            [
                'nombre'        => 'Sabritas Original 45g',
                'categoria_id'  => 2,
                'marca_id'      => 8,
                'proveedor_id'  => 1,
                'precio'        => 16.00,
                'stock'         => 120.000,
                'stock_minimo'  => 25.000,
                'unidad_medida' => 'bolsa',
            ],
        ];

        foreach ($productos as $producto) {
            DB::table('productos')->insert(array_merge($producto, [
                'activo'     => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]));
        }
    }
}
