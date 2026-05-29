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
                // 1. Diagnóstico
                $diagId = DB::table('historia_secciones_personalizadas')->insertGetId([
                    'historia_clinica_id' => $historia->id,
                    'titulo' => 'Diagnóstico',
                    'descripcion_general' => 'Diagnóstico y Plan de Tratamiento',
                    'orden' => 1,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                DB::table('historia_segmentos_personalizados')->insert([
                    [
                        'seccion_id' => $diagId,
                        'titulo' => 'Diagnóstico Inicial (Resumen)',
                        'contenido' => null,
                        'orden' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                    [
                        'seccion_id' => $diagId,
                        'titulo' => 'Plan de Tratamiento',
                        'contenido' => null,
                        'orden' => 2,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);

                // 2. Antecedentes Personales
                $persId = DB::table('historia_secciones_personalizadas')->insertGetId([
                    'historia_clinica_id' => $historia->id,
                    'titulo' => 'Antecedentes Personales',
                    'descripcion_general' => 'Salud e historial del paciente',
                    'orden' => 2,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                DB::table('historia_segmentos_personalizados')->insert([
                    [
                        'seccion_id' => $persId,
                        'titulo' => 'Salud Mental / Psiquiátrico',
                        'contenido' => null,
                        'orden' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                    [
                        'seccion_id' => $persId,
                        'titulo' => 'Salud General',
                        'contenido' => null,
                        'orden' => 2,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);

                // 3. Antecedentes Familiares
                $famId = DB::table('historia_secciones_personalizadas')->insertGetId([
                    'historia_clinica_id' => $historia->id,
                    'titulo' => 'Antecedentes Familiares',
                    'descripcion_general' => 'Historial hereditario y dinámicas',
                    'orden' => 3,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                DB::table('historia_segmentos_personalizados')->insert([
                    [
                        'seccion_id' => $famId,
                        'titulo' => 'Salud Mental',
                        'contenido' => null,
                        'orden' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                    [
                        'seccion_id' => $famId,
                        'titulo' => 'Salud General',
                        'contenido' => null,
                        'orden' => 2,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);
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
    public static function obtenerListado($psicologoId)
    {
        $historiasBase = \Illuminate\Support\Facades\DB::table('citas')
            ->join('users', 'citas.user_id', '=', 'users.id')
            ->leftJoin('historia_clinicas', 'users.id', '=', 'historia_clinicas.user_id')
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'realizada')
            ->select(
                'users.id',
                \Illuminate\Support\Facades\DB::raw("CONCAT(users.nombres, ' ', users.apellidos) as patient_name"),
                'users.email',
                'citas.fecha as ultima_sesion',
                'citas.notas'
            )
            ->orderBy('citas.fecha', 'desc')
            ->get()
            ->unique('id');

        return $historiasBase->map(function ($item) use ($psicologoId) {
            $h = self::obtenerPorPaciente($item->id);
            $countCitas = \Illuminate\Support\Facades\DB::table('citas')
                ->where('user_id', $item->id)
                ->where('psicologo_id', $psicologoId)
                ->where('estado', 'realizada')
                ->count();

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
            // Evitar duplicados exactos
            $existe = \Illuminate\Support\Facades\DB::table('historia_enfermedad')
                ->where('historia_clinica_id', $historiaId)
                ->where('enfermedad_id', $enfermedadId)
                ->where('contexto', $contexto)
                ->first();

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
