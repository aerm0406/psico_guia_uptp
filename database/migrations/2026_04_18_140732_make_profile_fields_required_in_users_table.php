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
            // Hacer NOT NULL los campos de perfil del paciente
            $table->string('nombres')->nullable(false)->default('')->change();
            $table->string('apellidos')->nullable(false)->default('')->change();
            $table->string('cedula')->nullable(false)->default('')->change();
            $table->string('genero')->nullable(false)->default('')->change();
            $table->date('fecha_nacimiento')->nullable()->change(); // fecha se mantiene nullable
            $table->string('telefono')->nullable(false)->default('')->change();
            $table->string('ubicacion')->nullable(false)->default('')->change();
            $table->string('discapacidad')->nullable(false)->default('')->change();
            $table->string('tiene_hijos')->nullable(false)->default('')->change();
            $table->string('estado_civil')->nullable(false)->default('')->change();

            // Bandera que indica si el paciente ya completó su perfil
            $table->boolean('profile_completed')->default(false)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombres')->nullable()->default(null)->change();
            $table->string('apellidos')->nullable()->default(null)->change();
            $table->string('cedula')->nullable()->default(null)->change();
            $table->string('genero')->nullable()->default(null)->change();
            $table->string('telefono')->nullable()->default(null)->change();
            $table->string('ubicacion')->nullable()->default(null)->change();
            $table->string('discapacidad')->nullable()->default(null)->change();
            $table->string('tiene_hijos')->nullable()->default(null)->change();
            $table->string('estado_civil')->nullable()->default(null)->change();
            $table->dropColumn('profile_completed');
        });
    }
};
