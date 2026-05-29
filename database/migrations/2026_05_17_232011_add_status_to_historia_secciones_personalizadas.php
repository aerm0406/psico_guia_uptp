<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade la columna 'status' (1=activo, 0=eliminado) para soft-delete de secciones personalizadas.
     */
    public function up(): void
    {
        Schema::table('historia_secciones_personalizadas', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->after('orden')
                  ->comment('1=activo, 0=eliminado (soft delete)');
        });

        // Todas las secciones existentes se marcan como activas
        DB::table('historia_secciones_personalizadas')->update(['status' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historia_secciones_personalizadas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
