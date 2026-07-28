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
        $cleanString = function($str) {
            $str = trim(mb_strtolower($str));
            $str = str_replace(
                ['á','é','í','ó','ú','ä','ë','ï','ö','ü','ñ'],
                ['a','e','i','o','u','a','e','i','o','u','n'],
                $str
            );
            return $str;
        };

        $r1 = \Illuminate\Support\Facades\Hash::make($cleanString('azul'));
        $r2 = \Illuminate\Support\Facades\Hash::make($cleanString('torta'));
        $r3 = \Illuminate\Support\Facades\Hash::make($cleanString('acarigua'));

        \Illuminate\Support\Facades\DB::table('users')
            ->whereNull('pregunta_seguridad_1')
            ->update([
                'pregunta_seguridad_1' => '¿Cuál es tu color favorito?',
                'respuesta_seguridad_1' => $r1,
                'pregunta_seguridad_2' => '¿Cuál es tu postre favorito?',
                'respuesta_seguridad_2' => $r2,
                'pregunta_seguridad_3' => '¿En qué ciudad naciste?',
                'respuesta_seguridad_3' => $r3,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
