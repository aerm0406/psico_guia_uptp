<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('prioridades')->whereNull('psicologo_id')->where('nombre', 'baja')->update(['nivel_gravedad' => 1]);
        DB::table('prioridades')->whereNull('psicologo_id')->where('nombre', 'media')->update(['nivel_gravedad' => 5]);
        DB::table('prioridades')->whereNull('psicologo_id')->where('nombre', 'alta')->update(['nivel_gravedad' => 7]);
        DB::table('prioridades')->whereNull('psicologo_id')->where('nombre', 'crítica')->update(['nivel_gravedad' => 10]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('prioridades')->whereNull('psicologo_id')->where('nombre', 'baja')->update(['nivel_gravedad' => 1]);
        DB::table('prioridades')->whereNull('psicologo_id')->where('nombre', 'media')->update(['nivel_gravedad' => 2]);
        DB::table('prioridades')->whereNull('psicologo_id')->where('nombre', 'alta')->update(['nivel_gravedad' => 3]);
        DB::table('prioridades')->whereNull('psicologo_id')->where('nombre', 'crítica')->update(['nivel_gravedad' => 4]);
    }
};
