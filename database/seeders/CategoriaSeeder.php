<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Bombones',
            'Botanas',
            'Chocolates',
            'Caramelos',
            'Gomitas',
            'Chicles',
            'Dulces Ácidos',
            'Dulces Típicos',
            'Dulces Picosos',
            'Paletas',
            'Pulpas',
            'Chiclosos',
        ];

        $ahora = now();

        foreach ($categorias as $nombre) {
            DB::table('categorias')->insert([
                'nombre'     => $nombre,
                'activo'     => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }
}
