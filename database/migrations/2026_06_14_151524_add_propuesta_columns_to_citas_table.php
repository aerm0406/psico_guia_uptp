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
        if (!Schema::hasColumn('citas', 'propuesta_estado')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->enum('propuesta_estado', ['pendiente', 'aceptada', 'rechazada', 'cualquier_dia', 'sugerencia_aceptada'])->nullable()->after('bloques_propuestos');
            });
        }
        if (!Schema::hasColumn('citas', 'propuesta_bloque_seleccionado')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->text('propuesta_bloque_seleccionado')->nullable()->after('propuesta_estado');
            });
        }
        if (!Schema::hasColumn('citas', 'motivo_rechazo_propuesta')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->text('motivo_rechazo_propuesta')->nullable()->after('propuesta_bloque_seleccionado');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['propuesta_estado', 'propuesta_bloque_seleccionado', 'motivo_rechazo_propuesta']);
        });
    }
};
