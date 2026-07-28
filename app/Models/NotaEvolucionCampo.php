<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class NotaEvolucionCampo
{
    /**
     * Obtener todos los campos disponibles para un psicólogo (los suyos y los del sistema).
     */
    public static function obtenerCamposDisponibles($psicologoId)
    {
        return DB::table('nota_evolucion_campos')
            ->where(function ($query) use ($psicologoId) {
                $query->where('psicologo_id', $psicologoId)
                      ->orWhereNull('psicologo_id');
            })
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Obtener los campos disponibles paginados para la vista de gestión.
     */
    public static function obtenerCamposDisponiblesPaginados($psicologoId, $perPage = 9)
    {
        return DB::table('nota_evolucion_campos')
            ->where(function ($query) use ($psicologoId) {
                $query->where('psicologo_id', $psicologoId)
                      ->orWhereNull('psicologo_id');
            })
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Crear un nuevo campo personalizado para el psicólogo.
     */
    public static function crearPersonalizado($psicologoId, $titulo)
    {
        return DB::table('nota_evolucion_campos')->insertGetId([
            'psicologo_id' => $psicologoId,
            'titulo' => $titulo,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => null,
        ]);
    }

    /**
     * Verifica si ya existe un campo con el mismo título para evitar duplicados globales/locales.
     */
    public static function existeTitulo($psicologoId, $titulo, $excludeId = null)
    {
        $query = DB::table('nota_evolucion_campos')
            ->where(function ($q) use ($psicologoId) {
                $q->where('psicologo_id', $psicologoId)
                  ->orWhereNull('psicologo_id');
            })
            ->where('status', 1)
            ->where('titulo', $titulo);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Obtener los campos creados específicamente por este psicólogo.
     */
    public static function obtenerPorPsicologo($psicologoId)
    {
        return DB::table('nota_evolucion_campos')
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Obtener un campo específico por ID, validando que pertenezca al psicólogo.
     */
    public static function obtenerPorId($id, $psicologoId)
    {
        return DB::table('nota_evolucion_campos')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->first();
    }

    /**
     * Actualizar el título de un campo.
     */
    public static function actualizar($id, $psicologoId, $titulo)
    {
        return DB::table('nota_evolucion_campos')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->update([
                'titulo' => $titulo,
                'updated_at' => now(),
            ]);
    }

    /**
     * Eliminar lógicamente un campo (cambiar status a 0).
     */
    public static function eliminar($id, $psicologoId)
    {
        return DB::table('nota_evolucion_campos')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->update([
                'status' => 0,
                'updated_at' => now(),
            ]);
    }
}
