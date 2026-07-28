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
        Schema::table('avances_sesion', function (Blueprint $table) {
            $table->unsignedTinyInteger('valor')->default(0)->after('psicologo_id')->comment('Nivel del 1 al 10');
        });

        // Actualizar valores por defecto de los del sistema
        \Illuminate\Support\Facades\DB::table('avances_sesion')
            ->where('nombre', 'Estancado')
            ->update(['valor' => 1]);

        \Illuminate\Support\Facades\DB::table('avances_sesion')
            ->where('nombre', 'En progreso')
            ->update(['valor' => 3]);

        \Illuminate\Support\Facades\DB::table('avances_sesion')
            ->where('nombre', 'Logrado')
            ->update(['valor' => 9]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avances_sesion', function (Blueprint $table) {
            $table->dropColumn('valor');
        });
    }
};
