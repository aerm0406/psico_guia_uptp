<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->string('tipo')->default('texto')->after('contenido'); // texto, color, imagen
            $table->string('color_fondo')->nullable()->after('tipo');
            $table->string('media_path')->nullable()->after('color_fondo');
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'color_fondo', 'media_path']);
        });
    }
};
