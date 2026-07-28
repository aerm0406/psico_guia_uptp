<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class Cita
{

    /**
     * Filtros de citas por estado y cantidad
     */

    /**
     * Obtiene todas las citas del sistema con información del paciente y psicólogo.
     * Soporta filtrado por estado y paginación.
     */
    public static function obtenerCitasGlobales($estado = null, $cantidad = 10)
    {
        $paginator = \Illuminate\Support\Facades\DB::table('citas')
            ->join('users as pacientes', 'citas.user_id', '=', 'pacientes.id')
            ->join('users as psicologos', 'citas.psicologo_id', '=', 'psicologos.id')
            ->select('citas.*')
            ->selectRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellidos) as paciente_nombre")
            ->selectRaw("CONCAT(psicologos.nombres, ' ', psicologos.apellidos) as psicologo_nombre")
            ->when($estado, function($q) use ($estado) {
                return $q->where('citas.estado', $estado);
            })
            ->orderBy('citas.created_at', 'desc')
            ->paginate($cantidad);

        $paginator->getCollection()->transform(function($item) {
            $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
            $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
            return self::desencriptarItem($item);
        });

        return $paginator;
    }

    /**
     * Instancia un modelo Cita desde Query Builder para envío de notificaciones.
     */
    public static function obtenerEstadisticas($psicologoId, $fechaInicio, $fechaFin, $estado = null, $avanceId = null, $estadoAnimoId = null, $prioridad = null, $perfilAcademico = null, $pnf = null)
    {
        $query = DB::table('citas')
            ->select('citas.*', 'citas.id as cita_id', 'users.nombres', 'users.apellidos', 'users.genero', 'users.fecha_nacimiento', 'users.perfil_academico', 'users.pnf')
            ->leftJoin('users', 'citas.user_id', '=', 'users.id')
            ->where('citas.psicologo_id', $psicologoId);

        if ($fechaInicio && $fechaFin) {
            $query->where(function($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('citas.fecha', [$fechaInicio, $fechaFin])
                  ->orWhereBetween('citas.created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            });
        }

        if ($estado) {
            $query->where('citas.estado', $estado);
        }

        if ($estadoAnimoId) {
            $query->where('citas.estado_animo_id', $estadoAnimoId);
        }

        if ($prioridad) {
            $query->where('citas.prioridad', $prioridad);
        }

        if ($perfilAcademico) {
            $query->where('users.perfil_academico', $perfilAcademico);
        }

        if ($pnf) {
            $query->where('users.pnf', $pnf);
        }

        $citas = $query->orderBy('citas.created_at', 'desc')->get();

        $citas->transform(function($item) {
            return self::desencriptarItem($item);
        });

        if ($avanceId) {
            $citas = $citas->filter(function($cita) use ($avanceId) {
                if (!$cita->notas) return false;
                $notas = json_decode($cita->notas, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($notas['avance_estado'])) {
                    return $notas['avance_estado'] == $avanceId;
                }
                return false;
            })->values();
        }

        return $citas->map(function($cita) {
            try { if (strlen($cita->nombres) > 40) $cita->nombres = \Illuminate\Support\Facades\Crypt::decryptString($cita->nombres); } catch (\Exception $e) {}
            try { if (strlen($cita->apellidos) > 40) $cita->apellidos = \Illuminate\Support\Facades\Crypt::decryptString($cita->apellidos); } catch (\Exception $e) {}
            try { if (strlen($cita->genero) > 40) $cita->genero = \Illuminate\Support\Facades\Crypt::decryptString($cita->genero); } catch (\Exception $e) {}

            $cita->paciente_nombre = trim(($cita->nombres ?? '') . ' ' . ($cita->apellidos ?? ''));
            $cita->id = $cita->cita_id ?? $cita->id;
            $cita->fecha_carbon = $cita->fecha ? \Carbon\Carbon::parse($cita->fecha) : null;
            $cita->created_at_carbon = $cita->created_at ? \Carbon\Carbon::parse($cita->created_at) : null;
            return $cita;
        });
    }

    public static function obtenerResumenEstadistico($citas, $fechaInicio = null, $fechaFin = null, $psicologoId = null)
    {
        $resumen = [
            'total_citas' => $citas->count(),
            'total_pacientes' => 0,
            'genero' => [
                'masculino' => 0,
                'femenino' => 0,
                'otro' => 0,
            ],
            'edades' => [
                'rangos' => [
                    '0-17' => 0,
                    '18-25' => 0,
                    '26-35' => 0,
                    '36-50' => 0,
                    '51+' => 0,
                ],
                'promedio' => 0,
            ],
            'perfil_academico' => [
                'Estudiante' => 0,
                'Profesor' => 0,
                'Obrero' => 0,
                'Administrativo' => 0,
                'Pre-escolar' => 0,
                'Otros' => 0,
                'No especificado' => 0,
            ],
            'pnf' => [
                'Administracion' => 0,
                'Mecanica' => 0,
                'Mantenimiento' => 0,
                'Electricidad' => 0,
                'Veterinaria' => 0,
                'Informatica' => 0,
                'PDA' => 0,
                'Distribucion_Logistica' => 0,
                'Agroalimentacion' => 0,
                'Seguridad_Alimentaria_Nutricional' => 0,
                'No especificado' => 0,
                'No aplica' => 0,
            ],
            'avances' => [],
            'avances_pacientes' => [],
            'prioridades' => [],
            'prioridades_pacientes' => [],
            'estados_animo' => [],
            'estados_animo_pacientes' => []
        ];

        // Obtener nombres de avances
        $avancesDb = DB::table('avances_sesion')->pluck('nombre', 'id')->toArray();
        foreach ($avancesDb as $nombre) {
            $resumen['avances'][$nombre] = 0;
            $resumen['avances_pacientes'][$nombre] = [];
        }
        $resumen['avances']['No especificado'] = 0;
        $resumen['avances_pacientes']['No especificado'] = [];

        // Obtener nombres de prioridades
        $prioridadesDb = DB::table('prioridades')->pluck('nombre')->toArray();
        foreach ($prioridadesDb as $nombre) {
            $nombreFormat = ucfirst($nombre);
            $resumen['prioridades'][$nombreFormat] = 0;
            $resumen['prioridades_pacientes'][$nombreFormat] = [];
        }
        $resumen['prioridades']['No especificado'] = 0;
        $resumen['prioridades_pacientes']['No especificado'] = [];

        // Obtener nombres de estados de animo
        $estadosAnimoDb = DB::table('estado_animos')->pluck('nombre', 'id')->toArray();
        foreach ($estadosAnimoDb as $nombre) {
            $resumen['estados_animo'][$nombre] = 0;
            $resumen['estados_animo_pacientes'][$nombre] = [];
        }
        $resumen['estados_animo']['No especificado'] = 0;
        $resumen['estados_animo_pacientes']['No especificado'] = [];

        $pacientes = [];
        $edadesList = [];
        $horasBloques = [];
        $totalRealizadas = 0;
        $totalEspera = 0;
        $citasConEspera = 0;
        $citasSemanales = [];

        // Iterar de la más reciente a la más antigua para quedar con el último avance del paciente
        $citasOrdenadas = $citas->sortByDesc('fecha_carbon');

        foreach ($citasOrdenadas as $cita) {
            // Tasa de Asistencia
            if ($cita->estado === 'realizada') {
                $totalRealizadas++;
            }

            // Flujo Semanal (Citas programadas)
            if ($cita->fecha_carbon && !in_array($cita->estado, ['cancelada', 'rechazada'])) {
                $semanaKey = $cita->fecha_carbon->format('W-Y');
                if (!isset($citasSemanales[$semanaKey])) $citasSemanales[$semanaKey] = 0;
                $citasSemanales[$semanaKey]++;
            }

            // Moda de Horas
            if ($cita->hora) {
                $horaCarbon = Carbon::parse($cita->hora);
                // Bloque: "08:00 AM - 09:00 AM"
                $bloque = $horaCarbon->format('h:00 A') . ' - ' . $horaCarbon->copy()->addHour()->format('h:00 A');
                if (!isset($horasBloques[$bloque])) $horasBloques[$bloque] = 0;
                $horasBloques[$bloque]++;
            }

            // Tiempo de espera
            if ($cita->created_at_carbon && $cita->fecha_carbon) {
                $diffDays = $cita->created_at_carbon->startOfDay()->diffInDays($cita->fecha_carbon->copy()->startOfDay());
                // Evitamos negativos si se programa para el mismo día (espera = 0)
                $totalEspera += max(0, $diffDays);
                $citasConEspera++;
            }

            if (!isset($pacientes[$cita->user_id])) {
                $pacientes[$cita->user_id] = true;
                $resumen['total_pacientes']++;

                // Género
                $genero = strtolower(trim($cita->genero ?? ''));
                if (in_array($genero, ['masculino', 'hombre', 'm'])) {
                    $resumen['genero']['masculino']++;
                } elseif (in_array($genero, ['femenino', 'mujer', 'f'])) {
                    $resumen['genero']['femenino']++;
                } else {
                    $resumen['genero']['otro']++;
                }

                // Edad
                if ($cita->fecha_nacimiento) {
                    $edad = Carbon::parse($cita->fecha_nacimiento)->age;
                    $edadesList[] = $edad;

                    if ($edad <= 17) $resumen['edades']['rangos']['0-17']++;
                    elseif ($edad <= 25) $resumen['edades']['rangos']['18-25']++;
                    elseif ($edad <= 35) $resumen['edades']['rangos']['26-35']++;
                    elseif ($edad <= 50) $resumen['edades']['rangos']['36-50']++;
                    else $resumen['edades']['rangos']['51+']++;
                }

                // Perfil Académico
                $perfil = $cita->perfil_academico ?? 'No especificado';
                if (!in_array($perfil, ['Estudiante', 'Profesor', 'Obrero', 'Administrativo', 'Pre-escolar', 'Otros'])) {
                    $perfil = 'No especificado';
                }
                $resumen['perfil_academico'][$perfil]++;

                // PNF (Carreras)
                $pnfVal = $cita->pnf ?? 'No especificado';
                if ($pnfVal === 'Agroalimentaria') $pnfVal = 'Agroalimentacion';
                if ($pnfVal === 'Electrica') $pnfVal = 'Electricidad';
                
                if (!in_array($pnfVal, [
                    'Administracion', 'Mecanica', 'Mantenimiento', 'Electricidad', 
                    'Veterinaria', 'Informatica', 'PDA', 'Distribucion_Logistica', 
                    'Agroalimentacion', 'Seguridad_Alimentaria_Nutricional'
                ])) {
                    $pnfVal = ($perfil === 'Estudiante') ? 'No especificado' : 'No aplica';
                }
                if (!isset($resumen['pnf'][$pnfVal])) {
                    $resumen['pnf'][$pnfVal] = 0;
                }
                $resumen['pnf'][$pnfVal]++;

                // Avance
                $avanceId = null;
                if ($cita->notas) {
                    $notas = is_string($cita->notas) ? json_decode($cita->notas, true) : $cita->notas;
                    if (json_last_error() === JSON_ERROR_NONE && is_array($notas) && isset($notas['avance_estado'])) {
                        $avanceId = $notas['avance_estado'];
                    }
                }
                
                if ($avanceId && isset($avancesDb[$avanceId])) {
                    $resumen['avances'][$avancesDb[$avanceId]]++;
                    if (!in_array($cita->paciente_nombre, $resumen['avances_pacientes'][$avancesDb[$avanceId]])) {
                        $resumen['avances_pacientes'][$avancesDb[$avanceId]][] = $cita->paciente_nombre;
                    }
                } else {
                    $resumen['avances']['No especificado']++;
                    if (!in_array($cita->paciente_nombre, $resumen['avances_pacientes']['No especificado'])) {
                        $resumen['avances_pacientes']['No especificado'][] = $cita->paciente_nombre;
                    }
                }

                // Prioridad
                $prioridad = ucfirst($cita->prioridad ?? 'No especificado');
                if (!isset($resumen['prioridades'][$prioridad])) {
                    $prioridad = 'No especificado';
                }
                $resumen['prioridades'][$prioridad]++;
                if (!in_array($cita->paciente_nombre, $resumen['prioridades_pacientes'][$prioridad])) {
                    $resumen['prioridades_pacientes'][$prioridad][] = $cita->paciente_nombre;
                }

                // Estado de Animo
                $estadoAnimoId = $cita->estado_animo_id;
                if ($estadoAnimoId && isset($estadosAnimoDb[$estadoAnimoId])) {
                    $estadoAnimoNombre = $estadosAnimoDb[$estadoAnimoId];
                    $resumen['estados_animo'][$estadoAnimoNombre]++;
                    if (!in_array($cita->paciente_nombre, $resumen['estados_animo_pacientes'][$estadoAnimoNombre])) {
                        $resumen['estados_animo_pacientes'][$estadoAnimoNombre][] = $cita->paciente_nombre;
                    }
                } else {
                    $resumen['estados_animo']['No especificado']++;
                    if (!in_array($cita->paciente_nombre, $resumen['estados_animo_pacientes']['No especificado'])) {
                        $resumen['estados_animo_pacientes']['No especificado'][] = $cita->paciente_nombre;
                    }
                }
            }
        }

        if (count($edadesList) > 0) {
            $resumen['edades']['promedio'] = round(array_sum($edadesList) / count($edadesList), 1);
            
            $sortedEdades = $edadesList;
            sort($sortedEdades);
            $count = count($sortedEdades);
            $middle = floor(($count - 1) / 2);
            if ($count % 2 == 0) {
                $resumen['edades']['mediana'] = ($sortedEdades[$middle] + $sortedEdades[$middle + 1]) / 2;
            } else {
                $resumen['edades']['mediana'] = $sortedEdades[$middle];
            }

            $counts = array_count_values($edadesList);
            arsort($counts);
            $resumen['edades']['moda'] = array_key_first($counts);
        } else {
            $resumen['edades']['mediana'] = 0;
            $resumen['edades']['moda'] = 0;
        }

        // Calcula Moda de horas
        arsort($horasBloques);
        $resumen['hora_pico'] = !empty($horasBloques) ? array_key_first($horasBloques) : 'N/A';
        $resumen['distribucion_horas'] = $horasBloques; // for chart

        // Volumen de atención (Promedio semanal)
        $semanasTotal = 1;
        if ($fechaInicio && $fechaFin) {
            $inicio = \Carbon\Carbon::parse($fechaInicio);
            $fin = \Carbon\Carbon::parse($fechaFin);
            $diasPeriodo = max(1, $inicio->diffInDays($fin));
            $semanasTotal = $diasPeriodo / 7;
        }
        $resumen['promedio_semanal'] = round($resumen['total_citas'] / max(0.1, $semanasTotal), 1);
        
        // Sort flujo semanal
        ksort($citasSemanales);
        $resumen['flujo_semanal'] = $citasSemanales;

        // Tasa de asistencia
        $resumen['tasa_asistencia'] = $resumen['total_citas'] > 0 ? round(($totalRealizadas / $resumen['total_citas']) * 100, 1) : 0;

        // Tiempo de espera promedio
        $resumen['tiempo_espera_promedio'] = $citasConEspera > 0 ? round($totalEspera / $citasConEspera, 1) : 0;

        // Comparativa temporal
        $resumen['comparativa_pacientes'] = 0;
        if ($fechaInicio && $fechaFin && $psicologoId) {
            $inicio = \Carbon\Carbon::parse($fechaInicio);
            $fin = \Carbon\Carbon::parse($fechaFin);
            $dias = $inicio->diffInDays($fin);
            
            $prevFin = $inicio->copy()->subDay();
            $prevInicio = $prevFin->copy()->subDays($dias);

            $prevCitas = DB::table('citas')
                ->where('psicologo_id', $psicologoId)
                ->where(function ($q) use ($prevInicio, $prevFin) {
                    $q->whereBetween('fecha', [$prevInicio->toDateString(), $prevFin->toDateString()])
                      ->orWhereBetween('created_at', [$prevInicio->toDateString() . ' 00:00:00', $prevFin->toDateString() . ' 23:59:59']);
                })
                ->count();
                
            $currentCitas = $resumen['total_citas'];

            if ($prevCitas > 0) {
                $resumen['comparativa_pacientes'] = round((($currentCitas - $prevCitas) / $prevCitas) * 100, 1);
            } else if ($currentCitas > 0) {
                $resumen['comparativa_pacientes'] = 100; // si antes había 0 y ahora más de 0
            }
        }

        return $resumen;
    }
    public static function instanciarParaNotificacion($id)
    {
        $data = DB::table('citas')->where('id', $id)->first();
        if (!$data) return null;

        $cita = new self();
        foreach ((array)$data as $key => $value) {
            $cita->$key = $value;
        }
        return self::desencriptarItem($cita);
    }

    public function __get($key)
    {
        if ($key === 'paciente') {
            return User::obtenerUsuarioPorId($this->user_id);
        }
        if ($key === 'psicologo') {
            return User::obtenerUsuarioPorId($this->psicologo_id);
        }
        return null;
    }

    public static function notificarUsuario($userId, $notification)
    {
        $data = DB::table('users')->where('id', $userId)->first();
        if (!$data) return;

        $notifiable = new \App\Models\NotifiableUser();

        foreach ((array) $data as $key => $value) {
            $notifiable->{$key} = $value;
        }
        $notifiable->name = trim($data->nombres . ' ' . $data->apellidos);
        $notifiable->notify($notification);
    }

    /**
     * Obtiene las citas asignadas a un psicólogo específico.
     */
    public static function obtenerCitasPorPsicologo($psicologoId, $estado = null, $cantidad = 10)
    {
        $paginator = \Illuminate\Support\Facades\DB::table('citas')
            ->join('users as pacientes', 'citas.user_id', '=', 'pacientes.id')
            ->select('citas.*')
            ->selectRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellidos) as paciente_nombre")
            ->where('citas.psicologo_id', $psicologoId)
            ->when($estado, function($q) use ($estado) {
                return $q->where('citas.estado', $estado);
            })
            ->orderBy('citas.created_at', 'desc')
            ->paginate($cantidad);

        $coleccionFiltrada = $paginator->getCollection()->transform(function($item) {
            $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
            $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
            return self::desencriptarItem($item);
        })->filter(function($item) {
            return trim($item->motivo) !== 'Nota de Evolución (Manual)';
        })->values();

        $paginator->setCollection($coleccionFiltrada);

        return $paginator;
    }

    public static function obtenerPaciente($userId)
    {
        return DB::table('users')
            ->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->where('id', $userId)
            ->first();
    }

    public static function obtenerPsicologo($psicologoId)
    {
        return DB::table('users')
            ->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->where('id', $psicologoId)
            ->first();
    }

    /**
     * Devuelve una versión legible de las notas, manejando tanto el formato JSON
     * estructurado como el formato antiguo de texto plano.
     */
    public static function obtenerNotasLimpias($raw)
    {
        if (!$raw) return '';

        // Si parece JSON, intentamos decodificarlo
        if (str_starts_with($raw, '{')) {
            try {
                $data = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    // Prioridad: Observaciones -> Motivo Consulta -> Intervenciones
                    return $data['observaciones'] ?? ($data['motivo_consulta'] ?? ($data['intervenciones'] ?? 'Sesión con datos estructurados.'));
                }
            } catch (\Exception $e) {}
        }

        return $raw;
    }

    /**
     * Helper para desencriptar manualmente campos de un objeto stdClass (Query Builder).
     */
    public static function obtenerDetalle($id)
    {
        $cita = DB::table('citas')
            ->join('users as pacientes', 'citas.user_id', '=', 'pacientes.id')
            ->leftJoin('users as psicologos', 'citas.psicologo_id', '=', 'psicologos.id')
            ->select(
                'citas.*',
                'pacientes.nombres as paciente_nombres',
                'pacientes.apellidos as paciente_apellidos',
                'psicologos.nombres as psicologo_nombres',
                'psicologos.apellidos as psicologo_apellidos'
            )
            ->where('citas.id', $id)
            ->first();

        if (!$cita) return null;

        // Desencriptar campos sensibles
        $cita = self::desencriptarItem($cita);

        // Convertir campos de fecha de cadenas de texto a instancias de Carbon para soporte de formateo en las vistas
        if (isset($cita->fecha) && $cita->fecha) {
            $cita->fecha = \Carbon\Carbon::parse($cita->fecha);
        }
        if (isset($cita->created_at) && $cita->created_at) {
            $cita->created_at = \Carbon\Carbon::parse($cita->created_at);
        }
        if (isset($cita->updated_at) && $cita->updated_at) {
            $cita->updated_at = \Carbon\Carbon::parse($cita->updated_at);
        }
        if (isset($cita->confirmado_en) && $cita->confirmado_en) {
            $cita->confirmado_en = \Carbon\Carbon::parse($cita->confirmado_en);
        }

        // Vincular los modelos completos de paciente y psicólogo para evitar errores de referencia nula en las vistas
        $cita->paciente = User::obtenerUsuarioPorId($cita->user_id);
        $cita->psicologo = User::obtenerUsuarioPorId($cita->psicologo_id);

        // Formatear nombres cortos (Primer nombre y Primer apellido)
        $pNombre = explode(' ', trim($cita->paciente_nombres ?? ''))[0];
        $pApellido = explode(' ', trim($cita->paciente_apellidos ?? ''))[0];
        $cita->paciente_short_name = trim($pNombre . ' ' . $pApellido) ?: 'Paciente';

        $psNombre = explode(' ', trim($cita->psicologo_nombres ?? ''))[0];
        $psApellido = explode(' ', trim($cita->psicologo_apellidos ?? ''))[0];
        $cita->psicologo_short_name = trim($psNombre . ' ' . $psApellido) ?: 'Psicólogo';

        return $cita;
    }

    private static function desencriptarItem($item)
    {
        if (!$item) return $item;

        // Asegurar que las propiedades de relación existen para evitar errores en vistas que usan optional() con stdClass
        if (!$item instanceof self) {
            if (!isset($item->paciente)) $item->paciente = null;
            if (!isset($item->psicologo)) $item->psicologo = null;
        }

        $campos = ['motivo', 'notas', 'bloques_sugeridos', 'bloques_propuestos', 'bloque_propuesto', 'propuesta_bloque_seleccionado'];
        foreach ($campos as $campo) {
            if (isset($item->$campo) && !empty($item->$campo) && is_string($item->$campo)) {
                // Si la cadena parece un token encriptado (larga y sin espacios)
                if (strlen($item->$campo) > 40 && !str_contains($item->$campo, ' ')) {
                    try {
                        // Intentamos con decrypt() que maneja serialización (formato estándar de Eloquent)
                        $decrypted = decrypt($item->$campo);

                        // Si el resultado es una estructura de datos, lo normalizamos a JSON para compatibilidad
                        if (is_array($decrypted) || is_object($decrypted)) {
                            $item->$campo = json_encode($decrypted);
                        } else {
                            $item->$campo = (string) $decrypted;
                        }
                    } catch (\Exception $e) {
                        // Segundo intento con decryptString (por si se usó sin serialización)
                        try {
                            $item->$campo = Crypt::decryptString($item->$campo);
                        } catch (\Exception $e2) {
                            // Si ambos fallan, es probable que ya sea texto plano
                        }
                    }
                }
            }
        }
        return $item;
    }

    // Los datos ahora se encriptan y castean manualmente al guardarse o recuperarse.
    /**
     * Normaliza el formato de un bloque horario para permitir comparaciones precisas.
     */
    public static function normalizarBloque($bloque)
    {
        $value = trim($bloque ?? '');

        // Limpiar espacios invisibles (non-breaking spaces, etc)
        $value = preg_replace('/[\x{00a0}\x{200b}]+/u', ' ', $value);

        // Remove Spanish day names
        $dias = ['lunes', 'martes', 'miércoles', 'miercoles', 'jueves', 'viernes', 'sábado', 'sabado', 'domingo'];
        $value = str_ireplace($dias, '', $value);

        $value = preg_replace_callback('/(\d{1,2}):(\d{2})\s*(am|pm)\b/i', function($matches) {
            $hours = (int)$matches[1];
            $ampm = strtolower($matches[3]);
            if ($ampm === 'pm' && $hours < 12) $hours += 12;
            if ($ampm === 'am' && $hours === 12) $hours = 0;
            return sprintf('%02d:%s', $hours, $matches[2]);
        }, $value);

        $value = preg_replace([
            '/\s*[-–—]\s*/u',
            '/(\d{1,2}:\d{2}):\d{2}/',
            '/\s+/'
        ], [
            '-',
            '$1',
            ' '
        ], $value);

        // Fijar ceros iniciales tomando en cuenta el delimitador pipe (|) también
        $value = preg_replace('/(^|\s|-|\|)(\d):/', '${1}0$2:', $value);

        return mb_strtolower(str_replace(' ', '', $value), 'UTF-8');
    }

    /**
     * Evalúa la prioridad base de un paciente basándose en su historial de cancelaciones.
     */
    public static function evaluarPrioridadBasePaciente($userId)
    {
        $paciente = DB::table('users')->where('id', $userId)->first();
        $resetAt = $paciente ? $paciente->infracciones_reset_at : null;

        $queryCancelaciones = DB::table('citas')
            ->where('user_id', $userId)
            ->where('estado', 'cancelada')
            ->where('cancelado_por', 'paciente');

        if ($resetAt) {
            $queryCancelaciones->where('updated_at', '>', $resetAt);
        }
        $cancelacionesPaciente = $queryCancelaciones->count();

        $queryNoAsistencias = DB::table('citas')
            ->where('user_id', $userId)
            ->where('estado', 'no_asistio');

        if ($resetAt) {
            $queryNoAsistencias->where('updated_at', '>', $resetAt);
        }
        $noAsistencias = $queryNoAsistencias->count();

        if (($cancelacionesPaciente + $noAsistencias) >= 3) {
            return 'baja';
        }

        return 'media';
    }

    /**
     * Verifica si un paciente ha alcanzado el límite de inasistencias y notifica si es necesario.
     */
    public static function verificarUmbralInfraccionesPaciente($userId, $psicologoId = null)
    {
        $resetAt = DB::table('users')->where('id', $userId)->value('infracciones_reset_at');

        $queryCancelaciones = DB::table('citas')
            ->where('user_id', $userId)
            ->where('estado', 'cancelada')
            ->where('cancelado_por', 'paciente');

        if ($resetAt) {
            $queryCancelaciones->where('updated_at', '>', $resetAt);
        }
        $cancelacionesPaciente = $queryCancelaciones->count();

        $queryNoAsistencias = DB::table('citas')
            ->where('user_id', $userId)
            ->where('estado', 'no_asistio');

        if ($resetAt) {
            $queryNoAsistencias->where('updated_at', '>', $resetAt);
        }
        $noAsistencias = $queryNoAsistencias->count();

        if (($cancelacionesPaciente + $noAsistencias) == 3) {
            // Notificar al paciente
            self::notificarUsuario($userId, new \App\Notifications\PenalizacionPacienteNotification());

            // Notificar al psicólogo
            if ($psicologoId) {
                self::notificarUsuario($psicologoId, new \App\Notifications\PenalizacionPsicologoNotification((object)['id' => $userId]));
            } else {
                $ultimaCita = DB::table('citas')->where('user_id', $userId)->orderBy('created_at', 'desc')->first();
                if ($ultimaCita) {
                    self::notificarUsuario($ultimaCita->psicologo_id, new \App\Notifications\PenalizacionPsicologoNotification((object)['id' => $userId]));
                }
            }
        }
    }

    /**
     * Recalcula la prioridad de las solicitudes pendientes de un paciente.
     */
    public static function aplicarRecalculoPrioridad($userId, $psicologoId = null)
    {
        try {
            DB::beginTransaction();

            $prioridad = self::evaluarPrioridadBasePaciente($userId);
            DB::table('citas')->where('user_id', $userId)
                ->where('estado', 'pendiente')
                ->limit(10)
                ->update(['prioridad' => $prioridad, 'updated_at' => now()]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Notifica al psicólogo si un paciente ha sido rechazado o cancelado repetidamente.
     */
    public static function evaluarAvisoAtencionPsicologo($userId, $psicologoId)
    {
        $pacienteExists = DB::table('users')->where('id', $userId)->exists();
        $psicologoExists = DB::table('users')->where('id', $psicologoId)->exists();

        if (!$pacienteExists || !$psicologoExists) {
            return;
        }

        // Determinar las veces que este psicologo no lo ha atendido (Rechazadas + Canceladas por él)
        $rechazos = DB::table('citas')->where('user_id', $userId)
            ->where('psicologo_id', $psicologoId)
            ->where('estado', 'rechazada')
            ->count();

        $cancelaciones = DB::table('citas')->where('user_id', $userId)
            ->where('psicologo_id', $psicologoId)
            ->where('estado', 'cancelada')
            ->where('cancelado_por', 'psicologo')
            ->count();

        if (($rechazos + $cancelaciones) == 3) {
            // El psicólogo alcanzó 3 faltas de atención con este paciente.
            // La última cita sirve para anclar un botón de ayuda de cambio.
            $ultimaCita = DB::table('citas')->where('user_id', $userId)
                ->where('psicologo_id', $psicologoId)
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($ultimaCita) {
                $pacienteMock = (object)['id' => $userId];
                $citaModel = self::instanciarParaNotificacion($ultimaCita->id);
                if ($citaModel) {
                    try {
                        self::notificarUsuario($psicologoId, new \App\Notifications\AvisoAtencionPsicologoNotification($pacienteMock, $citaModel));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
        }
    }

    /**
     * Crea una nueva solicitud de cita validando que no existan duplicados activos.
     */
    public static function crear($user, $validated)
    {
        try {
            DB::beginTransaction();

            $tieneCitaPendiente = DB::table('citas')->where('user_id', $user->id)
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->lockForUpdate()
                ->exists();

            if ($tieneCitaPendiente) {
                DB::rollBack();
                return [false, 'Tienes una cita pendiente o confirmada. Espera a que se marque como realizada, no asistió o cancelada antes de solicitar otra.', null];
            }

            if (empty($validated['bloques_sugeridos'])) {
                DB::rollBack();
                return [false, 'Debes seleccionar un bloque de horario.', null];
            }

            $prioridadHeredada = $user->prioridad_siguiente_cita;

            if ($prioridadHeredada) {
                $prioridad = $prioridadHeredada;
                DB::table('users')->where('id', $user->id)->update(['prioridad_siguiente_cita' => null]);
            } else {
                if (! empty($validated['prioridad'])) {
                    $prioridad = $validated['prioridad'];
                } else {
                    $prioridad = self::evaluarPrioridadBasePaciente($user->id);
                }
            }

            $psicologo = DB::table('users')
                ->where('id', $validated['psicologo_id'])
                ->where('role', 'psicologo')
                ->first();

            if (! $psicologo) {
                DB::rollBack();
                return [false, 'Selecciona un psicólogo válido.', null];
            }

            $citaId = DB::table('citas')->insertGetId([
                'user_id' => $user->id,
                'psicologo_id' => $psicologo->id,
                'fecha' => $validated['fecha_solicitada'],
                'hora' => null,
                'estado' => 'pendiente',
                'prioridad' => $prioridad,
                'motivo' => Crypt::encryptString($validated['motivo']),
                'bloques_sugeridos' => !empty($validated['bloques_sugeridos']) ? Crypt::encryptString($validated['bloques_sugeridos']) : null,
                'created_at' => now(),
                'updated_at' => null,
            ]);

            $cita = DB::table('citas')->where('id', $citaId)->first();

            // Notificar al psicólogo
            $citaModel = self::instanciarParaNotificacion($citaId);
            if ($citaModel) {
                self::notificarUsuario($psicologo->id, new \App\Notifications\CitaRequestedNotification($citaModel));
            }

            DB::commit();
            return [true, 'Solicitud de cita creada correctamente.', $cita];
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al crear la solicitud de cita: ' . $e->getMessage(), null];
        }
    }


    /**
     * Confirma una cita propuesta asignándole una fecha y hora definitiva.
     * Sigue el estándar de transacciones y comentarios detallados.
     */
    public static function confirmar($citaId, $psicologoId, $validated)
    {
        try {
            DB::beginTransaction();

            // 1. Buscamos la cita y validamos su estado inicial
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita || $cita->estado !== 'pendiente') {
                return [false, 'Error: Solo se pueden aceptar citas que estén en estado pendiente.'];
            }

            // 2. Validación de seguridad: No permitir agendar en el pasado (valida fecha y hora de inicio)
            $motivo = '';
            if (!empty($cita->motivo)) {
                try {
                    $motivo = \Illuminate\Support\Facades\Crypt::decryptString($cita->motivo);
                } catch (\Exception $e) {
                    $motivo = $cita->motivo; // Fallback in case it's not encrypted
                }
            }
            
            $isManual = in_array($motivo, ['Asignado manualmente por psicólogo', 'Gestionada por psicólogo']) 
                        || str_contains($motivo, 'anualmente') 
                        || str_contains($motivo, 'estionada');

            if (!$isManual) {
                $fechaHoraCita = \Carbon\Carbon::parse($validated['fecha'] . ' ' . $validated['hora']);
                if ($fechaHoraCita->isBefore(now())) {
                    return [false, 'Validación fallida: No se pueden agendar citas en fechas u horas que ya pasaron.'];
                }
            }

            // 3. Verificación de disponibilidad: Evitar colisiones en el mismo bloque
            $bloqueConfirmadoExistente = DB::table('citas')
                ->where('psicologo_id', $cita->psicologo_id)
                ->where('estado', 'confirmada')
                ->where('fecha', $validated['fecha'])
                ->where('id', '!=', $citaId)
                ->get()
                ->first(function ($otraCita) use ($validated) {
                    if (!$otraCita->bloque_propuesto) return false;
                    
                    try {
                        $bloqueDecrypted = decrypt($otraCita->bloque_propuesto);
                    } catch (\Exception $e) {
                        try {
                            $bloqueDecrypted = \Illuminate\Support\Facades\Crypt::decryptString($otraCita->bloque_propuesto);
                        } catch (\Exception $e2) {
                            $bloqueDecrypted = $otraCita->bloque_propuesto;
                        }
                    }
                    if (is_array($bloqueDecrypted) || is_object($bloqueDecrypted)) {
                        $bloqueDecrypted = json_encode($bloqueDecrypted);
                    }
                    
                    return $bloqueDecrypted && self::normalizarBloque($bloqueDecrypted) === self::normalizarBloque($validated['bloque']);
                });

            if ($bloqueConfirmadoExistente) {
                return [false, 'Conflicto: Este bloque horario ya tiene una cita confirmada para este psicólogo.'];
            }

            // 4. Actualización del registro principal
            DB::table('citas')->where('id', $citaId)->update([
                'estado' => 'confirmada',
                'fecha' => $validated['fecha'],
                'hora' => $validated['hora'],
                'bloque_propuesto' => Crypt::encryptString($validated['bloque']),
                'bloques_propuestos' => null,
                'confirmado_en' => now(),
                'updated_at' => now(),
            ]);

            // 5. Notificación al paciente (Correo y Sistema)
            $pacienteRow = DB::table('users')->where('id', $cita->user_id)->first();
            $citaActualizada = self::desencriptarItem(DB::table('citas')->where('id', $citaId)->first());

            if ($pacienteRow) {
                $citaModel = self::instanciarParaNotificacion($citaId);
                if ($citaModel) {
                    if (filter_var($pacienteRow->email, FILTER_VALIDATE_EMAIL)) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($pacienteRow->email)->send(new \App\Mail\CitaConfirmada($citaModel));
                        } catch (\Throwable $exception) {
                            report($exception); // Registramos el error de correo pero no detenemos el proceso
                        }
                    }
                    self::notificarUsuario($cita->user_id, new \App\Notifications\CitaConfirmedNotification($citaModel));
                }
            }

            DB::commit();
            return [true, 'Cita confirmada con éxito. El paciente ha sido notificado.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error interno al confirmar la cita: ' . $e->getMessage()];
        }
    }

    /**
     * Rechaza una solicitud de cita proporcionando un motivo explicativo.
     */
    public static function rechazar($citaId, $motivo)
    {
        try {
            DB::beginTransaction();

            // 1. Verificación de existencia y estado
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita || $cita->estado !== 'pendiente') {
                return [false, 'Error: Solo se pueden rechazar citas con estado pendiente.'];
            }

            // 2. Actualización del estado a 'rechazada'
            DB::table('citas')->where('id', $citaId)->update([
                'estado' => 'rechazada',
                'notas' => Crypt::encryptString($motivo ?: 'Lo siento, no puedo atenderte en los horarios solicitados'),
                'updated_at' => now(),
            ]);

            // 3. Evaluación de avisos de atención (lógica de 3 rechazos)
            self::evaluarAvisoAtencionPsicologo($cita->user_id, $cita->psicologo_id);

            // 4. Notificar al paciente
            $citaModel = self::instanciarParaNotificacion($citaId);
            if ($citaModel) {
                self::notificarUsuario($cita->user_id, new \App\Notifications\CitaRechazadaNotification($citaModel));
            }

            DB::commit();
            return [true, 'La solicitud de cita ha sido rechazada correctamente.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al procesar el rechazo: ' . $e->getMessage()];
        }
    }

    /**
     * Marca una cita como realizada exitosamente.
     */
    public static function marcarRealizada($citaId, $psicologoId)
    {
        try {
            DB::beginTransaction();

            // 1. Verificación de existencia y estado (Debe estar confirmada)
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita || $cita->estado !== 'confirmada') {
                return [false, 'Error: Solo se pueden marcar como realizada citas que ya han sido confirmadas.'];
            }



            // 2. Actualización del estado a 'realizada'
            DB::table('citas')->where('id', $citaId)->update([
                'estado' => 'realizada',
                'updated_at' => now(),
            ]);

            DB::commit();
            return [true, 'La cita ha sido marcada como realizada exitosamente.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error interno al marcar como realizada: ' . $e->getMessage()];
        }
    }

    /**
     * Marca una cita como no asistida, aplicando penalizaciones y recalculo de prioridades.
     */
    public static function marcarNoAsistio($citaId)
    {
        try {
            DB::beginTransaction();

            // 1. Verificación de existencia y estado
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita || $cita->estado !== 'confirmada') {
                return [false, 'Error: Solo se pueden marcar como "no asistió" citas que estaban confirmadas.'];
            }



            // 2. Actualización del estado a 'no_asistio'
            DB::table('citas')->where('id', $citaId)->update([
                'estado' => 'no_asistio',
                'updated_at' => now(),
            ]);

            // 3. Procesamiento de infracciones (Penalización automática)
            self::verificarUmbralInfraccionesPaciente($cita->user_id, $cita->psicologo_id);

            // 4. Recalcular prioridades de futuras solicitudes del paciente
            self::aplicarRecalculoPrioridad($cita->user_id, $cita->psicologo_id);

            DB::commit();
            return [true, 'Cita marcada como "no asistió". Se han procesado las penalizaciones correspondientes.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al procesar la inasistencia: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene la fecha de la primera cita registrada y confirmada/realizada del paciente.
     */
    public static function obtenerFechaPrimeraCita($userId)
    {
        return DB::table('citas')
            ->where('user_id', $userId)
            ->whereNotNull('fecha')
            ->orderBy('fecha', 'asc')
            ->value('fecha');
    }

    /**
     * Cancela una cita, gestionando los estados según quién realice la acción.
     * Sigue el estándar de transacciones y notificaciones detalladas.
     */
    public static function cancelar($citaId, $userId, $motivo = null)
    {
        try {
            DB::beginTransaction();

            // 1. Buscamos el registro y el usuario que cancela
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita) {
                DB::rollBack();
                return [false, 'Error: El registro de la cita no existe.'];
            }

            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) {
                DB::rollBack();
                return [false, 'Error: Usuario no identificado.'];
            }

            // 2. Determinamos el rol del actor que realiza la cancelación
            $actor = 'paciente';
            if ($user->role === 'admin') $actor = 'admin';
            if ($user->role === 'psicologo') $actor = 'psicologo';

            // 3. Lógica específica según el actor
            if ($actor === 'psicologo') {
                // El psicólogo solo puede cancelar citas que ya estaban confirmadas
                if ($cita->estado !== 'confirmada') {
                    return [false, 'Validación: Solo se pueden cancelar citas que ya estén confirmadas.'];
                }

                $notas = $motivo ?: 'Lo siento, no podré atenderte, surgió un inconveniente a última hora.';

                DB::table('citas')->where('id', $citaId)->update([
                    'estado' => 'cancelada',
                    'cancelado_por' => 'psicologo',
                    'notas' => Crypt::encryptString($notas),
                    'updated_at' => now(),
                ]);

                // Procesamos avisos y recalculo para compensar al paciente
                self::aplicarRecalculoPrioridad($cita->user_id, $cita->psicologo_id);
                self::evaluarAvisoAtencionPsicologo($cita->user_id, $cita->psicologo_id);

                // Notificamos al paciente
                $citaModel = self::instanciarParaNotificacion($citaId);
                if ($citaModel) {
                    self::notificarUsuario($cita->user_id, new \App\Notifications\CitaCancelledNotification($citaModel, 'psicologo'));
                }

            } else {
                // Caso: Paciente o Administrador
                if (!in_array($cita->estado, ['pendiente', 'confirmada'])) {
                    return [false, 'Validación: Sólo se pueden cancelar citas pendientes o confirmadas.'];
                }

                if ($actor === 'paciente' && $cita->estado === 'confirmada' && $cita->fecha && $cita->hora) {
                    $fechaSolo = \Carbon\Carbon::parse($cita->fecha)->format('Y-m-d');
                    $horaInicioStr = $cita->hora;
                    // Extraer solo la hora de inicio si es un rango
                    if (preg_match('/(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)/i', $cita->hora, $m)) {
                        $horaInicioStr = $m[1];
                    }
                    $fechaHoraCita = \Carbon\Carbon::parse($fechaSolo . ' ' . $horaInicioStr);
                    
                    if ($fechaHoraCita->isPast()) {
                        return [false, 'No puedes cancelar una cita que ya ha empezado. Queda a la espera del procesamiento del psicólogo.'];
                    }
                }

                DB::table('citas')->where('id', $citaId)->update([
                    'estado' => 'cancelada',
                    'cancelado_por' => $actor,
                    'updated_at' => now(),
                ]);

                // Si cancela el paciente, se evalúan penalizaciones
                if ($actor === 'paciente') {
                    self::verificarUmbralInfraccionesPaciente($cita->user_id, $cita->psicologo_id);
                    self::aplicarRecalculoPrioridad($cita->user_id, $cita->psicologo_id);

                    // Notificamos al psicólogo
                    $citaModel = self::instanciarParaNotificacion($citaId);
                    if ($citaModel) {
                        self::notificarUsuario($cita->psicologo_id, new \App\Notifications\CitaCancelledNotification($citaModel, $actor));
                    }
                }
            }

            DB::commit();
            return [true, 'La cita ha sido cancelada exitosamente.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error interno al cancelar la cita: ' . $e->getMessage()];
        }
    }

    /**
     * Posponer una cita confirmada, devolviéndola al estado pendiente
     * y descartando las propuestas o bloques asignados.
     */
    public static function posponer($citaId, $userId)
    {
        try {
            DB::beginTransaction();

            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita) {
                DB::rollBack();
                return [false, 'Error: El registro de la cita no existe.'];
            }

            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id) {
                DB::rollBack();
                return [false, 'Error: Usuario no autorizado para esta acción.'];
            }

            if ($cita->estado !== 'confirmada') {
                DB::rollBack();
                return [false, 'Validación: Solo se pueden posponer citas que ya estén confirmadas.'];
            }

            if ($cita->fecha && $cita->hora) {
                $fechaSolo = \Carbon\Carbon::parse($cita->fecha)->format('Y-m-d');
                $fechaHoraCita = \Carbon\Carbon::parse($fechaSolo . ' ' . $cita->hora);
                if ($fechaHoraCita->isPast()) {
                    DB::rollBack();
                    return [false, 'Validación: No es posible posponer una cita cuya hora programada ya ha comenzado o pasado. En su lugar, registre si el paciente asistió o no.'];
                }
            }

            // Solo limpiamos bloque_propuesto (el bloque confirmado) y bloques_propuestos
            // (las contrapropuestas del psicólogo). Conservamos bloques_sugeridos porque
            // son los días que el paciente indicó en su formulario original y el psicólogo
            // necesita verlos para reprogramar sin pedirle datos de nuevo.
            DB::table('citas')->where('id', $citaId)->update([
                'estado' => 'pendiente',
                'bloque_propuesto' => null,
                'bloques_propuestos' => null,
                'propuesta_estado' => null,
                'propuesta_bloque_seleccionado' => null,
                'updated_at' => now(),
            ]);

            // Notificar al paciente que su cita fue pospuesta
            $citaModel = self::instanciarParaNotificacion($citaId);
            if ($citaModel) {
                try {
                    self::notificarUsuario($cita->user_id, new \App\Notifications\CitaCancelledNotification($citaModel, 'pospuesta'));
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            DB::commit();
            return [true, 'La cita ha sido pospuesta y devuelta a pendientes.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error interno al posponer la cita: ' . $e->getMessage()];
        }
    }

    /**
     * Propone un nuevo bloque de horario para una cita, validando colisiones.
     * Sigue el estándar de transacciones y comentarios detallados.
     */
    public static function proponer($citaId, $psicologoId, $fecha, $nuevoBloque)
    {
        try {
            DB::beginTransaction();

            // 1. Buscamos el registro
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita) return [false, 'Error: Cita no encontrada.'];

            $cita = self::desencriptarItem($cita);

            if ($cita->motivo !== 'Gestionada por psicólogo' && !self::validarBloqueFuturo($fecha, $nuevoBloque)) {
                return [false, 'No se pueden proponer fechas u horas pasadas.'];
            }

            $bloqueConFecha = $fecha . '|' . $nuevoBloque;
            $bloqueNormalizado = self::normalizarBloque($nuevoBloque);

            // 2. Verificamos si el bloque ya está ocupado por otra cita confirmada
            $bloqueConfirmadoExistente = DB::table('citas')
                ->where('psicologo_id', $psicologoId)
                ->where('fecha', $fecha)
                ->where('estado', 'confirmada')
                ->get()
                ->first(function ($otraCita) use ($bloqueNormalizado) {
                    return $otraCita->bloque_propuesto && self::normalizarBloque($otraCita->bloque_propuesto) === $bloqueNormalizado;
                });

            if ($bloqueConfirmadoExistente) {
                return [false, 'Conflicto: Este bloque horario ya tiene una cita confirmada para esta fecha.'];
            }

            // 3. Verificamos si ya hay una propuesta enviada a otro paciente
            $propuestaPendienteExistente = DB::table('citas')
                ->where('psicologo_id', $psicologoId)
                ->where('id', '!=', $citaId)
                ->where('propuesta_estado', 'pendiente')
                ->get()
                ->first(function ($otraCita) use ($bloqueConFecha) {
                    $otraCitaDes = self::desencriptarItem($otraCita);
                    $bloquesOtros = array_filter(array_map('trim', explode(';', $otraCitaDes->bloques_propuestos ?? '')));
                    return in_array($bloqueConFecha, $bloquesOtros);
                });

            if ($propuestaPendienteExistente) {
                return [false, 'Conflicto: Ya has enviado una solicitud para esta fecha y bloque a otro paciente que está en espera de respuesta.'];
            }

            // 4. Procesamos la lista de bloques propuestos
            $bloquesPropuestos = array_filter(array_map('trim', explode(';', $cita->bloques_propuestos ?? '')));

            $yaPropuesto = false;
            foreach ($bloquesPropuestos as $propuesta) {
                if ($propuesta === $bloqueConFecha) {
                    $yaPropuesto = true;
                    break;
                }
            }

            // Si ya estaba propuesto
            if ($yaPropuesto) {
                if ($cita->propuesta_estado === 'rechazada') {
                    return [false, 'El paciente ya rechazó una propuesta para este mismo horario. Por favor, elige otro bloque.'];
                }
                return [false, 'Este paciente ya se encuentra propuesto en este bloque.'];
            }

            // 5. Actualizamos solo si no estaba propuesto previamente
            $bloquesPropuestos[] = $bloqueConFecha;
            DB::table('citas')->where('id', $citaId)->update([
                'bloques_propuestos' => Crypt::encryptString(implode(';', $bloquesPropuestos)),
                'updated_at' => now(),
            ]);

            DB::commit();
            return [true, 'Bloque propuesto correctamente.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error interno al proponer bloque: ' . $e->getMessage()];
        }
    }

    /**
     * Elimina una propuesta de horario específica de una cita.
     * Solo quita de bloques_propuestos. NO modifica bloques_sugeridos.
     * Los bloques solo se "descartan" cuando el paciente rechaza la contrapropuesta.
     */
    public static function quitarPropuesta($citaId, $fecha, $bloque)
    {
        try {
            DB::beginTransaction();
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita) {
                DB::rollBack();
                return [false, 'Cita no encontrada.'];
            }

            $cita = self::desencriptarItem($cita);

            if (!$bloque || !$fecha) {
                DB::commit();
                return [true, 'No se especificó la fecha o el bloque.'];
            }

            $bloqueConFecha = $fecha . '|' . $bloque;

            // Quitar de propuestos solamente
            $propuestos = array_filter(array_map('trim', explode(';', $cita->bloques_propuestos ?? '')));
            $propuestos = array_filter($propuestos, function ($item) use ($bloqueConFecha) {
                return $item !== $bloqueConFecha;
            });
            $nuevosPropuestos = $propuestos ? implode(';', $propuestos) : null;

            DB::table('citas')->where('id', $citaId)->update([
                'bloques_propuestos' => $nuevosPropuestos ? Crypt::encryptString($nuevosPropuestos) : null,
                'updated_at' => now(),
            ]);

            DB::commit();
            return [true, 'Propuesta retirada.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al quitar propuesta: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene la lista de citas pendientes para un psicólogo con soporte de búsqueda y filtros.
     */
    public static function obtenerPendientes($psicologoId, $prioridadFilter = null, $q = null, $perPage = 15)
    {
        if (!$psicologoId) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
            return $emptyPaginator;
        }

        $prioridades = \App\Models\Prioridad::obtenerParaPsicologo($psicologoId);
        $validPrioridades = $prioridades->pluck('nombre')->toArray();
        $orderedPrioridades = $prioridades->sortByDesc('nivel_gravedad')->pluck('nombre')->toArray();

        $confirmedPatientIds = DB::table('citas')
            ->where('psicologo_id', $psicologoId)
            ->where('estado', 'confirmada')
            ->pluck('user_id')
            ->unique()
            ->all();

        $query = DB::table('citas')
            ->join('users', 'citas.user_id', '=', 'users.id')
            ->select('citas.*', 'users.nombres as user_nombres', 'users.apellidos as user_apellidos', 'users.email as paciente_email', 'users.cedula as paciente_cedula', 'users.horario_path as paciente_horario_path')
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'pendiente');

        if (!empty($confirmedPatientIds)) {
            $query->whereNotIn('citas.user_id', $confirmedPatientIds);
        }

        if ($prioridadFilter && in_array(strtolower($prioridadFilter), array_map('strtolower', $validPrioridades), true)) {
            $query->where('citas.prioridad', strtolower($prioridadFilter));
        }

        if ($q) {
            $buscarNormalized = mb_strtolower($q, 'UTF-8');
            $query->where(function($s) use ($buscarNormalized) {
                $s->whereRaw("LOWER(COALESCE(users.nombres, '')) LIKE ?", ["%{$buscarNormalized}%"])
                  ->orWhereRaw("LOWER(COALESCE(users.apellidos, '')) LIKE ?", ["%{$buscarNormalized}%"])
                  ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellidos, '')))) LIKE ?", ["%{$buscarNormalized}%"])
                  ->orWhereRaw("LOWER(COALESCE(users.cedula, '')) LIKE ?", ["{$buscarNormalized}%"]);
            });
        }

        $prioridadesList = count($orderedPrioridades) > 0 ? $orderedPrioridades : ['crítica', 'alta', 'media', 'baja'];
        $caseSql = "CASE citas.prioridad ";
        foreach ($prioridadesList as $index => $prio) {
            $val = $index + 1;
            $escapedPrio = addslashes($prio);
            $caseSql .= "WHEN '{$escapedPrio}' THEN {$val} ";
        }
        $caseSql .= "ELSE " . (count($prioridadesList) + 1) . " END";

        $paginator = $query->orderByRaw($caseSql)
            ->orderBy('citas.created_at', 'desc')
            ->paginate($perPage);

        $paginator->getCollection()->transform(function($item) {
            $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
            $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;

            // Desencriptar campos sensibles
            $item = self::desencriptarItem($item);

            // Simular el shortName del modelo User
            $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
            $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
            $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';

            return $item;
        });

        return $paginator;
    }


    /**
     * Obtiene el historial completo de citas para un paciente específico.
     */
    public static function obtenerHistorialPaciente($userId, $cantidad = 12, $startDate = null, $endDate = null, $prioridad = null)
    {
        $query = DB::table('citas')
            ->join('users', 'citas.psicologo_id', '=', 'users.id')
            ->select('citas.id as id', 'citas.user_id', 'citas.psicologo_id', 'citas.fecha', 'citas.hora', 'citas.estado', 'citas.cancelado_por', 'citas.motivo', 'citas.notas', 'citas.prioridad', 'citas.created_at', 'citas.updated_at', 'citas.estado_animo_id', 'users.nombres', 'users.apellidos')
            ->where('citas.user_id', $userId)
            ->whereIn('citas.estado', ['realizada', 'no_asistio', 'cancelada', 'rechazada']);

        if ($startDate) {
            $query->whereDate('citas.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('citas.created_at', '<=', $endDate);
        }
        if ($prioridad) {
            $query->where('citas.prioridad', $prioridad);
        }

        $paginator = $query->orderBy('citas.created_at', 'desc')
            ->paginate($cantidad);

        $coleccionFiltrada = $paginator->getCollection()->transform(function($item) {
            $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
            $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;

            // Desencriptar campos sensibles
            $item = self::desencriptarItem($item);

            $firstName = explode(' ', trim($item->nombres ?? ''))[0];
            $firstLastName = explode(' ', trim($item->apellidos ?? ''))[0];
            $item->psicologo_short_name = trim($firstName . ' ' . $firstLastName);
            $item->psicologo_nombre = $item->psicologo_short_name;

            if (trim($item->motivo) === 'Asignado manualmente por psicólogo') {
                $item->motivo = 'Gestionada por psicólogo';
            }

            return $item;
        })->filter(function($item) {
            return trim($item->motivo) !== 'Nota de Evolución (Manual)';
        })->values();

        $paginator->setCollection($coleccionFiltrada);

        return $paginator;
    }

    /**
     * Obtiene el historial de citas para un psicólogo con filtros opcionales.
     * Usado en la vista "list" de la agenda del psicólogo.
     */
    public static function obtenerHistorial($psicologoId, $cantidad = 12, $startDate = null, $endDate = null, $estado = null, $avanceId = null, $estadoAnimoId = null, $prioridad = null, $tipoFiltroFecha = 'rango')
    {
        $query = DB::table('citas')
            ->join('users', 'citas.user_id', '=', 'users.id')
            ->select('citas.id as id', 'citas.user_id', 'citas.psicologo_id', 'citas.fecha', 'citas.hora', 'citas.estado', 'citas.cancelado_por', 'citas.motivo', 'citas.notas', 'citas.prioridad', 'citas.created_at', 'citas.updated_at', 'citas.estado_animo_id', 'users.nombres as user_nombres', 'users.apellidos as user_apellidos')
            ->where('citas.psicologo_id', $psicologoId);

        // Filtrado por fecha según el tipo seleccionado
        if (!empty($startDate) || !empty($endDate)) {
            if ($tipoFiltroFecha === 'primera_cita') {
                // Filtrar citas de pacientes cuya PRIMERA cita realizada caiga en el rango
                $subquery = DB::table('citas as sub_citas')
                    ->select('sub_citas.user_id')
                    ->where('sub_citas.psicologo_id', $psicologoId)
                    ->where('sub_citas.estado', 'realizada')
                    ->groupBy('sub_citas.user_id');

                if (!empty($startDate)) {
                    $subquery->havingRaw('MIN(sub_citas.fecha) >= ?', [$startDate]);
                }
                if (!empty($endDate)) {
                    $subquery->havingRaw('MIN(sub_citas.fecha) <= ?', [$endDate]);
                }

                $pacientesIds = $subquery->pluck('user_id');
                $query->whereIn('citas.user_id', $pacientesIds);
            } elseif ($tipoFiltroFecha === 'ultima_cita') {
                // Filtrar citas de pacientes cuya ÚLTIMA cita realizada caiga en el rango
                $subquery = DB::table('citas as sub_citas')
                    ->select('sub_citas.user_id')
                    ->where('sub_citas.psicologo_id', $psicologoId)
                    ->where('sub_citas.estado', 'realizada')
                    ->groupBy('sub_citas.user_id');

                if (!empty($startDate)) {
                    $subquery->havingRaw('MAX(sub_citas.fecha) >= ?', [$startDate]);
                }
                if (!empty($endDate)) {
                    $subquery->havingRaw('MAX(sub_citas.fecha) <= ?', [$endDate]);
                }

                $pacientesIds = $subquery->pluck('user_id');
                $query->whereIn('citas.user_id', $pacientesIds);
            } else {
                // Rango normal: filtra por fecha de creación de la cita
                if ($startDate) {
                    $query->whereDate('citas.created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $query->whereDate('citas.created_at', '<=', $endDate);
                }
            }
        }

        if ($estado) {
            if ($estado === 'cancelada_paciente') {
                $query->where('citas.estado', 'cancelada')->where('citas.cancelado_por', 'paciente');
            } elseif ($estado === 'cancelada_psicologo') {
                $query->where('citas.estado', 'cancelada')->where('citas.cancelado_por', 'psicologo');
            } elseif ($estado === 'sin_cita') {
                $query->whereNotExists(function ($q) use ($psicologoId) {
                    $q->select(DB::raw(1))
                        ->from('citas as sub_citas')
                        ->whereColumn('sub_citas.user_id', 'citas.user_id')
                        ->where('sub_citas.psicologo_id', $psicologoId)
                        ->where('sub_citas.estado', 'realizada');
                });
            } else {
                $query->where('citas.estado', $estado);
            }
        }
        if ($estadoAnimoId) {
            $query->where('citas.estado_animo_id', $estadoAnimoId);
        }
        if ($prioridad) {
            $query->where('citas.prioridad', $prioridad);
        }

        // Filtrar por avance de sesión (dentro del campo notas JSON)
        // Este filtro se aplica en PHP porque notas está cifrado

        $paginator = $query->orderBy('citas.created_at', 'desc')
            ->paginate($cantidad);

        $coleccionFiltrada = $paginator->getCollection()->transform(function($item) {
            $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
            $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;

            // Desencriptar campos sensibles
            $item = self::desencriptarItem($item);

            $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
            $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
            $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';
            $item->paciente_nombre = trim(($item->user_nombres ?? '') . ' ' . ($item->user_apellidos ?? '')) ?: 'Paciente';

            return $item;
        })->filter(function($item) {
            return trim($item->motivo) !== 'Nota de Evolución (Manual)';
        })->values();

        $paginator->setCollection($coleccionFiltrada);

        // Filtro de avance (post-query, ya que notas está cifrado)
        if ($avanceId) {
            $filtered = $paginator->getCollection()->filter(function($item) use ($avanceId) {
                if (!$item->notas) return false;
                $notas = is_string($item->notas) ? json_decode($item->notas, true) : $item->notas;
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($notas)) return false;
                return isset($notas['avance_estado']) && $notas['avance_estado'] == $avanceId;
            });
            $paginator->setCollection($filtered->values());
        }

        return $paginator;
    }

    /**
     * Actualiza el campo de notas de una cita de forma segura.
     */
    public static function actualizarNota($cita, $notas)
    {
        try {
            DB::beginTransaction();
            $targetId = is_object($cita) ? $cita->id : $cita;
            $res = DB::table('citas')->where('id', $targetId)->update([
                'notas' => Crypt::encryptString($notas),
                'updated_at' => now()
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza la prioridad de una cita específica.
     */
    public static function actualizarPrioridad($cita, $prioridad)
    {
        try {
            DB::beginTransaction();
            $targetId = is_object($cita) ? $cita->id : $cita;
            DB::table('citas')->where('id', $targetId)->update([
                'prioridad' => $prioridad,
                'updated_at' => now()
            ]);

            $citaActualizada = DB::table('citas')->where('id', $targetId)->first();
            if ($citaActualizada && $citaActualizada->user_id) {
                DB::table('users')->where('id', $citaActualizada->user_id)->update([
                    'infracciones_reset_at' => now()
                ]);
            }
            DB::commit();
            return [true, 'Prioridad actualizada correctamente.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al actualizar prioridad: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene estadísticas rápidas (realizadas, inasistencias, cancelaciones) de un paciente.
     */
    public static function obtenerEstadisticasPaciente($pacienteId, $psicologoId)
    {
        $realizadasQuery = DB::table('citas')
            ->where('user_id', $pacienteId)
            ->where('psicologo_id', $psicologoId)
            ->where('estado', 'realizada')
            ->where('status', 1)
            ->get(['id', 'motivo']);

        $realizadasCount = $realizadasQuery->filter(function($cita) {
            try {
                // If the decrypted motive is exactly 'Nota de Evolución (Manual)', it's a manual note, not a realized session.
                return Crypt::decryptString($cita->motivo) !== 'Nota de Evolución (Manual)';
            } catch (\Exception $e) {
                // If it can't be decrypted, assume it's a regular realized session
                return true;
            }
        })->count();

        $inasistenciasCount = DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'no_asistio')->count();
        $pacienteCancelPreCount = DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'cancelada')->where('cancelado_por', 'paciente')->whereNull('confirmado_en')->count();
        $pacienteCancelPostCount = DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'cancelada')->where('cancelado_por', 'paciente')->whereNotNull('confirmado_en')->count();
        $psicologoCancelCount = DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'cancelada')->where('cancelado_por', 'psicologo')->count();
        $rechazadasCount = DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'rechazada')->count();

        $totalActividades = $realizadasCount + $inasistenciasCount + $pacienteCancelPreCount + $pacienteCancelPostCount + $psicologoCancelCount + $rechazadasCount;

        return [
            'realizadas' => $realizadasCount,
            'inasistencias' => $inasistenciasCount,
            'paciente_cancel_pre' => $pacienteCancelPreCount,
            'paciente_cancel_post' => $pacienteCancelPostCount,
            'psicologo_cancel' => $psicologoCancelCount,
            'rechazadas' => $rechazadasCount,
            'total' => $totalActividades,
        ];
    }

    public static function obtenerCitasRealizadas($pacienteId, $psicologoId)
    {
        $paginator = DB::table('citas')
            ->where('psicologo_id', $psicologoId)
            ->where('user_id', $pacienteId)
            ->where('estado', 'realizada')
            ->where('status', 1)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(10);

        $paginator->getCollection()->transform(function($item) use ($pacienteId, $psicologoId) {
            $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;

            // Desencriptar antes de procesar notas_limpias
            $item = self::desencriptarItem($item);

            // Procesar notas JSON para obtener una versión limpia (notas_limpias)
            $item->notas_limpias = 'Sin notas registradas.';
            $data = null;
            if ($item->notas) {
                $data = json_decode($item->notas, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $preview = null;
                    foreach (['observaciones', 'avance_detalle', 'motivo_consulta', 'intervenciones'] as $previewField) {
                        if (!empty(trim($data[$previewField] ?? ''))) {
                            $preview = $data[$previewField];
                            break;
                        }
                    }
                    $item->notas_limpias = $preview ?: 'Sin observaciones.';
                } else {
                    $item->notas_limpias = $item->notas;
                }
            }

            if ($item->motivo === 'Nota de Evolución (Manual)') {
                $item->is_manual = true;
                if (is_array($data) && !empty($data['titulo_manual'])) {
                    $item->display_title = $data['titulo_manual'];
                } else {
                    $item->display_title = 'Nota de Evolución';
                }
            } else {
                $item->is_manual = false;
                $item->display_title = $item->motivo ?? 'Consulta General';
                
                // Calculate session number for this real cita
                $item->session_number = DB::table('citas')
                    ->where('user_id', $pacienteId)
                    ->where('psicologo_id', $psicologoId)
                    ->where('estado', 'realizada')
                    ->where('status', 1)
                    ->where(function($q) use ($item) {
                        $q->where('fecha', '<', $item->fecha->format('Y-m-d'))
                          ->orWhere(function($q2) use ($item) {
                              $q2->where('fecha', $item->fecha->format('Y-m-d'))
                                 ->where('hora', '<=', $item->hora);
                          });
                    })
                    ->get(['id', 'motivo'])
                    ->filter(function($c) {
                        try {
                            return Crypt::decryptString($c->motivo) !== 'Nota de Evolución (Manual)';
                        } catch (\Exception $e) {
                            return true;
                        }
                    })->count();
            }

            return $item;
        });

        return $paginator;
    }

    public static function crearNotaManual($pacienteId, $psicologoId)
    {
        try {
            DB::beginTransaction();
            $citaId = DB::table('citas')->insertGetId([
                'user_id' => $pacienteId,
                'psicologo_id' => $psicologoId,
                'fecha' => \Carbon\Carbon::today(),
                'hora' => \Carbon\Carbon::now()->format('H:i'),
                'estado' => 'realizada',
                'motivo' => Crypt::encryptString('Nota de Evolución (Manual)'),
                'notas' => Crypt::encryptString(''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cita = DB::table('citas')->where('id', $citaId)->first();
            DB::commit();
            return $cita;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene las citas de un psicólogo dentro de un rango de fechas.
     */
    public static function obtenerCitasPorRango($psicologoId, $inicio, $fin)
    {
        return DB::table('citas')
            ->join('users', 'citas.user_id', '=', 'users.id')
            ->select('citas.*', 'users.nombres as user_nombres', 'users.apellidos as user_apellidos', 'users.horario_path as paciente_horario_path')
            ->where('citas.psicologo_id', $psicologoId)
            ->whereBetween('citas.fecha', [$inicio, $fin])
            ->orderBy('citas.fecha')
            ->orderBy('citas.hora')
            ->get()
            ->map(function($item) {
                $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;

                // Desencriptar campos sensibles
                $item = self::desencriptarItem($item);

                $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
                $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
                $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';
                $item->paciente_nombre = trim(($item->user_nombres ?? '') . ' ' . ($item->user_apellidos ?? '')) ?: 'Paciente';

                return $item;
            });
    }

    /**
     * Actualiza una cita.
     */
    public static function actualizar($id, $data)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('citas')
                ->where('id', $id)
                ->update(array_merge($data, ['updated_at' => now()]));
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una cita.
     */
    public static function eliminar($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('citas')
                ->where('id', $id)
                ->update([
                    'estado' => 'cancelada',
                    'updated_at' => now()
                ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene las citas confirmadas de un psicólogo.
     */
    public static function obtenerCitasAsignadas($psicologoId)
    {
        return DB::table('citas')
            ->join('users', 'citas.user_id', '=', 'users.id')
            ->select('citas.*', 'users.nombres as user_nombres', 'users.apellidos as user_apellidos')
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'confirmada')
            ->get()
            ->map(function($item) {
                $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;

                // Desencriptar campos sensibles
                $item = self::desencriptarItem($item);

                $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
                $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
                $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';
                $item->paciente_nombre = trim(($item->user_nombres ?? '') . ' ' . ($item->user_apellidos ?? '')) ?: 'Paciente';

                return $item;
            });
    }

    /**
     * Obtiene las citas de un psicólogo para una fecha específica.
     */
    public static function obtenerCitasPorFecha($psicologoId, $fecha)
    {
        return DB::table('citas')
            ->join('users', 'citas.user_id', '=', 'users.id')
            ->select('citas.*', 'users.nombres as user_nombres', 'users.apellidos as user_apellidos')
            ->where('citas.psicologo_id', $psicologoId)
            ->whereDate('citas.fecha', $fecha)
            ->get()
            ->map(function($item) {
                $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;

                // Desencriptar campos sensibles
                $item = self::desencriptarItem($item);

                $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
                $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
                $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';
                $item->paciente_nombre = trim(($item->user_nombres ?? '') . ' ' . ($item->user_apellidos ?? '')) ?: 'Paciente';

                return $item;
            });
    }

    /**
     * Obtiene todas las citas solicitadas por un paciente específico.
     * CORREGIDO: Se seleccionan explícitamente las columnas propuesta_estado y propuesta_bloque_seleccionado
     */
    public static function obtenerPorPaciente($userId)
    {
        return DB::table('citas')
            ->join('users', 'citas.psicologo_id', '=', 'users.id')
            ->select(
                'citas.*',
                'users.nombres as psicologo_nombres',
                'users.apellidos as psicologo_apellidos',
                'citas.propuesta_estado',
                'citas.propuesta_bloque_seleccionado'
            )
            ->where('citas.user_id', $userId)
            ->orderBy('citas.created_at', 'desc')
            ->get()
            ->map(function($item) {
                $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
                $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;

                $item = self::desencriptarItem($item);
                $item->bloques_propuestos_raw = $item->bloques_propuestos;
                $firstName = explode(' ', trim($item->psicologo_nombres ?? ''))[0];
                $firstLastName = explode(' ', trim($item->psicologo_apellidos ?? ''))[0];
                $item->psicologo_nombre = trim($firstName . ' ' . $firstLastName) ?: 'N/A';
                
                // Formatear el motivo para citas manuales antiguas/nuevas
                if (trim($item->motivo) === 'Asignado manualmente por psicólogo') {
                    $item->motivo = 'Gestionada por psicólogo';
                }
                
                return $item;
            })
            ->filter(function($item) {
                return trim($item->motivo) !== 'Nota de Evolución (Manual)';
            })
            ->values(); // resetear indices después del filter
    }

    /**
     * Verifica si un paciente tiene una cita activa (pendiente o confirmada).
     */
    public static function tieneCitaActiva($userId)
    {
        return DB::table('citas')
            ->where('user_id', $userId)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->exists();
    }

    /**
     * Centraliza la recopilación de datos para la vista de la agenda.
     * Mueve toda la lógica de procesamiento del controlador al modelo.
     */
    public static function obtenerDataAgenda($request, $user)
    {
        $psicologos = collect();
        $psicologoId = $user->id;

        if ($user->role === 'admin') {
            $psicologos = DB::table('users')
                ->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
                ->where('role', 'psicologo')
                ->where('status', 1)
                ->get();
            if ($request->has('psicologo_id')) {
                $psicologoId = $request->input('psicologo_id');
            } else {
                $psicologoId = $psicologos->first()->id ?? null;
            }
        }

        // Parámetros de navegación
        $view = $request->input('view', 'week'); // month, week, list
        $dateStr = $request->input('date', now()->toDateString());
        $currentDate = \Carbon\Carbon::parse($dateStr);

        // Lógica según la vista
        $citasCalendario = collect();
        $calendarioData = [];

        if ($view === 'month') {
            $startOfMonth = $currentDate->copy()->startOfMonth();
            $endOfMonth = $currentDate->copy()->endOfMonth();
            $startOfGrid = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
            $endOfGrid = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);

            $citasCalendario = self::obtenerCitasPorRango($psicologoId, $startOfGrid->toDateString(), $endOfGrid->toDateString());

            // Construir grid de días
            $tempDate = $startOfGrid->copy();
            while ($tempDate <= $endOfGrid) {
                $calendarioData[] = [
                    'date' => $tempDate->toDateString(),
                    'day' => $tempDate->day,
                    'isCurrentMonth' => $tempDate->month === $currentDate->month,
                    'isToday' => $tempDate->isToday(),
                    'citas' => $citasCalendario->filter(fn($c) => $c->fecha->isSameDay($tempDate))
                ];
                $tempDate->addDay();
            }
        } elseif ($view === 'week') {
            // Corrección de Domingo: si se consulta el domingo, la semana inicia en el lunes siguiente
            if ($currentDate->dayOfWeek === \Carbon\Carbon::SUNDAY) {
                $currentDate = $currentDate->copy()->next(\Carbon\Carbon::MONDAY);
            } else {
                $currentDate = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            }
            $startOfWeek = $currentDate;
            $endOfWeek = $currentDate->copy()->endOfWeek(\Carbon\Carbon::FRIDAY);
            $citasCalendario = self::obtenerCitasPorRango($psicologoId, $startOfWeek->toDateString(), $endOfWeek->toDateString());
        } elseif ($view === 'list') {
            $startDate = $request->input('start_date', now()->subMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());
            $estado = $request->input('estado');
            $avanceId = $request->input('avance_id');
            $estadoAnimoId = $request->input('estado_animo_id');
            $prioridadHistorial = $request->input('prioridad');
            $tipoFiltroFecha = $request->input('tipo_filtro_fecha', 'rango');
            $citasCalendario = self::obtenerHistorial($psicologoId, 12, $startDate, $endDate, $estado, $avanceId, $estadoAnimoId, $prioridadHistorial, $tipoFiltroFecha);
        }

        // Solicitudes pendientes
        $prioridadFilter = trim((string) $request->input('prioridad'));
        $q = trim((string) $request->input('q'));
        $citasPendientes = self::obtenerPendientes($psicologoId, $prioridadFilter, $q);

        // Horarios base
        $grupoActivo = $psicologoId ? GrupoHorario::obtenerActivoPorPsicologo($psicologoId) : null;
        $horarios = collect();
        if ($grupoActivo) {
            $horarios = Horario::obtenerPorGrupo($grupoActivo->id);
        }

        $citasAsignadas = $psicologoId ? self::obtenerCitasAsignadas($psicologoId) : collect();

        $prioridadesDisponibles = $psicologoId ? \App\Models\Prioridad::obtenerParaPsicologo($psicologoId) : collect();

        return [
            'view' => $view,
            'currentDate' => $currentDate,
            'calendarioData' => $calendarioData,
            'citasCalendario' => $citasCalendario,
            'grupoActivo' => $grupoActivo,
            'horarios' => $horarios,
            'citasPendientes' => $citasPendientes,
            'citasAsignadas' => $citasAsignadas,
            'psicologos' => $psicologos,
            'psicologoId' => $psicologoId,
            'prioridadesDisponibles' => $prioridadesDisponibles
        ];
    }

    /**
     * Obtiene las citas de un día específico formateadas para JSON.
     */
    public static function obtenerCitasDiariasJson($psicologoId, $fecha)
    {
        $citas = self::obtenerCitasPorFecha($psicologoId, $fecha);

        return $citas->map(fn($c) => [
            'id' => $c->id,
            'paciente' => $c->paciente_nombre ?? 'Paciente sin nombre',
            'hora' => $c->hora ? \Carbon\Carbon::parse($c->hora)->format('g:i A') : 'S/H',
            'estado' => $c->estado,
            'paciente_id' => $c->user_id
        ]);
    }

    public static function contarCitas()
    {
        return DB::table('citas')->count();
    }

    public static function contarCitasHoy()
    {
        return DB::table('citas')->whereDate('fecha', Carbon::today())->count();
    }

    public static function obtenerCitasConfirmadasHoyPorPsicologo($psicologoId, $limit = 3)
    {
        return DB::table('citas')
            ->join('users as pacientes', 'citas.user_id', '=', 'pacientes.id')
            ->select('citas.*')
            ->selectRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellidos) as paciente_nombre")
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'confirmada')
            ->whereDate('citas.fecha', Carbon::today())
            ->whereTime('citas.hora', '>=', Carbon::now()->format('H:i:s'))
            ->orderBy('citas.hora')
            ->take($limit)
            ->get()
            ->map(function ($cita) {
                $cita->paciente = User::obtenerUsuarioPorId($cita->user_id);
                return $cita;
            });
    }

    /**
     * Envía formalmente una contrapropuesta al paciente.
     * Cambia el estado de propuesta a 'pendiente' y notifica al paciente.
     */
    public static function enviarPropuesta($citaId, $psicologoId = null)
    {
        try {
            DB::beginTransaction();
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita) {
                DB::rollBack();
                return [false, 'Cita no encontrada.'];
            }

            if ($psicologoId && $cita->psicologo_id != $psicologoId) {
                DB::rollBack();
                return [false, 'No tienes permiso para esta acción.'];
            }
            
            if ($cita->propuesta_estado === 'pendiente') {
                DB::rollBack();
                return [false, 'Ya se envió una contrapropuesta y está en espera de respuesta por parte del paciente.'];
            }

            $citaDes = self::desencriptarItem($cita);
            $propuestos = array_filter(array_map('trim', explode(';', $citaDes->bloques_propuestos ?? '')));

            if (empty($propuestos)) {
                DB::rollBack();
                return [false, 'No hay bloques propuestos para enviar. Primero arrastra al paciente a los bloques que deseas proponer.'];
            }

            foreach ($propuestos as $b) {
                $parts = explode('|', $b);
                if (count($parts) === 2 && $citaDes->motivo !== 'Gestionada por psicólogo' && !self::validarBloqueFuturo($parts[0], $parts[1])) {
                    DB::rollBack();
                    return [false, 'Uno de los bloques propuestos ya ha pasado. Por favor, remuévelo antes de enviar la propuesta.'];
                }
            }

            // Marcar como propuesta pendiente de respuesta del paciente
            DB::table('citas')->where('id', $citaId)->update([
                'propuesta_estado' => 'pendiente',
                'updated_at' => now(),
            ]);

            // Notificar al paciente
            $citaModel = self::instanciarParaNotificacion($citaId);
            if ($citaModel) {
                self::notificarUsuario($cita->user_id, new \App\Notifications\ContrapropuestaCitaNotification($citaModel));
            }

            DB::commit();
            return [true, 'La contrapropuesta ha sido enviada al paciente. Los bloques propuestos: ' . implode(', ', $propuestos)];
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al enviar la propuesta: ' . $e->getMessage()];
        }
    }

    /**
     * Permite al paciente responder a una contrapropuesta del psicólogo.
     */
    public static function responderPropuesta($citaId, $respuesta, $bloqueSeleccionado = null, $motivoRechazo = null, $nuevosBloques = null)
    {
        try {
            DB::beginTransaction();

            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita || $cita->propuesta_estado !== 'pendiente') {
                DB::rollBack();
                return [false, 'No hay una propuesta pendiente para responder.'];
            }

            if ($respuesta === 'aceptada') {
                // Desencriptar para obtener los bloques propuestos
                $citaDes = self::desencriptarItem($cita);
                $bloquesPropuestos = array_filter(array_map('trim', explode(';', $citaDes->bloques_propuestos ?? '')));

                if (empty($bloquesPropuestos)) {
                    DB::rollBack();
                    return [false, 'No hay bloque propuesto para confirmar.'];
                }

                // Tomar el bloque seleccionado o el primero si no hay selección
                $bloqueAConfirmar = $bloqueSeleccionado ?: $bloquesPropuestos[0];

                $parts = explode('|', $bloqueAConfirmar);
                if (count($parts) === 2 && $citaDes->motivo !== 'Gestionada por psicólogo' && !self::validarBloqueFuturo($parts[0], $parts[1])) {
                    DB::rollBack();
                    return [false, 'Esta propuesta no puede ser seleccionada porque su fecha u hora ya ha pasado.'];
                }

                // Si enviaron un bloque seleccionado, verificar que esté en la lista de propuestas
                if ($bloqueSeleccionado && !in_array($bloqueSeleccionado, $bloquesPropuestos)) {
                    DB::rollBack();
                    return [false, 'El bloque seleccionado no es válido.'];
                }

                $partes = explode('|', $bloqueAConfirmar, 2);
                $fecha = $partes[0] ?? null;
                $bloqueLabel = $partes[1] ?? null;

                if (!$fecha || !$bloqueLabel) {
                    DB::rollBack();
                    return [false, 'El formato del bloque propuesto no es válido.'];
                }

                // Extraer la hora de inicio del bloque
                preg_match('/(\d{1,2}:\d{2})/', $bloqueLabel, $horaMatch);
                $hora = $horaMatch[1] ?? '00:00';

                // Marcar propuesta como aceptada
                DB::table('citas')->where('id', $citaId)->update([
                    'propuesta_estado' => 'aceptada',
                    'propuesta_bloque_seleccionado' => Crypt::encryptString($bloqueAConfirmar),
                    'updated_at' => now(),
                ]);

                DB::commit();

                // Confirmar la cita automáticamente usando el método existente
                $resultado = self::confirmar($citaId, $cita->psicologo_id, [
                    'fecha' => $fecha,
                    'hora' => $hora,
                    'bloque' => $bloqueLabel,
                ]);

                if ($resultado[0]) {
                    // Notificar al psicólogo que su contrapropuesta fue aceptada
                    $citaModel = self::instanciarParaNotificacion($citaId);
                    if ($citaModel) {
                        self::notificarUsuario($cita->psicologo_id, new \App\Notifications\RespuestaPropuestaNotification($citaModel, 'aceptada'));
                    }
                    return [true, 'Contrapropuesta aceptada con éxito.'];
                }
                
                return $resultado;

            } elseif ($respuesta === 'rechazada') {
                // Si envían nuevos bloques, se resetea la propuesta para que inicie el ciclo
                if ($nuevosBloques) {
                    $citaDes = self::desencriptarItem($cita);
                    $propuestosArr = array_filter(array_map('trim', explode(';', $citaDes->bloques_propuestos ?? '')));

                    // Parsear bloques sugeridos por el paciente a partir del formato:
                    // "Días propuestos: ... | Horarios propuestos: YYYY-MM-DD: slot1, slot2; ..."
                    $pacienteBloques = [];
                    $parts = explode('|', $nuevosBloques);
                    $horariosPart = '';
                    foreach ($parts as $p) {
                        if (str_contains($p, 'Horarios propuestos:')) {
                            $horariosPart = trim(str_replace('Horarios propuestos:', '', $p));
                            break;
                        }
                    }

                    if ($horariosPart && $horariosPart !== 'Ninguno') {
                        $diasConSlots = array_filter(array_map('trim', explode(';', $horariosPart)));
                        foreach ($diasConSlots as $diaConSlot) {
                            $colonPos = strpos($diaConSlot, ':');
                            if ($colonPos !== false) {
                                $fecha = trim(substr($diaConSlot, 0, $colonPos));
                                $slotsStr = trim(substr($diaConSlot, $colonPos + 1));
                                $slots = array_filter(array_map('trim', explode(',', $slotsStr)));
                                foreach ($slots as $slot) {
                                    $pacienteBloques[] = $fecha . '|' . $slot;
                                }
                            }
                        }
                    }

                    // Normalizar bloques propuestos por el psicólogo
                    $propuestosArrNorm = [];
                    foreach ($propuestosArr as $pb) {
                        $propuestosArrNorm[] = self::normalizarBloque($pb);
                    }

                    // Validar si algún bloque propuesto por el paciente coincide exactamente con la contrapropuesta
                    foreach ($pacienteBloques as $pbPac) {
                        $pbPacNorm = self::normalizarBloque($pbPac);
                        if (in_array($pbPacNorm, $propuestosArrNorm)) {
                            DB::rollBack();
                            return [false, 'La fecha y hora que sugieres coincide con uno de los bloques de la contrapropuesta enviada por el psicólogo. Si deseas ese horario, por favor acepta la propuesta en lugar de sugerirla nuevamente.'];
                        }
                    }

                    $updateData = [
                        'propuesta_estado' => null,
                        'bloques_propuestos' => null,
                        'bloques_sugeridos' => Crypt::encryptString($nuevosBloques),
                        'updated_at' => now(),
                    ];
                } else {
                    $updateData = [
                        'propuesta_estado' => 'rechazada',
                        'updated_at' => now(),
                    ];
                }

                if ($motivoRechazo) {
                    $updateData['motivo'] = Crypt::encryptString($motivoRechazo);
                }

                DB::table('citas')->where('id', $citaId)->update($updateData);

                // Notificar al psicólogo que fue rechazada/reprogramada
                $psicologoRow = DB::table('users')->where('id', $cita->psicologo_id)->first();
                if ($psicologoRow) {
                    $citaModel = self::instanciarParaNotificacion($citaId);
                    if ($citaModel) {
                        self::notificarUsuario($cita->psicologo_id, new \App\Notifications\ContrapropuestaRechazadaNotification($citaModel));
                    }
                }

                DB::commit();
                return [true, 'Contrapropuesta rechazada y nueva solicitud enviada.'];
            }

            // Compatibilidad: 'cualquier_dia' y 'sugerencia_aceptada' antiguos
            $update = [
                'propuesta_estado' => $respuesta,
                'updated_at' => now(),
            ];

            if ($respuesta === 'sugerencia_aceptada' && $bloqueSeleccionado) {
                $update['propuesta_bloque_seleccionado'] = Crypt::encryptString($bloqueSeleccionado);
            }

            DB::table('citas')->where('id', $citaId)->update($update);

            // Notificar al psicólogo que aceptó
            $citaModel = self::instanciarParaNotificacion($citaId);
            if ($citaModel && $citaModel->psicologo_id) {
                self::notificarUsuario($citaModel->psicologo_id, new \App\Notifications\RespuestaPropuestaNotification($citaModel, 'aceptada'));
            }

            DB::commit();
            return [true, 'Respuesta registrada correctamente.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al registrar respuesta: ' . $e->getMessage()];
        }
    }

    public static function validarBloqueFuturo($fecha, $bloqueLabel)
    {
        preg_match('/(\d{1,2}:\d{2}\s*(?:AM|PM)?)/i', $bloqueLabel, $horaMatch);
        $hora = $horaMatch[1] ?? '00:00';
        try {
            $dt = \Carbon\Carbon::parse($fecha . ' ' . $hora);
            return !$dt->isPast();
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Oculta el mensaje de cancelación eliminando el bloque propuesto.
     */
    public static function ocultarMensajeCancelacion($citaId)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('citas')->where('id', $citaId)->update([
                'bloque_propuesto' => null,
                'updated_at' => now()
            ]);
            DB::commit();
            return [true, 'Mensaje ocultado correctamente.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al ocultar el mensaje: ' . $e->getMessage()];
        }
    }
    public static function obtenerUltimasCitasConfirmadasPsicologo($psicologoId, $limit = 5)
    {
        return DB::table('citas')
            ->join('users as pacientes', 'citas.user_id', '=', 'pacientes.id')
            ->select('citas.*')
            ->selectRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellidos) as paciente_nombre")
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'confirmada')
            ->orderBy('citas.updated_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($cita) {
                $cita->paciente = User::obtenerUsuarioPorId($cita->user_id);
                $nombres = explode(' ', trim($cita->paciente->nombres ?? ''));
                $apellidos = explode(' ', trim($cita->paciente->apellidos ?? ''));
                $cita->paciente_nombre_corto = ($nombres[0] ?? '') . ' ' . ($apellidos[0] ?? '');
                return $cita;
            });
    }

    public static function obtenerEstadisticasCitasPsicologo($psicologoId)
    {
        $stats = DB::table('citas')
            ->select('estado', DB::raw('count(*) as total'))
            ->where('psicologo_id', $psicologoId)
            ->whereIn('estado', ['realizada', 'cancelada'])
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');

        return [
            'realizada' => $stats->has('realizada') ? $stats->get('realizada')->total : 0,
            'cancelada' => $stats->has('cancelada') ? $stats->get('cancelada')->total : 0,
        ];
    }

    public static function obtenerTendenciaSemanalCitasPsicologo($psicologoId, $semanas = 4)
    {
        $tendencia = [];
        for ($i = $semanas - 1; $i >= 0; $i--) {
            $inicioSemana = Carbon::now()->subWeeks($i)->startOfWeek();
            $finSemana = Carbon::now()->subWeeks($i)->endOfWeek();
            
            $total = DB::table('citas')
                ->where('psicologo_id', $psicologoId)
                ->where('estado', 'realizada')
                ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
                ->count();

            $tendencia[] = [
                'semana' => 'Sem ' . $inicioSemana->weekOfYear,
                'total' => $total
            ];
        }
        return collect($tendencia);
    }

    public static function obtenerCitasPendientesAntiguasPsicologo($psicologoId, $limit = 5)
    {
        return DB::table('citas')
            ->join('users as pacientes', 'citas.user_id', '=', 'pacientes.id')
            ->select('citas.*')
            ->selectRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellidos) as paciente_nombre")
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'pendiente')
            ->orderBy('citas.created_at', 'asc')
            ->take($limit)
            ->get()
            ->map(function ($cita) {
                $cita->paciente = User::obtenerUsuarioPorId($cita->user_id);
                $nombres = explode(' ', trim($cita->paciente->nombres ?? ''));
                $apellidos = explode(' ', trim($cita->paciente->apellidos ?? ''));
                $cita->paciente_nombre_corto = ($nombres[0] ?? '') . ' ' . ($apellidos[0] ?? '');
                return $cita;
            });
    }

    public static function obtenerCitasPsicologoPacienteRaw($pacienteId, $psicologoId)
    {
        return DB::table('citas')
            ->where('user_id', $pacienteId)
            ->where('psicologo_id', $psicologoId)
            ->get();
    }

    public static function eliminarFisicamente($id)
    {
        return DB::table('citas')->where('id', $id)->delete();
    }
}
