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
            $table->string('perfil_academico')->nullable()->after('estado_civil');
            $table->string('pnf')->nullable()->after('perfil_academico');
            $table->integer('semestre')->nullable()->after('pnf');
            $table->string('horario_path')->nullable()->after('semestre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
