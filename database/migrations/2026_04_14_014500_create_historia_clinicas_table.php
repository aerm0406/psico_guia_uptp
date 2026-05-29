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
        Schema::create('historia_clinicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Paciente
            $table->foreignId('psicologo_id')->constrained('users')->onDelete('cascade'); // Psicólogo asignado
            
            // Campos cifrados (almacenados como TEXT/LONGTEXT en BD)
            $table->longText('antecedentes_personales')->nullable();
            $table->longText('antecedentes_familiares')->nullable();
            $table->text('diagnostico_inicial')->nullable();
            $table->longText('plan_tratamiento')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historia_clinicas');
    }
};
