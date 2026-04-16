<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();

        DB::table('proveedores')->insert([
            [
                'nombre'     => 'Distribuidora Dulce México',
                'email'      => 'ventas@dulcemexico.com',
                'telefono'   => '5512345678',
                'notas'      => 'Proveedor principal de dulces nacionales. Entrega los lunes y jueves.',
                'activo'     => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'nombre'     => 'Proveedor Ricolino Norte',
                'email'      => 'pedidos@ricolinonorte.com',
                'telefono'   => '8112345678',
                'notas'      => 'Distribuidor autorizado Ricolino para la zona norte. Pedido mínimo $500.',
                'activo'     => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'nombre'     => 'Importadora Candy SA',
                'email'      => 'contacto@importadoracandy.com',
                'telefono'   => '3312345678',
                'notas'      => 'Especialistas en dulces importados y chocolates premium. Entrega semanal.',
                'activo'     => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ]);
    }
}
