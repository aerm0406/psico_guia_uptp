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
        Schema::create('prioridades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('nivel_gravedad')->default(1);
            $table->foreignId('psicologo_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Cada psicólogo no puede repetir el nombre de prioridad (globales tienen psicologo_id = null)
            $table->unique(['nombre', 'psicologo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prioridades');
    }
};
