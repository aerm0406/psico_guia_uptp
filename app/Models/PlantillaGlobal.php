<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class PlantillaGlobal
{
    /**
     * Obtiene la plantilla global única de un psicólogo.
     */
    public static function obtenerPorPsicologo($psicologoId)
    {
        $plantilla = DB::table('historia_plantillas_globales')
            ->where('psicologo_id', $psicologoId)
            ->whereIn('status', [1, 2])
            ->first();

        if ($plantilla) {
            $plantilla->secciones_decoded = json_decode($plantilla->secciones, true) ?? [];
        }

        return $plantilla;
    }

    /**
     * Obtiene una plantilla global por su ID, validando propiedad.
     */
    public static function obtenerPorId($id, $psicologoId)
    {
        $plantilla = DB::table('historia_plantillas_globales')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->first();

        if ($plantilla) {
            $plantilla->secciones_decoded = json_decode($plantilla->secciones, true) ?? [];
        }

        return $plantilla;
    }

    /**
     * Actualiza la plantilla global existente.
     */
    public static function actualizar($psicologoId, $data)
    {
        return DB::table('historia_plantillas_globales')
            ->where('psicologo_id', $psicologoId)
            ->update([
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'] ?? null,
                'secciones' => json_encode($data['secciones_estructura']),
                'status' => 1, // Al actualizarla, se marca como activa
                'updated_at' => now(),
            ]);
    }

    /**
     * Aplica la plantilla global a todos los pacientes del psicólogo.
     *
     * Para cada paciente:
     * 1. Inicia la historia clínica si no existe (reusando HistoriaClinica::iniciarHistoria no es viable
     *    porque ya inserta secciones default; usamos lógica propia aquí)
     * 2. Inserta las secciones y segmentos de la plantilla
     *
     * Retorna la cantidad de pacientes afectados.
     */
    public static function aplicarATodos($psicologoId)
    {
        $plantilla = self::obtenerPorPsicologo($psicologoId);
        if (!$plantilla || $plantilla->status != 1) {
            return ['success' => false, 'message' => 'Plantilla no encontrada o no está activa.'];
        }

        $secciones = $plantilla->secciones_decoded;
        if (empty($secciones)) {
            return ['success' => false, 'message' => 'La plantilla no tiene secciones definidas.'];
        }

        // Obtener pacientes únicos del psicólogo (vía citas realizadas o confirmadas)
        $pacientesIds = DB::table('citas')
            ->where('psicologo_id', $psicologoId)
            ->whereIn('estado', ['realizada', 'confirmada'])
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($pacientesIds->isEmpty()) {
            return ['success' => false, 'message' => 'No tienes pacientes registrados.'];
        }

        try {
            DB::beginTransaction();

            $pacientesAfectados = 0;

            foreach ($pacientesIds as $pacienteId) {
                // Asegurar que existe la historia clínica base
                $historia = DB::table('historia_clinicas')
                    ->where('user_id', $pacienteId)
                    ->first();

                if (!$historia) {
                    $historiaId = DB::table('historia_clinicas')->insertGetId([
                        'user_id' => $pacienteId,
                        'psicologo_id' => $psicologoId,
                        'created_at' => now(),
                        'updated_at' => null,
                    ]);
                } else {
                    $historiaId = $historia->id;
                }

                // Sincronización inteligente de secciones
                $seccionesPaciente = DB::table('historia_secciones_personalizadas')
                    ->where('historia_clinica_id', $historiaId)
                    ->get();
                
                $titulosPlantilla = collect($secciones)->pluck('titulo')->toArray();
                $titulosPaciente = $seccionesPaciente->pluck('titulo')->toArray();

                // 1. Eliminar secciones obsoletas (que ya no están en la plantilla) SI ESTÁN VACÍAS
                foreach ($seccionesPaciente as $seccionPac) {
                    if (!in_array($seccionPac->titulo, $titulosPlantilla)) {
                        // Verificar si tiene segmentos con contenido
                        $segmentosLlenos = DB::table('historia_segmentos_personalizados')
                            ->where('seccion_id', $seccionPac->id)
                            ->whereNotNull('contenido')
                            ->where('contenido', '!=', '')
                            ->exists();
                        
                        if (!$segmentosLlenos) {
                            // Está vacía, la eliminamos de forma segura
                            DB::table('historia_segmentos_personalizados')->where('seccion_id', $seccionPac->id)->delete();
                            DB::table('historia_secciones_personalizadas')->where('id', $seccionPac->id)->delete();
                        }
                    }
                }

                // 2. Insertar NUEVAS secciones y actualizar el orden de las existentes
                $ordenActual = 1;

                foreach ($secciones as $seccionData) {
                    if (!in_array($seccionData['titulo'], $titulosPaciente)) {
                        $seccionId = DB::table('historia_secciones_personalizadas')->insertGetId([
                            'historia_clinica_id' => $historiaId,
                            'titulo' => $seccionData['titulo'],
                            'descripcion_general' => $seccionData['descripcion_general'] ?? null,
                            'orden' => $ordenActual,
                            'status' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Insertar segmentos de la sección
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
                    } else {
                        // Si ya existe, actualizamos su orden para reflejar el nuevo orden de la plantilla
                        DB::table('historia_secciones_personalizadas')
                            ->where('historia_clinica_id', $historiaId)
                            ->where('titulo', $seccionData['titulo'])
                            ->update(['orden' => $ordenActual]);
                    }
                    $ordenActual++;
                }

                // 3. Ajustar el orden de las secciones obsoletas que no se eliminaron (porque tenían contenido)
                foreach ($seccionesPaciente as $seccionPac) {
                    if (!in_array($seccionPac->titulo, $titulosPlantilla)) {
                        $existe = DB::table('historia_secciones_personalizadas')->where('id', $seccionPac->id)->exists();
                        if ($existe) {
                            DB::table('historia_secciones_personalizadas')
                                ->where('id', $seccionPac->id)
                                ->update(['orden' => $ordenActual]);
                            $ordenActual++;
                        }
                    }
                }

                $pacientesAfectados++;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Plantilla aplicada exitosamente a {$pacientesAfectados} paciente(s).",
                'pacientes_afectados' => $pacientesAfectados,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error al aplicar la plantilla: ' . $e->getMessage()];
        }
    }

    /**
     * Verifica si el psicólogo tiene registrada al menos una plantilla global.
     */
    public static function tienePlantillaGlobal($psicologoId)
    {
        return DB::table('historia_plantillas_globales')
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->exists();
    }
}
