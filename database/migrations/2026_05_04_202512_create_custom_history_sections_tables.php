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
        // Tabla para almacenar títulos de secciones que el psicólogo puede reutilizar
        Schema::create('historia_plantillas_secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psicologo_id')->constrained('users')->onDelete('cascade');
            $table->string('titulo');
            $table->timestamps();
            
            // Un psicólogo no debería tener títulos duplicados en sus plantillas
            $table->unique(['psicologo_id', 'titulo']);
        });

        // Tabla para las secciones reales en el historial de cada paciente
        Schema::create('historia_secciones_personalizadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_clinica_id')->constrained('historia_clinicas')->onDelete('cascade');
            $table->string('titulo');
            $table->longText('contenido')->nullable(); // Se guardará cifrado mediante Eloquent
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historia_secciones_personalizadas');
        Schema::dropIfExists('historia_plantillas_secciones');
    }
};
