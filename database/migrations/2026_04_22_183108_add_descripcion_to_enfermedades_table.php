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
        Schema::table('enfermedades', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('categoria');
        });
    }

    public function down(): void
    {
        Schema::table('enfermedades', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
};
