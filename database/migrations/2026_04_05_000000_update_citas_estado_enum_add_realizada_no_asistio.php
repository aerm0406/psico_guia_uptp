<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE `citas` MODIFY `estado` ENUM('pendiente','confirmada','cancelada','rechazada','realizada','no_asistio') NOT NULL DEFAULT 'pendiente';");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `citas` MODIFY `estado` ENUM('pendiente','confirmada','cancelada','rechazada') NOT NULL DEFAULT 'pendiente';");
    }
};
