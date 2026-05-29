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
        $decrypt = function($value) {
            if (empty($value)) return null;
            try {
                return \Illuminate\Support\Facades\Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        };

        $encrypt = function($value) {
            if (empty($value)) return null;
            return \Illuminate\Support\Facades\Crypt::encryptString($value);
        };

        $historias = DB::table('historia_clinicas')->get();

        foreach ($historias as $historia) {
            $hasDiag = DB::table('historia_secciones_personalizadas')
                ->where('historia_clinica_id', $historia->id)
                ->where('titulo', 'Diagnóstico')
                ->exists();

            if (!$hasDiag) {
                // Desplazar las secciones personalizadas existentes (+3) para dar espacio a las estándar
                $hasOtherSections = DB::table('historia_secciones_personalizadas')
                    ->where('historia_clinica_id', $historia->id)
                    ->exists();
                if ($hasOtherSections) {
                    DB::table('historia_secciones_personalizadas')
                        ->where('historia_clinica_id', $historia->id)
                        ->increment('orden', 3);
                }

                // 1. Diagnóstico
                $diagId = DB::table('historia_secciones_personalizadas')->insertGetId([
                    'historia_clinica_id' => $historia->id,
                    'titulo' => 'Diagnóstico',
                    'icono' => '🩺',
                    'descripcion_general' => 'Diagnóstico y Plan de Tratamiento',
                    'orden' => 1,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $segDiagIniId = DB::table('historia_segmentos_personalizados')->insertGetId([
                    'seccion_id' => $diagId,
                    'titulo' => 'Diagnóstico Inicial (Resumen)',
                    'contenido' => $encrypt($decrypt($historia->diagnostico_inicial ?? null)),
                    'orden' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $segPlanId = DB::table('historia_segmentos_personalizados')->insertGetId([
                    'seccion_id' => $diagId,
                    'titulo' => 'Plan de Tratamiento',
                    'contenido' => $encrypt($decrypt($historia->plan_tratamiento ?? null)),
                    'orden' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 2. Antecedentes Personales
                $persId = DB::table('historia_secciones_personalizadas')->insertGetId([
                    'historia_clinica_id' => $historia->id,
                    'titulo' => 'Antecedentes Personales',
                    'icono' => '👤',
                    'descripcion_general' => 'Salud e historial del paciente',
                    'orden' => 2,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $ordenSegmentoPers = 1;
                $antecPersonalesDecrypted = $decrypt($historia->antecedentes_personales ?? null);
                if (!empty($antecPersonalesDecrypted)) {
                    DB::table('historia_segmentos_personalizados')->insert([
                        'seccion_id' => $persId,
                        'titulo' => 'Antecedentes Personales (Historial)',
                        'contenido' => $encrypt($antecPersonalesDecrypted),
                        'orden' => $ordenSegmentoPers++,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $segPersPsqId = DB::table('historia_segmentos_personalizados')->insertGetId([
                    'seccion_id' => $persId,
                    'titulo' => 'Salud Mental / Psiquiátrico',
                    'contenido' => $encrypt($decrypt($historia->antec_pers_psq ?? null)),
                    'orden' => $ordenSegmentoPers++,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $segPersMedId = DB::table('historia_segmentos_personalizados')->insertGetId([
                    'seccion_id' => $persId,
                    'titulo' => 'Salud General',
                    'contenido' => $encrypt($decrypt($historia->antec_pers_med ?? null)),
                    'orden' => $ordenSegmentoPers++,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 3. Antecedentes Familiares
                $famId = DB::table('historia_secciones_personalizadas')->insertGetId([
                    'historia_clinica_id' => $historia->id,
                    'titulo' => 'Antecedentes Familiares',
                    'icono' => '👥',
                    'descripcion_general' => 'Historial hereditario y dinámicas',
                    'orden' => 3,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $ordenSegmentoFam = 1;
                $antecFamiliaresDecrypted = $decrypt($historia->antecedentes_familiares ?? null);
                if (!empty($antecFamiliaresDecrypted)) {
                    DB::table('historia_segmentos_personalizados')->insert([
                        'seccion_id' => $famId,
                        'titulo' => 'Antecedentes Familiares (Historial)',
                        'contenido' => $encrypt($antecFamiliaresDecrypted),
                        'orden' => $ordenSegmentoFam++,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $segFamPsqId = DB::table('historia_segmentos_personalizados')->insertGetId([
                    'seccion_id' => $famId,
                    'titulo' => 'Salud Mental',
                    'contenido' => $encrypt($decrypt($historia->antec_fam_psq ?? null)),
                    'orden' => $ordenSegmentoFam++,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $segFamMedId = DB::table('historia_segmentos_personalizados')->insertGetId([
                    'seccion_id' => $famId,
                    'titulo' => 'Salud General',
                    'contenido' => $encrypt($decrypt($historia->antec_fam_med ?? null)),
                    'orden' => $ordenSegmentoFam++,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Migrar vínculos de enfermedades antiguos
                DB::table('historia_enfermedad')
                    ->where('historia_clinica_id', $historia->id)
                    ->where('contexto', 'diag_ini')
                    ->update(['contexto' => 'seg_' . $segDiagIniId]);

                DB::table('historia_enfermedad')
                    ->where('historia_clinica_id', $historia->id)
                    ->where('contexto', 'pers_psq')
                    ->update(['contexto' => 'seg_' . $segPersPsqId]);

                DB::table('historia_enfermedad')
                    ->where('historia_clinica_id', $historia->id)
                    ->where('contexto', 'pers_med')
                    ->update(['contexto' => 'seg_' . $segPersMedId]);

                DB::table('historia_enfermedad')
                    ->where('historia_clinica_id', $historia->id)
                    ->where('contexto', 'fam_psq')
                    ->update(['contexto' => 'seg_' . $segFamPsqId]);

                DB::table('historia_enfermedad')
                    ->where('historia_clinica_id', $historia->id)
                    ->where('contexto', 'fam_med')
                    ->update(['contexto' => 'seg_' . $segFamMedId]);
            } else {
                // Si la historia ya tiene la sección Diagnóstico, verificar datos legados en antecedentes
                $antecPersonalesDecrypted = $decrypt($historia->antecedentes_personales ?? null);
                if (!empty($antecPersonalesDecrypted)) {
                    $persSeccion = DB::table('historia_secciones_personalizadas')
                        ->where('historia_clinica_id', $historia->id)
                        ->where('titulo', 'Antecedentes Personales')
                        ->first();
                    if ($persSeccion) {
                        $exists = DB::table('historia_segmentos_personalizados')
                            ->where('seccion_id', $persSeccion->id)
                            ->where('titulo', 'Antecedentes Personales (Historial)')
                            ->exists();
                        if (!$exists) {
                            DB::table('historia_segmentos_personalizados')
                                ->where('seccion_id', $persSeccion->id)
                                ->increment('orden');
                            DB::table('historia_segmentos_personalizados')->insert([
                                'seccion_id' => $persSeccion->id,
                                'titulo' => 'Antecedentes Personales (Historial)',
                                'contenido' => $encrypt($antecPersonalesDecrypted),
                                'orden' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }

                $antecFamiliaresDecrypted = $decrypt($historia->antecedentes_familiares ?? null);
                if (!empty($antecFamiliaresDecrypted)) {
                    $famSeccion = DB::table('historia_secciones_personalizadas')
                        ->where('historia_clinica_id', $historia->id)
                        ->where('titulo', 'Antecedentes Familiares')
                        ->first();
                    if ($famSeccion) {
                        $exists = DB::table('historia_segmentos_personalizados')
                            ->where('seccion_id', $famSeccion->id)
                            ->where('titulo', 'Antecedentes Familiares (Historial)')
                            ->exists();
                        if (!$exists) {
                            DB::table('historia_segmentos_personalizados')
                                ->where('seccion_id', $famSeccion->id)
                                ->increment('orden');
                            DB::table('historia_segmentos_personalizados')->insert([
                                'seccion_id' => $famSeccion->id,
                                'titulo' => 'Antecedentes Familiares (Historial)',
                                'contenido' => $encrypt($antecFamiliaresDecrypted),
                                'orden' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }
        }

        // Eliminar las columnas obsoletas
        Schema::table('historia_clinicas', function (Blueprint $table) {
            $table->dropColumn([
                'antecedentes_personales',
                'antecedentes_familiares',
                'diagnostico_inicial',
                'plan_tratamiento',
                'antec_pers_psq',
                'antec_pers_med',
                'antec_fam_psq',
                'antec_fam_med',
                'orden_secciones'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historia_clinicas', function (Blueprint $table) {
            $table->longText('antecedentes_personales')->nullable();
            $table->longText('antecedentes_familiares')->nullable();
            $table->text('diagnostico_inicial')->nullable();
            $table->longText('plan_tratamiento')->nullable();
            $table->text('antec_pers_psq')->nullable();
            $table->text('antec_pers_med')->nullable();
            $table->text('antec_fam_psq')->nullable();
            $table->text('antec_fam_med')->nullable();
            $table->longText('orden_secciones')->nullable();
        });
    }
};
