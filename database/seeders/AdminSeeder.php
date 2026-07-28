<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si el admin ya existe para no duplicarlo
        if (!\Illuminate\Support\Facades\DB::table('users')->where('email', 'admin@psicoguia.com')->exists()) {
            \Illuminate\Support\Facades\DB::table('users')->insert([
                'nombres' => 'Administrador',
                'apellidos' => 'Global',
                'cedula' => '00000000',
                'email' => 'admin@psicoguia.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'genero' => 'Femenino',
                'role' => 'admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
