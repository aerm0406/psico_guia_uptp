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
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `citas` MODIFY `estado` ENUM('pendiente','confirmada','cancelada','rechazada') NOT NULL DEFAULT 'pendiente';");
            } else {
                DB::statement("ALTER TABLE citas DROP CONSTRAINT IF EXISTS citas_estado_check;");
                DB::statement("ALTER TABLE citas ADD CONSTRAINT citas_estado_check CHECK (estado IN ('pendiente', 'confirmada', 'cancelada', 'rechazada'));");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('citas')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `citas` MODIFY `estado` ENUM('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente';");
            } else {
                DB::statement("ALTER TABLE citas DROP CONSTRAINT IF EXISTS citas_estado_check;");
                DB::statement("ALTER TABLE citas ADD CONSTRAINT citas_estado_check CHECK (estado IN ('pendiente', 'confirmada', 'cancelada'));");
            }
        }
    }
};
