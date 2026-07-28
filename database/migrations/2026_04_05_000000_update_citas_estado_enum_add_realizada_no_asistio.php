<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `citas` MODIFY `estado` ENUM('pendiente','confirmada','cancelada','rechazada','realizada','no_asistio') NOT NULL DEFAULT 'pendiente';");
        } else {
            DB::statement("ALTER TABLE citas DROP CONSTRAINT IF EXISTS citas_estado_check;");
            DB::statement("ALTER TABLE citas ADD CONSTRAINT citas_estado_check CHECK (estado IN ('pendiente','confirmada','cancelada','rechazada','realizada','no_asistio'));");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `citas` MODIFY `estado` ENUM('pendiente','confirmada','cancelada','rechazada') NOT NULL DEFAULT 'pendiente';");
        } else {
            DB::statement("ALTER TABLE citas DROP CONSTRAINT IF EXISTS citas_estado_check;");
            DB::statement("ALTER TABLE citas ADD CONSTRAINT citas_estado_check CHECK (estado IN ('pendiente','confirmada','cancelada','rechazada'));");
        }
    }
};
