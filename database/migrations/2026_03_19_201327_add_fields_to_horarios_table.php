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
        Schema::table('horarios', function (Blueprint $table) {
            $table->string('dia')->after('user_id');
            $table->time('hora_inicio')->after('dia');
            $table->time('hora_fin')->after('hora_inicio');

            // Se eliminan los campos de fecha/hora anteriores para no duplicar datos.
            if (Schema::hasColumn('horarios', 'fecha')) {
                $table->dropColumn('fecha');
            }
            if (Schema::hasColumn('horarios', 'hora')) {
                $table->dropColumn('hora');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            if (Schema::hasColumn('horarios', 'dia')) {
                $table->dropColumn('dia');
            }
            if (Schema::hasColumn('horarios', 'hora_inicio')) {
                $table->dropColumn('hora_inicio');
            }
            if (Schema::hasColumn('horarios', 'hora_fin')) {
                $table->dropColumn('hora_fin');
            }

            // Restaurar los campos anteriores si hacen falta. (No se recrean automáticamente).
        });
    }
};
