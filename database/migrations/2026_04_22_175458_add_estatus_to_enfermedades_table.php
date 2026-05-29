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
            $table->boolean('estatus')->default(1)->after('categoria');
        });
    }

    public function down(): void
    {
        Schema::table('enfermedades', function (Blueprint $table) {
            $table->dropColumn('estatus');
        });
    }
};
