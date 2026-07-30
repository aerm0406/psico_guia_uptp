<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User factory fue removido porque el modelo no usa Eloquent

        $this->call([
            AdminSeeder::class,
            GrupoHorarioSeeder::class,
            AvanceSesionSeeder::class,
            NotaEvolucionSeeder::class,
            EstadoAnimoSeeder::class,
            PlantillaGlobalSeeder::class,
            PrioridadesSeeder::class,
        ]);
    }
}
