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
            ->where('status', 1)
            ->orderBy('titulo')
            ->paginate(8);
    }

    public static function obtenerPorId($id, $psicologoId)
    {
        return \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->first();
    }

    /**
     * Verifica si ya existe una plantilla con el mismo título para el psicólogo.
     * @param string $titulo
     * @param int $psicologoId
     * @param int|null $excluirId ID a excluir (para edición)
     * @return bool
     */
    public static function existeTitulo($titulo, $psicologoId, $excluirId = null)
    {
        $query = \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->where('titulo', $titulo);

        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }

    public static function crear($psicologoId, $data)
    {
        return \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')->insertGetId([
            'psicologo_id' => $psicologoId,
            'titulo' => $data['titulo'],
            'descripcion_general' => $data['descripcion_general'] ?? null,
            'segmentos' => isset($data['segmentos']) ? json_encode($data['segmentos']) : null,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => null,
        ]);
    }

    public static function actualizar($id, $psicologoId, $data)
    {
        return \Illuminate\Support\Facades\DB::table('historia_plantillas_secciones')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
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
            ->update(['status' => 0]);
    }

    /**
     * Verifica si una plantilla de sección está en uso en algún expediente.
     * Como no hay foreign key directa, validamos por el título de la sección y los expedientes del psicólogo.
     */
    public static function estaEnUso($id, $psicologoId)
    {
        $plantilla = self::obtenerPorId($id, $psicologoId);
        if (!$plantilla) return false;

        $titulosUso = \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas as hs')
            ->join('historia_clinicas as hc', 'hs.historia_clinica_id', '=', 'hc.id')
            ->where('hc.psicologo_id', $psicologoId)
            ->where('hs.titulo', $plantilla->titulo)
            ->exists();

        return $titulosUso;
    }
}
