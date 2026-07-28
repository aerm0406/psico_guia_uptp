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
            Schema::table('enfermedades', function (Blueprint $table) {
                $table->renameColumn('descripcion', 'categoria');
            });
        }

        // 2. Limpiar datos para que coincidan con el nuevo ENUM
        \Illuminate\Support\Facades\DB::table('enfermedades')->whereNull('categoria')->update([
            'categoria' => 'fisica'
        ]);
        
        \Illuminate\Support\Facades\DB::table('enfermedades')->whereNotIn('categoria', ['mental', 'fisica'])->update([
            'categoria' => 'fisica'
        ]);

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            Schema::table('enfermedades', function (Blueprint $table) {
                // 3. Cambiar tipo a string (variacion)
                $table->string('tipo', 255)->nullable()->change();
                
                // 4. Cambiar categoria a enum
                $table->enum('categoria', ['mental', 'fisica'])->default('fisica')->change();
                
                // 5. Añadir el indice unico
                $table->unique(['nombre', 'tipo', 'categoria'], 'enfermedad_unica_idx');
            });
        } else {
            Schema::table('enfermedades', function (Blueprint $table) {
                $table->string('tipo', 255)->nullable()->change();
                $table->string('categoria', 255)->default('fisica')->change();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE enfermedades DROP CONSTRAINT IF EXISTS enfermedades_categoria_check;");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE enfermedades ADD CONSTRAINT enfermedades_categoria_check CHECK (categoria IN ('mental', 'fisica'));");
            
            Schema::table('enfermedades', function (Blueprint $table) {
                $table->unique(['nombre', 'tipo', 'categoria'], 'enfermedad_unica_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enfermedades', function (Blueprint $table) {
            $table->dropUnique('enfermedad_unica_idx');
        });

        Schema::table('enfermedades', function (Blueprint $table) {
            $table->renameColumn('categoria', 'descripcion');
        });
        
        Schema::table('enfermedades', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
                $table->enum('tipo', ['mental', 'fisica'])->default('fisica')->change();
            } else {
                $table->string('tipo', 255)->default('fisica')->change();
            }
        });
    }
};
