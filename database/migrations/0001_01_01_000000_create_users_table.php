<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nombres')->default('');
            $table->string('apellidos')->default('');
            $table->string('email')->nullable()->unique();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['psicologo', 'paciente'])->default('psicologo');
            $table->boolean('profile_completed')->default(false);
            $table->boolean('must_change_password')->default(false);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('aprobado')->default(1);
            $table->timestamp('infracciones_reset_at')->nullable();
            $table->string('pregunta_seguridad_1')->nullable();
            $table->string('respuesta_seguridad_1')->nullable();
            $table->string('pregunta_seguridad_2')->nullable();
            $table->string('respuesta_seguridad_2')->nullable();
            $table->string('pregunta_seguridad_3')->nullable();
            $table->string('respuesta_seguridad_3')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->string('prioridad_siguiente_cita')->nullable();
            $table->string('cedula')->default('')->unique();
            $table->string('genero')->default('');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono')->default('');
            $table->string('ubicacion')->default('');
            $table->string('discapacidad')->default('');
            $table->string('tipo_discapacidad')->nullable();
            $table->string('tiene_hijos')->default('');
            $table->unsignedTinyInteger('numero_hijos')->nullable();
            $table->string('estado_civil')->default('');
            $table->string('perfil_academico')->nullable();
            $table->string('pnf')->nullable();
            $table->integer('semestre')->nullable();
            $table->string('horario_path')->nullable();
            $table->timestamp('ultima_actividad_chat')->nullable();
            $table->unsignedBigInteger('chat_activo_user_id')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};