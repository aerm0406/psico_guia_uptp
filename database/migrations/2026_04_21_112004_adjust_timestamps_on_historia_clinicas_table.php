<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpiamos los registros donde la fecha de edición es igual a la de creación
        DB::table('historia_clinicas')
            ->whereColumn('created_at', 'updated_at')
            ->update(['updated_at' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar updated_at con created_at si se desea revertir
        DB::table('historia_clinicas')
            ->whereNull('updated_at')
            ->update(['updated_at' => DB::raw('created_at')]);
    }
};
