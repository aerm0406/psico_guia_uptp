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
            // Antecedentes Personales
            $table->text('antec_pers_psq')->nullable()->after('antecedentes_personales');
            $table->text('antec_pers_med')->nullable()->after('antec_pers_psq');
            
            // Antecedentes Familiares
            $table->text('antec_fam_psq')->nullable()->after('antecedentes_familiares');
            $table->text('antec_fam_med')->nullable()->after('antec_fam_psq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historia_clinicas', function (Blueprint $table) {
            $table->dropColumn(['antec_pers_psq', 'antec_pers_med', 'antec_fam_psq', 'antec_fam_med']);
        });
    }
};
