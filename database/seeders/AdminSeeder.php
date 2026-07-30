<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!DB::table('users')->where('email', 'admin@psicoguia.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Administrador Global',
                'nombres' => 'Administrador',
                'apellidos' => 'Global',
                'cedula' => '00000000',
                'email' => 'admin@psicoguia.com',
                'password' => Hash::make('password'),
                'genero' => 'Femenino',
                'role' => 'psicologo',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
