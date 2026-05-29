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
        // 1. Ajustar tabla de secciones para incluir icono y descripción general
        Schema::table('historia_secciones_personalizadas', function (Blueprint $table) {
            $table->string('icono')->nullable()->after('titulo');
            $table->string('descripcion_general')->nullable()->after('icono');
            $table->dropColumn('contenido'); // El contenido ahora estará en los segmentos
        });

        // 2. Crear tabla de segmentos (sub-secciones)
        Schema::create('historia_segmentos_personalizados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('historia_secciones_personalizadas')->onDelete('cascade');
            $table->string('titulo')->nullable();
            $table->string('subtitulo')->nullable();
            $table->string('icono')->nullable();
            $table->longText('contenido')->nullable(); // Cifrado en el modelo
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        // 3. Añadir icono a las plantillas para que también se reutilice el emoji
        Schema::table('historia_plantillas_secciones', function (Blueprint $table) {
            $table->string('icono')->nullable()->after('titulo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historia_segmentos_personalizados');
        
        Schema::table('historia_secciones_personalizadas', function (Blueprint $table) {
            $table->longText('contenido')->nullable();
            $table->dropColumn(['icono', 'descripcion_general']);
        });

        Schema::table('historia_plantillas_secciones', function (Blueprint $table) {
            $table->dropColumn('icono');
        });
    }
};
