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
        // Change the column to string type instead of ENUM, which requires raw statement or doctrine/dbal. 
        // In Laravel 11, ->change() works natively for basic types like string.
        Schema::table('citas', function (Blueprint $table) {
            $table->string('prioridad')->default('media')->change();
        });

        // Update existing records
        DB::table('citas')->where('prioridad', 'super-alta')->update(['prioridad' => 'crítica']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data
        DB::table('citas')->where('prioridad', 'crítica')->update(['prioridad' => 'super-alta']);

        // Since going back to ENUM is tricky without knowing previous values, we can leave it as string, 
        // but if strictly needed:
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE citas MODIFY prioridad ENUM('baja', 'media', 'alta', 'super-alta') DEFAULT 'media'");
        } else {
            Schema::table('citas', function (Blueprint $table) {
                $table->string('prioridad')->default('media')->change();
            });
        }
    }
};
