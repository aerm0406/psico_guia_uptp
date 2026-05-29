<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('citas')) {
            DB::statement("ALTER TABLE `citas` MODIFY `estado` ENUM('pendiente','confirmada','cancelada','rechazada') NOT NULL DEFAULT 'pendiente';");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('citas')) {
            DB::statement("ALTER TABLE `citas` MODIFY `estado` ENUM('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente';");
        }
    }
};
