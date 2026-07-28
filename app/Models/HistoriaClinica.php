<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class HistoriaClinica
{
    /**
     * El paciente al que pertenece esta historia.
     */
    public static function obtenerPaciente($userId)
    {
        return DB::table('users')->where('id', $userId)->first();
    }

    /**
     * El psicólogo que gestiona esta historia.
     */
    public static function obtenerPsicologo($psicologoId)
    {
        return DB::table('users')->where('id', $psicologoId)->first();
    }

    /**
     * Obtiene las secciones personalizadas añadidas por el psicólogo.
     */
    public static function obtenerSeccionesPersonalizadas($historiaId)
    {
        return DB::table('historia_secciones_personalizadas')
            ->where('historia_clinica_id', $historiaId)
            ->orderBy('orden')
            ->get();
    }

    /**
     * Busca la historia clínica de un paciente específico e hidrata el modelo
     * para mantener el descifrado automático.
     */
    public static function obtenerPorPaciente($pacienteId)
    {
        return \Illuminate\Support\Facades\DB::table('historia_clinicas')->where('user_id', $pacienteId)->first();
    }

    /**
     * Crea o recupera el registro inicial de una historia clínica.
     */
    public static function iniciarHistoria($pacienteId, $psicologoId)
    {
        try {
            DB::beginTransaction();
            $historia = self::obtenerPorPaciente($pacienteId);

            if (!$historia) {
                $id = \Illuminate\Support\Facades\DB::table('historia_clinicas')->insertGetId([
                    'user_id' => $pacienteId,
                    'psicologo_id' => $psicologoId,
                    'created_at' => now(),
                    'updated_at' => null
                ]);
                $historia = self::obtenerPorPaciente($pacienteId);
            }

            $seccionesActivas = DB::table('historia_secciones_personalizadas')
                ->where('historia_clinica_id', $historia->id)
                ->where('status', 1)
                ->count();

            if ($seccionesActivas === 0) {
                // Obtener la última plantilla global del psicólogo usando Query Builder
                $plantillaGlobal = DB::table('historia_plantillas_globales')
                    ->where('psicologo_id', $psicologoId)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($plantillaGlobal) {
                    $secciones = json_decode($plantillaGlobal->secciones, true) ?? [];
                    $maxOrden = 0;

                    foreach ($secciones as $seccionData) {
                        $maxOrden++;
                        $seccionId = DB::table('historia_secciones_personalizadas')->insertGetId([
                            'historia_clinica_id' => $historia->id,
                            'titulo' => $seccionData['titulo'],
                            'descripcion_general' => $seccionData['descripcion_general'] ?? null,
                            'orden' => $maxOrden,
                            'status' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $segmentos = $seccionData['segmentos'] ?? [];
                        foreach ($segmentos as $indexSeg => $segmentoTitulo) {
                            if (!empty(trim($segmentoTitulo))) {
                                DB::table('historia_segmentos_personalizados')->insert([
                                    'seccion_id' => $seccionId,
                                    'titulo' => $segmentoTitulo,
                                    'contenido' => null,
                                    'orden' => $indexSeg + 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return $historia;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Busca pacientes que tienen relación con un psicólogo específico.
     */
    public static function buscarPacientes($query, $psicologoId)
    {
        return \Illuminate\Support\Facades\DB::table('users')
            ->join('citas', 'users.id', '=', 'citas.user_id')
            ->where('citas.psicologo_id', $psicologoId)
            ->where(function($q) use ($query) {
                $q->where('users.nombres', 'like', '%' . $query . '%')
                  ->orWhere('users.apellidos', 'like', '%' . $query . '%')
                  ->orWhere('users.cedula', 'like', '%' . $query . '%');
            })
            ->select('users.id', \Illuminate\Support\Facades\DB::raw("CONCAT(nombres, ' ', apellidos) as name"), 'users.email')
            ->distinct()
            ->get();
    }

    /**
     * Obtiene el listado de pacientes atendidos por un psicólogo.
     */
    public static function obtenerListado($psicologoId, $search = null, $filters = [])
    {
        $query = \Illuminate\Support\Facades\DB::table('citas')
            ->join('users', 'citas.user_id', '=', 'users.id')
            ->leftJoin('historia_clinicas', 'users.id', '=', 'historia_clinicas.user_id')
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'realizada');

        if (!empty($search)) {
            $query->leftJoin('historia_enfermedad', 'historia_clinicas.id', '=', 'historia_enfermedad.historia_clinica_id')
                  ->leftJoin('enfermedades', 'historia_enfermedad.enfermedad_id', '=', 'enfermedades.id')
                  ->where(function($q) use ($search) {
                      $q->where('users.nombres', 'like', "%{$search}%")
                        ->orWhere('users.apellidos', 'like', "%{$search}%")
                        ->orWhere('users.cedula', 'like', "%{$search}%");
                  });
        }

        if (!empty($filters['pnf'])) {
            $query->where('users.pnf', $filters['pnf']);
        }

        if (!empty($filters['edad'])) {
            $birthDate = \Carbon\Carbon::now()->subYears($filters['edad'])->format('Y-m-d');
            // Assuming we match the exact year:
            $query->whereYear('users.fecha_nacimiento', \Carbon\Carbon::now()->subYears($filters['edad'])->year);
        }

        // Filtrado por fecha según el tipo seleccionado
        $tipoFiltroFecha = $filters['tipo_filtro_fecha'] ?? 'rango';
        $fechaDesde = $filters['fecha_desde'] ?? null;
        $fechaHasta = $filters['fecha_hasta'] ?? null;

        if (!empty($fechaDesde) || !empty($fechaHasta)) {
            if ($tipoFiltroFecha === 'primera_cita') {
                // Filtrar pacientes cuya PRIMERA cita realizada caiga en el rango
                $subquery = \Illuminate\Support\Facades\DB::table('citas as sub_citas')
                    ->select('sub_citas.user_id')
                    ->where('sub_citas.psicologo_id', $psicologoId)
                    ->where('sub_citas.estado', 'realizada')
                    ->groupBy('sub_citas.user_id');

                if (!empty($fechaDesde)) {
                    $subquery->havingRaw('MIN(sub_citas.fecha) >= ?', [$fechaDesde]);
                }
                if (!empty($fechaHasta)) {
                    $subquery->havingRaw('MIN(sub_citas.fecha) <= ?', [$fechaHasta]);
                }

                $pacientesIds = $subquery->pluck('user_id');
                $query->whereIn('users.id', $pacientesIds);
            } elseif ($tipoFiltroFecha === 'ultima_cita') {
                // Filtrar pacientes cuya ÚLTIMA cita realizada caiga en el rango
                $subquery = \Illuminate\Support\Facades\DB::table('citas as sub_citas')
                    ->select('sub_citas.user_id')
                    ->where('sub_citas.psicologo_id', $psicologoId)
                    ->where('sub_citas.estado', 'realizada')
                    ->groupBy('sub_citas.user_id');

                if (!empty($fechaDesde)) {
                    $subquery->havingRaw('MAX(sub_citas.fecha) >= ?', [$fechaDesde]);
                }
                if (!empty($fechaHasta)) {
                    $subquery->havingRaw('MAX(sub_citas.fecha) <= ?', [$fechaHasta]);
                }

                $pacientesIds = $subquery->pluck('user_id');
                $query->whereIn('users.id', $pacientesIds);
            } else {
                // Rango normal: filtra por la fecha de cualquier cita
                if (!empty($fechaDesde)) {
                    $query->whereDate('citas.fecha', '>=', $fechaDesde);
                }
                if (!empty($fechaHasta)) {
                    $query->whereDate('citas.fecha', '<=', $fechaHasta);
                }
            }
        }

        if (!empty($filters['prioridad'])) {
            $query->where('citas.prioridad', $filters['prioridad']);
        }

        if (!empty($filters['estado_animo_id'])) {
            $query->where('citas.estado_animo_id', $filters['estado_animo_id']);
        }

        if (!empty($filters['enfermedad_id'])) {
            $query->leftJoin('historia_enfermedad as he_filter', 'historia_clinicas.id', '=', 'he_filter.historia_clinica_id')
                  ->where('he_filter.enfermedad_id', $filters['enfermedad_id'])
                  ->where('he_filter.status', 1);
        }

        $historiasBase = $query->select(
                'users.id',
                \Illuminate\Support\Facades\DB::raw("CONCAT(users.nombres, ' ', users.apellidos) as patient_name"),
                'users.email',
                'citas.fecha as ultima_sesion',
                'citas.notas'
            )
            ->orderBy('citas.fecha', 'desc')
            ->get()
            ->unique('id');

        if (!empty($filters['avance_id'])) {
            $historiasBase = $historiasBase->filter(function($item) use ($filters) {
                if (empty($item->notas)) return false;
                try {
                    $notasDecrypted = \Illuminate\Support\Facades\Crypt::decryptString($item->notas);
                    $notasArr = json_decode($notasDecrypted, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($notasArr)) {
                        return isset($notasArr['avance_estado']) && $notasArr['avance_estado'] == $filters['avance_id'];
                    }
                } catch (\Exception $e) {}
                return false;
            });
        }

        return $historiasBase->map(function ($item) use ($psicologoId) {
            $h = self::obtenerPorPaciente($item->id);
            $citasRealizadas = \Illuminate\Support\Facades\DB::table('citas')
                ->where('user_id', $item->id)
                ->where('psicologo_id', $psicologoId)
                ->where('estado', 'realizada')
                ->where('status', 1)
                ->get(['id', 'motivo']);

            $countCitas = $citasRealizadas->filter(function($cita) {
                try {
                    return \Illuminate\Support\Facades\Crypt::decryptString($cita->motivo) !== 'Nota de Evolución (Manual)';
                } catch (\Exception $e) {
                    return true;
                }
            })->count();

            // Obtener el paciente y añadir propiedades compatibles con la vista (Query Builder retorna stdClass)
            $paciente = self::obtenerPaciente($item->id);
            if ($paciente) {
                $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));
                $paciente->avatar = strtoupper(
                    substr($paciente->nombres ?? '', 0, 1) . substr($paciente->apellidos ?? '', 0, 1)
                );
            }

            $diagnosticoText = 'Sin diagnóstico';
            if ($h) {
                $diagSegment = DB::table('historia_secciones_personalizadas')
                    ->join('historia_segmentos_personalizados', 'historia_secciones_personalizadas.id', '=', 'historia_segmentos_personalizados.seccion_id')
                    ->where('historia_secciones_personalizadas.historia_clinica_id', $h->id)
                    ->where('historia_secciones_personalizadas.titulo', 'Diagnóstico')
                    ->where('historia_segmentos_personalizados.titulo', 'Diagnóstico Inicial (Resumen)')
                    ->first();
                if ($diagSegment && !empty($diagSegment->contenido)) {
                    try {
                        $diagnosticoText = \Illuminate\Support\Facades\Crypt::decryptString($diagSegment->contenido);
                    } catch (\Exception $e) {
                        // ignore or use raw
                    }
                }
            }

            return [
                'id'              => $item->id,
                'paciente_name'   => $item->patient_name,
                'email'           => $item->email,
                'ultima_sesion'   => $item->ultima_sesion,
                'notas'           => $item->notas,
                'citas_realizadas'=> $countCitas,
                'diagnostico'     => $diagnosticoText,
                'paciente'        => $paciente,
            ];
        });
    }

    /**
     * Vincula una enfermedad o condición médica a la historia clínica.
     */
    public static function vincularEnfermedad($historiaId, $enfermedadId, $contexto)
    {
        try {
            DB::beginTransaction();
            // Evitar duplicados exactos en toda la sección
            $existe = null;
            if (str_starts_with($contexto, 'seg_')) {
                $segmentoId = str_replace('seg_', '', $contexto);
                $segmento = \Illuminate\Support\Facades\DB::table('historia_segmentos_personalizados')->where('id', $segmentoId)->first();
                if ($segmento) {
                    $segmentosSeccion = \Illuminate\Support\Facades\DB::table('historia_segmentos_personalizados')
                        ->where('seccion_id', $segmento->seccion_id)
                        ->pluck('id')
                        ->map(fn($id) => 'seg_' . $id)
                        ->toArray();

                    $existe = \Illuminate\Support\Facades\DB::table('historia_enfermedad')
                        ->where('historia_clinica_id', $historiaId)
                        ->where('enfermedad_id', $enfermedadId)
                        ->whereIn('contexto', $segmentosSeccion)
                        ->first();
                }
            } else {
                $existe = \Illuminate\Support\Facades\DB::table('historia_enfermedad')
                    ->where('historia_clinica_id', $historiaId)
                    ->where('enfermedad_id', $enfermedadId)
                    ->where('contexto', $contexto)
                    ->first();
            }

            if (!$existe) {
                $id = \Illuminate\Support\Facades\DB::table('historia_enfermedad')->insertGetId([
                    'historia_clinica_id' => $historiaId,
                    'enfermedad_id' => $enfermedadId,
                    'contexto' => $contexto,
                    'created_at' => now(),
                ]);
                
                $enfermedad = \Illuminate\Support\Facades\DB::table('enfermedades')->where('id', $enfermedadId)->first();
                DB::commit();
                return [
                    'success' => true,
                    'link_id' => $id,
                    'nombre' => $enfermedad->nombre,
                    'contexto' => $contexto
                ];
            }
            
            DB::commit();
            return ['success' => false, 'message' => 'Ya está vinculada'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error al vincular: ' . $e->getMessage()];
        }
    }

    /**
     * Elimina el vínculo de una enfermedad con la historia clínica.
     */
    public static function desvincularEnfermedad($linkId)
    {        
        try {
            DB::beginTransaction();
            \Illuminate\Support\Facades\DB::table('historia_enfermedad')
                ->where('id', $linkId)
                ->update([
                    'status' => 0,
                    'updated_at' => now()
                ]);
            DB::commit();
            return [true, 'Enfermedad desvinculada correctamente.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al desvincular: ' . $e->getMessage()];
        }
    }

    // (Método actualizarHistoria eliminado ya que no es necesario para el expediente dinámico)

    /**
     * Obtiene todas las enfermedades vinculadas agrupadas por su tipo de antecedente.
     */
    public static function obtenerEnfermedadesVinculadas($historiaId)
    {
        return \Illuminate\Support\Facades\DB::table('historia_enfermedad')
            ->join('enfermedades', 'historia_enfermedad.enfermedad_id', '=', 'enfermedades.id')
            ->where('historia_enfermedad.historia_clinica_id', $historiaId)
            ->where('historia_enfermedad.status', 1)
            ->select('enfermedades.*', 'historia_enfermedad.id as link_id', 'historia_enfermedad.contexto')
            ->get()
            ->groupBy('contexto');
    }

    /**
     * Verifica si un psicólogo tiene permiso para acceder a la historia de un paciente.
     */
    public static function verificarAcceso($pacienteId, $psicologoId)
    {
        return \Illuminate\Support\Facades\DB::table('historia_clinicas')
            ->where('user_id', $pacienteId)
            ->where('psicologo_id', $psicologoId)
            ->first();
    }

    /**
     * Obtiene una historia clínica específica por su ID.
     */
    public static function obtenerPorId($id)
    {
        return \Illuminate\Support\Facades\DB::table('historia_clinicas')
            ->where('id', $id)
            ->first();
    }

    /**
     * Busca la historia clínica y genera un error si no se encuentra.
     */
    public static function obtenerPorPacienteOrFail($pacienteId)
    {
        $historia = self::obtenerPorPaciente($pacienteId);

        if (!$historia) {
            abort(404);
        }

        return $historia;
    }

    /**
     * Recupera las secciones y segmentos personalizados del expediente.
     */
    public static function obtenerSeccionesConSegmentos($historiaId)
    {
        $secciones = \Illuminate\Support\Facades\DB::table('historia_secciones_personalizadas')
            ->where('historia_clinica_id', $historiaId)
            ->where('status', 1)
            ->orderBy('orden')
            ->get();

        foreach ($secciones as $seccion) {
            $seccion->segmentos = \Illuminate\Support\Facades\DB::table('historia_segmentos_personalizados')
                ->where('seccion_id', $seccion->id)
                ->get()
                ->map(function ($segmento) {
                    if (!empty($segmento->contenido)) {
                        try {
                            $segmento->contenido = \Illuminate\Support\Facades\Crypt::decryptString($segmento->contenido);
                        } catch (\Exception $e) {
                            // Ignorar error de descifrado
                        }
                    }
                    return $segmento;
                });
        }

        return $secciones;
    }
}
