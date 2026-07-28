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
        Schema::table('historia_plantillas_globales', function (Blueprint $table) {
            $table->tinyInteger('status')->default(2)->after('secciones')->comment('1 = Activo, 2 = Predeterminado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historia_plantillas_globales', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
