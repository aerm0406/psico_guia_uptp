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

    private static function notificarUsuario($userId, $notification)
    {
        $data = DB::table('users')->where('id', $userId)->first();
        if (!$data) return;

        $notifiable = new class {
            use \Illuminate\Notifications\Notifiable;
            public $id, $email, $nombres, $apellidos, $name;
            public function getKey() { return $this->id; }
            public function getMorphClass() { return 'App\Models\User'; }
            public function routeNotificationForMail($n) { return $this->email; }
            public function notifications() {
                return new class($this->id) {
                    private $userId;
                    public function __construct($userId) {
                        $this->userId = $userId;
                    }
                    public function create($data) {
                        \Illuminate\Support\Facades\DB::table('notifications')->insert([
                            'id' => $data['id'] ?? \Illuminate\Support\Str::uuid()->toString(),
                            'type' => $data['type'],
                            'notifiable_type' => 'App\Models\User',
                            'notifiable_id' => $this->userId,
                            'data' => is_array($data['data']) ? json_encode($data['data']) : $data['data'],
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => null,
                        ]);
                    }
                };
            }
        };

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

        $paginator->getCollection()->transform(function($item) {
            $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
            $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
            return self::desencriptarItem($item);
        });

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

        $campos = ['motivo', 'notas', 'bloques_sugeridos', 'bloques_propuestos', 'bloque_propuesto'];
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
        
        $value = preg_replace('/(^|\s|-)(\d):/', '${1}0$2:', $value);
        
        return strtolower($value);
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
                self::notificarUsuario($psicologoId, new \App\Notifications\AvisoAtencionPsicologoNotification($pacienteMock, $ultimaCita));
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
                'fecha' => now()->toDateString(),
                'hora' => null,
                'estado' => 'pendiente',
                'prioridad' => $prioridad,
                'motivo' => Crypt::encryptString($validated['motivo']),
                'bloques_sugeridos' => !empty($validated['bloques_sugeridos']) ? Crypt::encryptString($validated['bloques_sugeridos']) : null,
                'created_at' => now(),
                'updated_at' => now(),
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
            $fechaHoraCita = \Carbon\Carbon::parse($validated['fecha'] . ' ' . $validated['hora']);
            if ($fechaHoraCita->isBefore(now())) {
                return [false, 'Validación fallida: No se pueden agendar citas en fechas u horas que ya pasaron.'];
            }

            // 3. Verificación de disponibilidad: Evitar colisiones en el mismo bloque
            $bloqueConfirmadoExistente = DB::table('citas')
                ->where('psicologo_id', $psicologoId)
                ->where('estado', 'confirmada')
                ->where('fecha', $validated['fecha'])
                ->where('id', '!=', $citaId)
                ->get()
                ->first(function ($otraCita) use ($validated) {
                    return $otraCita->bloque_propuesto && self::normalizarBloque($otraCita->bloque_propuesto) === self::normalizarBloque($validated['bloque']);
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

                DB::table('citas')->where('id', $citaId)->update([
                    'estado' => 'cancelada',
                    'cancelado_por' => $actor,
                    'bloque_propuesto' => null,
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
     * Propone un nuevo bloque de horario para una cita, validando colisiones.
     * Sigue el estándar de transacciones y comentarios detallados.
     */
    public static function proponer($citaId, $psicologoId, $nuevoBloque)
    {
        try {
            DB::beginTransaction();

            // 1. Buscamos el registro
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita) return [false, 'Error: Cita no encontrada.'];

            $cita = self::desencriptarItem($cita);

            // 2. Verificamos si el bloque ya está ocupado por otra cita confirmada
            $bloqueNormalizado = self::normalizarBloque($nuevoBloque);
            
            $bloqueConfirmadoExistente = DB::table('citas')
                ->where('psicologo_id', $psicologoId)
                ->where('estado', 'confirmada')
                ->get()
                ->first(function ($otraCita) use ($bloqueNormalizado) {
                    return $otraCita->bloque_propuesto && self::normalizarBloque($otraCita->bloque_propuesto) === $bloqueNormalizado;
                });

            if ($bloqueConfirmadoExistente) {
                return [false, 'Conflicto: Este bloque horario ya tiene una cita confirmada.'];
            }

            // 3. Procesamos la lista de bloques propuestos (evitando duplicados normalizados)
            $bloquesPropuestos = array_filter(array_map('trim', explode(';', $cita->bloques_propuestos ?? '')));
            
            $yaPropuesto = false;
            foreach ($bloquesPropuestos as $propuesta) {
                if (self::normalizarBloque($propuesta) === $bloqueNormalizado) {
                    $yaPropuesto = true;
                    break;
                }
            }

            // 4. Actualizamos solo si no estaba propuesto previamente
            if (!$yaPropuesto) {
                $bloquesPropuestos[] = $nuevoBloque;
                DB::table('citas')->where('id', $citaId)->update([
                    'bloques_propuestos' => Crypt::encryptString(implode('; ', $bloquesPropuestos)),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return [true, 'Bloque propuesto correctamente.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error interno al proponer bloque: ' . $e->getMessage()];
        }
    }

    /**
     * Elimina una propuesta de horario específica de una cita.
     */
    public static function quitarPropuesta($citaId, $bloque)
    {
        try {
            DB::beginTransaction();
            $cita = DB::table('citas')->where('id', $citaId)->first();
            if (!$cita) {
                DB::rollBack();
                return [false, 'Cita no encontrada.'];
            }

            $cita = self::desencriptarItem($cita);

            if (!$bloque) {
                DB::commit();
                return [true, 'No se especificó el bloque.'];
            }

            $normalizedTarget = self::normalizarBloque($bloque);
            
            // Quitar de propuestos
            $propuestos = array_filter(array_map('trim', explode(';', $cita->bloques_propuestos ?? '')));
            $propuestos = array_filter($propuestos, function ($item) use ($normalizedTarget) {
                return self::normalizarBloque($item) !== $normalizedTarget;
            });
            $nuevosPropuestos = $propuestos ? implode('; ', $propuestos) : null;
            
            // Quitar de sugeridos (interesados) para que desaparezca visualmente de ese bloque
            $sugeridos = array_filter(array_map('trim', explode(';', $cita->bloques_sugeridos ?? '')));
            $sugeridos = array_filter($sugeridos, function ($item) use ($normalizedTarget) {
                return self::normalizarBloque($item) !== $normalizedTarget;
            });
            $nuevosSugeridos = $sugeridos ? implode('; ', $sugeridos) : null;
            
            DB::table('citas')->where('id', $citaId)->update([
                'bloques_propuestos' => $nuevosPropuestos ? Crypt::encryptString($nuevosPropuestos) : null,
                'bloques_sugeridos' => $nuevosSugeridos ? Crypt::encryptString($nuevosSugeridos) : null,
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
    public static function obtenerPendientes($psicologoId, $prioridadFilter = null, $q = null)
    {
        if (!$psicologoId) return collect();

        $validPrioridades = ['baja', 'media', 'alta', 'super-alta'];

        $confirmedPatientIds = DB::table('citas')
            ->where('psicologo_id', $psicologoId)
            ->where('estado', 'confirmada')
            ->pluck('user_id')
            ->unique()
            ->all();

        $query = DB::table('citas')
            ->join('users', 'citas.user_id', '=', 'users.id')
            ->select('citas.*', 'users.nombres as user_nombres', 'users.apellidos as user_apellidos', 'users.email as paciente_email')
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'pendiente');

        if (!empty($confirmedPatientIds)) {
            $query->whereNotIn('citas.user_id', $confirmedPatientIds);
        }

        if ($prioridadFilter && in_array($prioridadFilter, $validPrioridades, true)) {
            $query->where('citas.prioridad', $prioridadFilter);
        }

        if ($q) {
            $query->where(function($s) use ($q) {
                $s->where('users.nombres', 'like', '%' . $q . '%')
                  ->orWhere('users.apellidos', 'like', '%' . $q . '%');
            });
        }

        return $query->orderByRaw("FIELD(citas.prioridad, 'super-alta', 'alta', 'media', 'baja')")
            ->orderBy('citas.created_at', 'desc')
            ->get()
            ->map(function($item) {
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
    }

    /**
     * Obtiene el historial de citas paginado para un psicólogo.
     */
    public static function obtenerHistorial($psicologoId, $cantidad = 12)
    {
        $paginator = DB::table('citas')
            ->join('users', 'citas.user_id', '=', 'users.id')
            ->select('citas.*', 'users.nombres', 'users.apellidos')
            ->where('citas.psicologo_id', $psicologoId)
            ->orderBy('citas.fecha', 'desc')
            ->orderBy('citas.hora', 'desc')
            ->paginate($cantidad);

        $paginator->getCollection()->transform(function($item) {
            $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
            $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;

            // Desencriptar campos sensibles
            $item = self::desencriptarItem($item);

            $firstName = explode(' ', trim($item->nombres ?? ''))[0];
            $firstLastName = explode(' ', trim($item->apellidos ?? ''))[0];
            $item->paciente_short_name = trim($firstName . ' ' . $firstLastName);
            $item->paciente_nombre = trim(($item->nombres ?? '') . ' ' . ($item->apellidos ?? ''));

            return $item;
        });

        return $paginator;
    }

    /**
     * Obtiene el historial completo de citas para un paciente específico.
     */
    public static function obtenerHistorialPaciente($userId)
    {
        return DB::table('citas')
            ->join('users', 'citas.psicologo_id', '=', 'users.id')
            ->select('citas.*', 'users.nombres', 'users.apellidos')
            ->where('citas.user_id', $userId)
            ->whereIn('citas.estado', ['realizada', 'no_asistio', 'cancelada', 'rechazada'])
            ->orderBy('citas.fecha', 'desc')
            ->orderBy('citas.hora', 'desc')
            ->get()
            ->map(function($item) {
                $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
                $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;

                // Desencriptar campos sensibles
                $item = self::desencriptarItem($item);

                $firstName = explode(' ', trim($item->nombres ?? ''))[0];
                $firstLastName = explode(' ', trim($item->apellidos ?? ''))[0];
                $item->psicologo_short_name = trim($firstName . ' ' . $firstLastName);
                $item->psicologo_nombre = trim(($item->nombres ?? '') . ' ' . ($item->apellidos ?? ''));

                return $item;
            });
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
        return [
            'realizadas' => DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'realizada')->count(),
            'inasistencias' => DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'no_asistio')->count(),
            'paciente_cancel_pre' => DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'cancelada')->where('cancelado_por', 'paciente')->whereNull('confirmado_en')->count(),
            'paciente_cancel_post' => DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'cancelada')->where('cancelado_por', 'paciente')->whereNotNull('confirmado_en')->count(),
            'psicologo_cancel' => DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'cancelada')->where('cancelado_por', 'psicologo')->count(),
            'rechazadas' => DB::table('citas')->where('user_id', $pacienteId)->where('psicologo_id', $psicologoId)->where('estado', 'rechazada')->count(),
        ];
    }

    public static function obtenerCitasRealizadas($pacienteId, $psicologoId)
    {
        return DB::table('citas')
            ->where('psicologo_id', $psicologoId)
            ->where('user_id', $pacienteId)
            ->where('estado', 'realizada')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get()
            ->map(function($item) {
                $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
                
                // Desencriptar antes de procesar notas_limpias
                $item = self::desencriptarItem($item);

                // Procesar notas JSON para obtener una versión limpia (notas_limpias)
                $item->notas_limpias = 'Sin notas registradas.';
                if ($item->notas) {
                    $data = json_decode($item->notas, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                        $item->notas_limpias = $data['observaciones'] ?? ($data['motivo_consulta'] ?? 'Sin observaciones.');
                    } else {
                        $item->notas_limpias = $item->notas;
                    }
                }
                
                return $item;
            });
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
            ->select('citas.*', 'users.nombres as user_nombres', 'users.apellidos as user_apellidos')
            ->where('citas.psicologo_id', $psicologoId)
            ->whereBetween('citas.fecha', [$inicio, $fin])
            ->where('citas.estado', '!=', 'cancelada')
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
     */
    public static function obtenerPorPaciente($userId)
    {
        return DB::table('citas')
            ->join('users', 'citas.psicologo_id', '=', 'users.id')
            ->select('citas.*', 'users.nombres as psicologo_nombres', 'users.apellidos as psicologo_apellidos')
            ->where('citas.user_id', $userId)
            ->orderBy('citas.created_at', 'desc')
            ->get()
            ->map(function($item) {
                $item->fecha = $item->fecha ? \Carbon\Carbon::parse($item->fecha) : null;
                $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
                
                $item = self::desencriptarItem($item);
                $item->psicologo_nombre = trim(($item->psicologo_nombres ?? '') . ' ' . ($item->psicologo_apellidos ?? '')) ?: 'N/A';
                return $item;
            });
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
            $citasCalendario = self::obtenerHistorial($psicologoId);
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
            'psicologoId' => $psicologoId
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

    public static function obtenerCitasConfirmadasHoyPorPsicologo($psicologoId, $limit = 2)
    {
        return DB::table('citas')
            ->join('users as pacientes', 'citas.user_id', '=', 'pacientes.id')
            ->select('citas.*')
            ->selectRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellidos) as paciente_nombre")
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'confirmada')
            ->whereDate('citas.fecha', Carbon::today())
            ->orderBy('citas.hora')
            ->take($limit)
            ->get();
    }
}
