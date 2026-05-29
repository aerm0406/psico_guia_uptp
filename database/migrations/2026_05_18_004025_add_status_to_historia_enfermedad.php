<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade la columna 'status' (1=activo, 0=desvinculado) y 'updated_at' a 'historia_enfermedad'.
     */
    public function up(): void
    {
        Schema::table('historia_enfermedad', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->after('contexto')
                  ->comment('1=activo, 0=desvinculado/eliminado (soft delete)');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });

        // Asegurar que registros existentes tengan status = 1 y updated_at = created_at
        DB::table('historia_enfermedad')->update([
            'status' => 1,
            'updated_at' => DB::raw('created_at')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historia_enfermedad', function (Blueprint $table) {
            $table->dropColumn(['status', 'updated_at']);
        });
    }
};
