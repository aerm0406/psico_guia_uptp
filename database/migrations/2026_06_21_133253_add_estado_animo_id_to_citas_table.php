<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (!Schema::hasColumn('citas', 'estado_animo_id')) {
                $table->unsignedBigInteger('estado_animo_id')->nullable()->after('estado');
                $table->foreign('estado_animo_id')->references('id')->on('estado_animos')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (Schema::hasColumn('citas', 'estado_animo_id')) {
                $table->dropForeign(['estado_animo_id']);
                $table->dropColumn('estado_animo_id');
            }
        });
    }
};
