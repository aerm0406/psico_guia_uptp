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
        // 1. Renombrar descripcion a categoria solo si aun existe descripcion
        if (Schema::hasColumn('enfermedades', 'descripcion')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE enfermedades CHANGE descripcion categoria TEXT');
        }

        // 2. Limpiar datos para que coincidan con el nuevo ENUM
        \Illuminate\Support\Facades\DB::table('enfermedades')->whereNull('categoria')->update([
            'categoria' => 'fisica'
        ]);
        
        \Illuminate\Support\Facades\DB::table('enfermedades')->whereNotIn('categoria', ['mental', 'fisica'])->update([
            'categoria' => 'fisica'
        ]);

        Schema::table('enfermedades', function (Blueprint $table) {
            // 3. Cambiar tipo a string (variacion)
            $table->string('tipo', 255)->nullable()->change();
            
            // 4. Cambiar categoria a enum
            $table->enum('categoria', ['mental', 'fisica'])->default('fisica')->change();
            
            // 5. Añadir el indice unico
            $table->unique(['nombre', 'tipo', 'categoria'], 'enfermedad_unica_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enfermedades', function (Blueprint $table) {
            $table->dropUnique('enfermedad_unica_idx');
        });

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE enfermedades CHANGE categoria descripcion TEXT');
        
        Schema::table('enfermedades', function (Blueprint $table) {
            $table->enum('tipo', ['mental', 'fisica'])->default('fisica')->change();
        });
    }
};
