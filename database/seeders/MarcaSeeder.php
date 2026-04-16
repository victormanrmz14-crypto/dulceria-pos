<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        $marcas = [
            'Barcel',
            'Coronado',
            'De la Rosa',
            'Lucky Gummys',
            'Ferrero',
            'Hershey',
            'Pelon Pelo Rico',
            'Sabritas',
            'Sonrics',
            'Turin',
            'Dulces Vero',
            'Ricolino',
            'Tama-Roca',
            'Lucas',
            'Miguelito',
            'Arcor',
        ];

        $ahora = now();

        foreach ($marcas as $nombre) {
            DB::table('marcas')->insert([
                'nombre'     => $nombre,
                'activo'     => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }
}
