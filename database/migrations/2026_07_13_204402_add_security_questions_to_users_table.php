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
        Schema::table('users', function (Blueprint $table) {
            $table->string('pregunta_seguridad_1')->nullable()->after('infracciones_reset_at');
            $table->string('respuesta_seguridad_1')->nullable()->after('pregunta_seguridad_1');
            $table->string('pregunta_seguridad_2')->nullable()->after('respuesta_seguridad_1');
            $table->string('respuesta_seguridad_2')->nullable()->after('pregunta_seguridad_2');
            $table->string('pregunta_seguridad_3')->nullable()->after('respuesta_seguridad_2');
            $table->string('respuesta_seguridad_3')->nullable()->after('pregunta_seguridad_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'pregunta_seguridad_1',
                'respuesta_seguridad_1',
                'pregunta_seguridad_2',
                'respuesta_seguridad_2',
                'pregunta_seguridad_3',
                'respuesta_seguridad_3',
            ]);
        });
    }
};
