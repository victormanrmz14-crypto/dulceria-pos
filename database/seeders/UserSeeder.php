<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nombre'   => 'Victor Manuel',
            'apellido' => 'Ramírez',
            'username' => 'admin',
            'email'    => 'admin@dulceria.com',
            'password' => Hash::make('12345678'),
            'rol'      => 'admin',
            'activo'   => true,
        ]);

        User::create([
            'nombre'   => 'María',
            'apellido' => 'García',
            'username' => 'mgarcia',
            'email'    => 'mgarcia@dulceria.com',
            'password' => Hash::make('12345678'),
            'rol'      => 'cajero',
            'activo'   => true,
        ]);

        User::create([
            'nombre'   => 'Juan',
            'apellido' => 'López',
            'username' => 'jlopez',
            'email'    => 'jlopez@dulceria.com',
            'password' => Hash::make('12345678'),
            'rol'      => 'cajero',
            'activo'   => true,
        ]);
    }
}
