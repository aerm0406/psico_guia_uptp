<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PlantillaGlobalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los psicólogos
        $psicologos = DB::table('users')->whereIn('role', ['psicologo', 'admin'])->get();

        $seccionesPredefinidas = [
            [
                'titulo' => 'Antecedentes Personales',
                'descripcion_general' => 'En el ámbito de salud general',
                'segmentos' => ['Salud Mental', 'Salud General']
            ],
            [
                'titulo' => 'Antecedentes Familiares',
                'descripcion_general' => 'Recor de salud desde el lado familiar Paterno',
                'segmentos' => ['Salud Mental', 'Salud General']
            ],
            [
                'titulo' => 'Antecedentes',
                'descripcion_general' => 'Recor de salud desde el lado familiar Materno',
                'segmentos' => ['Salud Mental', 'Salud General']
            ],
            [
                'titulo' => 'Diagnostico General',
                'descripcion_general' => 'Este abarcará todo momento con el paciente',
                'segmentos' => ['Observaciones y Diagnosticos', 'Plan de Acción para la recuperación']
            ]
        ];

        foreach ($psicologos as $psicologo) {
            $plantilla = DB::table('historia_plantillas_globales')
                ->where('psicologo_id', $psicologo->id)
                ->first();

            if (!$plantilla) {
                DB::table('historia_plantillas_globales')->insert([
                    'psicologo_id' => $psicologo->id,
                    'titulo' => 'Expediente General de Pacientes',
                    'descripcion' => 'Especificaciones del record de salud del paciente',
                    'secciones' => json_encode($seccionesPredefinidas),
                    'status' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
