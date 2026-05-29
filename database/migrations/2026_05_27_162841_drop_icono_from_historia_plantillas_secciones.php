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
            $table->dropColumn('icono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historia_plantillas_secciones', function (Blueprint $table) {
            $table->string('icono')->nullable()->after('titulo');
        });
    }
};
