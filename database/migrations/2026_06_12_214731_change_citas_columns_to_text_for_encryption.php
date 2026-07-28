<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->text('motivo')->nullable()->change();
            $table->text('bloques_sugeridos')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->string('motivo', 255)->nullable()->change();
            $table->string('bloques_sugeridos', 255)->nullable()->change();
        });
    }
};
