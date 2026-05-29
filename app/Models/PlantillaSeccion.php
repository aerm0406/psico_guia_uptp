<?php

namespace App\Models;

class PlantillaSeccion
{
    /**
     * El psicólogo dueño de esta plantilla.
     */
    public static function obtenerPsicologo($psicologoId)
    {
        return \Illuminate\Support\Facades\DB::table('users')->where('id', $psicologoId)->first();
    }

    public static function obtenerPorPsicologo($psicologoId)
    {
        return \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')
            ->where('psicologo_id', $psicologoId)
            ->orderBy('titulo')
            ->get();
    }

    public static function obtenerPorId($id, $psicologoId)
    {
        return \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->first();
    }

    public static function crear($psicologoId, $data)
    {
        return \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')->insertGetId([
            'psicologo_id' => $psicologoId,
            'titulo' => $data['titulo'],
            'descripcion_general' => $data['descripcion_general'] ?? null,
            'segmentos' => isset($data['segmentos']) ? json_encode($data['segmentos']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function actualizar($id, $psicologoId, $data)
    {
        return \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->update([
                'titulo' => $data['titulo'],
                'descripcion_general' => $data['descripcion_general'] ?? null,
                'segmentos' => isset($data['segmentos']) ? json_encode($data['segmentos']) : null,
                'updated_at' => now(),
            ]);
    }

    public static function eliminar($id, $psicologoId)
    {
        return \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->delete();
    }
}
