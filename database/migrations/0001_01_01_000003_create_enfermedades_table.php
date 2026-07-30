<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enfermedades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('categoria', ['mental', 'fisica', 'biopsicosocial'])->default('fisica');
            $table->text('descripcion')->nullable();
            $table->boolean('estatus')->default(true);
            $table->string('tipo')->nullable();
            $table->timestamps();

            $table->unique(['nombre', 'tipo', 'categoria'], 'enfermedad_unica_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enfermedades');
    }
};
