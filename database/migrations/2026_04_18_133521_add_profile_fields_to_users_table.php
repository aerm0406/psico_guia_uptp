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
            $table->string('cedula')->nullable();
            $table->string('genero')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono')->nullable();
            $table->string('ubicacion')->nullable();
            $table->string('discapacidad')->nullable();
            $table->string('tiene_hijos')->nullable();
            $table->string('estado_civil')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cedula', 
                'genero', 
                'fecha_nacimiento', 
                'telefono', 
                'ubicacion', 
                'discapacidad', 
                'tiene_hijos', 
                'estado_civil'
            ]);
        });
    }
};
