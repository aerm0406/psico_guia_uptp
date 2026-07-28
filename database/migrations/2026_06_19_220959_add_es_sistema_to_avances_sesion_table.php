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
            $table->boolean('es_sistema')->default(false)->after('estado');
        });
        
        Schema::table('avances_sesion', function (Blueprint $table) {
            $table->unsignedBigInteger('psicologo_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avances_sesion', function (Blueprint $table) {
            $table->dropColumn('es_sistema');
        });
        // En reverse asume que vuelve a requerir psicologo_id, pero no lo aplicaremos aquí.
    }
};
