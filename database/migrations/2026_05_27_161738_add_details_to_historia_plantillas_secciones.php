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
        Schema::table('historia_plantillas_secciones', function (Blueprint $table) {
            $table->string('descripcion_general')->nullable()->after('icono');
            $table->json('segmentos')->nullable()->after('descripcion_general');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historia_plantillas_secciones', function (Blueprint $table) {
            $table->dropColumn(['descripcion_general', 'segmentos']);
        });
    }
};
