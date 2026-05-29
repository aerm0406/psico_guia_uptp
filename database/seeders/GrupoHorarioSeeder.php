<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GrupoHorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\GrupoHorario::create([
            'user_id' => 1, // Asumiendo que el primer usuario es el admin
            'nombre' => 'Horario Estándar',
            'activo' => \App\Models\GrupoHorario::STATUS_ACTIVE,
        ]);
    }
}
