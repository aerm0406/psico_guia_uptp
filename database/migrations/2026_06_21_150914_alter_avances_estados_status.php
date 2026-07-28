<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('avances_sesion', 'status')) {
            Schema::table('avances_sesion', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1);
            });
        }

        if (!Schema::hasColumn('estado_animos', 'status')) {
            Schema::table('estado_animos', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1);
            });
        }
    }

    public function down()
    {
        Schema::table('avances_sesion', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('estado_animos', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
