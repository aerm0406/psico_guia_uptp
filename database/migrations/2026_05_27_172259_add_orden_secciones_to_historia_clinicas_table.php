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
        Schema::table('historia_clinicas', function (Blueprint $table) {
            $table->longText('orden_secciones')->nullable()->after('plan_tratamiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historia_clinicas', function (Blueprint $table) {
            $table->dropColumn('orden_secciones');
        });
    }
};
