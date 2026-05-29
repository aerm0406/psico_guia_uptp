<?php

namespace App\Models;

class SeccionPersonalizada
{
    public static function obtenerPorId($id)
    {
        return \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')->where('id', $id)->first();
    }

    /**
     * Obtiene el historial clínico al que pertenece esta sección.
     */
    public static function obtenerHistoriaClinica($historiaClinicaId)
    {
        return \Illuminate\Support\Facades\DB::table('historia_clinicas')->where('id', $historiaClinicaId)->first();
    }

    /**
     * Obtiene los segmentos que componen esta sección.
     */
    public static function obtenerSegmentos($seccionId)
    {
        return \Illuminate\Support\Facades\DB::table('historia_segmentos_personalizados')
            ->where('seccion_id', $seccionId)
            ->orderBy('orden')
            ->get();
    }

    /**
     * Registra una nueva sección personalizada y sus segmentos asociados.
     */
    public static function crear($historia, $data)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $seccionId = \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')->insertGetId([
                'historia_clinica_id' => is_object($historia) ? $historia->id : $historia,
                'titulo'              => $data['titulo'],
                'descripcion_general' => $data['descripcion_general'] ?? null,
                'orden'               => \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')->where('historia_clinica_id', is_object($historia) ? $historia->id : $historia)->count() + 1,
                'status'              => 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            if (!empty($data['segmentos_titulos'])) {
                foreach ($data['segmentos_titulos'] as $index => $titulo) {
                    \Illuminate\Support\Facades\DB::table('historia_segmentos_personalizados')->insert([
                        'seccion_id' => $seccionId,
                        'titulo' => $titulo,
                        'orden' => $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $psicologoId = \Illuminate\Support\Facades\Auth::id();
            $plantilla = \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')
                ->where('psicologo_id', $psicologoId)
                ->where('titulo', $data['titulo'])
                ->first();
                
            if ($plantilla) {
                \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')->where('id', $plantilla->id)
                    ->update(['updated_at' => now()]);
            } else {
                \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')->insert([
                    'psicologo_id' => $psicologoId,
                    'titulo' => $data['titulo'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $seccion = \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')->where('id', $seccionId)->first();
            \Illuminate\Support\Facades\DB::commit();
            return $seccion;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina la sección personalizada del expediente.
     */
    public static function eliminar($id)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            // Soft delete: marcar como inactiva en vez de borrar físicamente
            $res = \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')
                ->where('id', $id)
                ->update([
                    'status'     => 0,
                    'updated_at' => now(),
                ]);
            \Illuminate\Support\Facades\DB::commit();
            return $res;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }

    /**
     * Intercambia el orden de la sección personalizada con su predecesora o sucesora.
     */
    public static function reordenar($seccionId, $direccion)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $seccion = self::obtenerPorId($seccionId);
            if (!$seccion || $seccion->status != 1) {
                \Illuminate\Support\Facades\DB::rollBack();
                return false;
            }

            $historiaId = $seccion->historia_clinica_id;
            $ordenActual = $seccion->orden;

            if ($direccion === 'up') {
                // Buscar la sección activa inmediatamente anterior (orden menor más cercano)
                $otraSeccion = \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')
                    ->where('historia_clinica_id', $historiaId)
                    ->where('status', 1)
                    ->where('orden', '<', $ordenActual)
                    ->orderBy('orden', 'desc')
                    ->first();
            } else {
                // Buscar la sección activa inmediatamente posterior (orden mayor más cercano)
                $otraSeccion = \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')
                    ->where('historia_clinica_id', $historiaId)
                    ->where('status', 1)
                    ->where('orden', '>', $ordenActual)
                    ->orderBy('orden', 'asc')
                    ->first();
            }

            if ($otraSeccion) {
                // Intercambiar los valores de la columna 'orden'
                \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')
                    ->where('id', $seccion->id)
                    ->update(['orden' => $otraSeccion->orden, 'updated_at' => now()]);

                \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')
                    ->where('id', $otraSeccion->id)
                    ->update(['orden' => $ordenActual, 'updated_at' => now()]);
            }

            \Illuminate\Support\Facades\DB::commit();
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }
}
