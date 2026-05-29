<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Horario
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public static function obtenerUsuario($userId)
    {
        return DB::table('users')->where('id', $userId)->first();
    }

    public static function obtenerGrupoHorario($grupoHorarioId)
    {
        return DB::table('grupos_horarios')->where('id', $grupoHorarioId)->first();
    }

    public static function obtenerPorId($id)
    {
        return DB::table('horarios')->where('id', $id)->first();
    }

    /**
     * Devuelve los días de la semana laborables.
     */
    public static function diasSemana(): array
    {
        // Dias de la semana laborables
        return [
            'Lunes',
            'Martes',
            'Miércoles',
            'Jueves',
            'Viernes',
        ];
    }

    /**
     * Elimina lógicamente un bloque de horario.
     */
    public static function eliminar($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('horarios')->where('id', $id)->update([
                'activo' => self::STATUS_DELETED,
                'updated_at' => Carbon::now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function asignarGrupo($horarioIds, $grupoId)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('horarios')
                ->whereIn('id', $horarioIds)
                ->update(['grupo_horario_id' => $grupoId, 'activo' => self::STATUS_ACTIVE]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Crea un nuevo bloque de horario.
     */
    public static function crear($data)
    {
        try {
            DB::beginTransaction();
            $id = DB::table('horarios')->insertGetId(array_merge($data, [
                'created_at' => Carbon::now(),
                'updated_at' => null,
            ]));
            DB::commit();
            return $id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un bloque de horario.
     */
    public static function actualizar($id, $data)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('horarios')
                ->where('id', $id)
                ->update(array_merge($data, ['updated_at' => Carbon::now()]));
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Verifica si un psicólogo tiene citas activas que impidan cambios en el horario.
     */
    public static function hasPendingCitas($userId)
    {
        return DB::table('citas')
            ->where('psicologo_id', $userId)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->exists();
    }

    /**
     * Valida si un nuevo bloque horario se superpone con uno existente para el mismo usuario y grupo.
     */
    public static function overlaps($userId, $dia, $inicio, $fin, $excludeId = null, $grupoId = null)
    {
        $query = DB::table('horarios')
            ->where('user_id', $userId)
            ->where('dia', $dia)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE]);

        if ($grupoId) {
            $query->where('grupo_horario_id', $grupoId);
        } else {
            $query->whereNull('grupo_horario_id');
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $query->where(function ($q) use ($inicio, $fin) {
            $q->whereBetween('hora_inicio', [$inicio, $fin])
                ->orWhereBetween('hora_fin', [$inicio, $fin])
                ->orWhere(function ($q2) use ($inicio, $fin) {
                    $q2->where('hora_inicio', '<', $inicio)
                        ->where('hora_fin', '>', $fin);
                });
        });

        $overlaps = $query->get();
        foreach ($overlaps as $match) {
            if ($match->hora_inicio == $fin || $match->hora_fin == $inicio) {
                continue;
            }
            return true;
        }

        return false;
    }

    /**
     * Normaliza el formato de hora a H:i.
     */
    public static function normalizeTime($time)
    {
        if (empty($time)) return null;
        return Carbon::parse($time)->format('H:i');
    }

    /**
     * Obtiene los horarios asociados a un grupo específico.
     */
    public static function obtenerPorGrupo($grupoId)
    {
        return DB::table('horarios')
            ->where('grupo_horario_id', $grupoId)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE])
            ->orderByRaw("FIELD(dia, 'Lunes','Martes','Miércoles','Jueves','Viernes')")
            ->orderBy('hora_inicio')
            ->get();
    }

    /**
     * Obtiene los horarios filtrados para un usuario.
     */
    /**
     * Obtiene los horarios filtrados por usuario, grupo y día.
     */
    public static function obtenerPorFiltros($userId, $grupoId = null, $filtroDia = null)
    {
        $query = DB::table('horarios')
            ->where('user_id', $userId)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE]);

        if ($grupoId) {
            $query->where('grupo_horario_id', $grupoId);
        } else {
            $query->whereNull('grupo_horario_id');
        }

        if ($filtroDia) {
            $query->where('dia', $filtroDia);
        }

        return $query->orderByRaw("FIELD(dia, 'Lunes','Martes','Miércoles','Jueves','Viernes')")
            ->orderBy('hora_inicio')
            ->get();
    }

    /**
     * Verifica si el usuario tiene al menos un bloque de horario registrado.
     */
    public static function hasBloques($userId)
    {
        return DB::table('horarios')
            ->where('user_id', $userId)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE])
            ->exists();
    }
}
