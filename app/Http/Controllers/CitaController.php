<?php

namespace App\Http\Controllers;

use App\Mail\CitaConfirmada;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CitaController extends Controller
{
    /**
     * Muestra el historial completo de citas para el usuario autenticado.
     */
    public function index()
    {
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (!$user) {
            abort(403);
        }

        if ($user->role === 'admin') {
            $citas = Cita::obtenerCitasGlobales();
            return view('citas.index', compact('citas'));
        }

        if ($user->role === 'paciente') {
            // Marcar notificaciones relacionadas con citas como leídas
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', 'App\Models\User')
                ->whereNull('read_at')
                ->whereIn('type', [
                    'App\Notifications\CitaConfirmedNotification', 
                    'App\Notifications\CitaRechazadaNotification',
                    'App\Notifications\CitaCancelledNotification'
                ])
                ->update(['read_at' => now()]);

            $citas = Cita::obtenerPorPaciente($user->id);
            $prioridades = \App\Models\Prioridad::obtenerParaPsicologo();
            return view('citas.index', compact('citas', 'prioridades'));
        }

        abort(403);
    }

    /**
     * Carga la vista para que un paciente solicite una nueva cita.
     */
    public function create()
    {
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (! $user || $user->role !== 'paciente') {
            abort(403);
        }

        $tieneCitaPendiente = Cita::tieneCitaActiva($user->id);

        $psicologos = collect();
        if (!$tieneCitaPendiente) {
            $psicologos = $this->obtenerPsicologosDisponibles();
        }

        return view('citas.create', compact('psicologos', 'tieneCitaPendiente'));
    }

    /**
     * Endpoint AJAX para obtener los bloques disponibles para un psicólogo en una fecha específica.
     * Excluye bloques pasados si es hoy y aquellos con 10 o más solicitudes.
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'psicologo_id' => 'required|exists:users,id',
        ]);

        $grupoActivo = \App\Models\GrupoHorario::obtenerActivoPorPsicologo($request->psicologo_id);
        
        if (!$grupoActivo) {
            return response()->json(['disponibilidad' => []]);
        }

        $horarios = \Illuminate\Support\Facades\DB::table('horarios')
            ->where('grupo_horario_id', $grupoActivo->id)
            ->whereIn('activo', [1, 2])
            ->get();

        // Agrupar horarios por día de la semana
        $horariosPorDia = [];
        $diasMapSort = ['Lunes'=>1, 'Martes'=>2, 'Miércoles'=>3, 'Miercoles'=>3, 'Jueves'=>4, 'Viernes'=>5];
        foreach ($horarios as $h) {
            $diaName = $h->dia === 'Miercoles' ? 'Miércoles' : $h->dia;
            $horariosPorDia[$diaName][] = $h;
        }

        // Ordenar horas
        foreach ($horariosPorDia as $dia => &$hrs) {
            usort($hrs, function($a, $b) {
                return strcmp($a->hora_inicio, $b->hora_inicio);
            });
        }

        $startDate = now();
        $endDate = now()->addDays(30);

        // Obtener citas confirmadas
        $citasConfirmadas = \Illuminate\Support\Facades\DB::table('citas')
            ->where('psicologo_id', $request->psicologo_id)
            ->where('estado', 'confirmada')
            ->whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get(['fecha', 'hora']);

        $citasPorFecha = [];
        foreach ($citasConfirmadas as $cita) {
            $citasPorFecha[$cita->fecha][] = \Carbon\Carbon::parse($cita->hora);
        }

        $disponibilidad = [];
        $diasLargo = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        for ($i = 0; $i < 30; $i++) {
            $fechaObj = $startDate->copy()->addDays($i);
            $fechaStr = $fechaObj->format('Y-m-d');
            $diaSemana = $diasLargo[$fechaObj->dayOfWeek];

            if (!isset($horariosPorDia[$diaSemana])) continue;

            $citasDelDia = $citasPorFecha[$fechaStr] ?? [];
            $slotsLibres = [];
            $ahora = now();

            foreach ($horariosPorDia[$diaSemana] as $h) {
                $inicio = \Carbon\Carbon::parse($h->hora_inicio);
                $fin = \Carbon\Carbon::parse($h->hora_fin);

                // Si es hoy y la hora de inicio ya pasó, ignorar este bloque
                if ($i === 0 && $inicio->format('H:i:s') <= $ahora->format('H:i:s')) {
                    continue;
                }
                
                $ocupado = false;
                foreach ($citasDelDia as $horaCita) {
                    if ($horaCita->betweenIncluded($inicio, $fin->copy()->subMinute())) {
                        $ocupado = true;
                        break;
                    }
                }

                if (!$ocupado) {
                    $slotsLibres[] = $inicio->format('g:i A') . ' - ' . $fin->format('g:i A');
                }
            }

            if (!empty($slotsLibres)) {
                $disponibilidad[$fechaStr] = $slotsLibres;
            }
        }

        return response()->json([
            'disponibilidad' => $disponibilidad
        ]);
    }

    /**
     * Procesa y guarda una nueva solicitud de cita.
     */
    public function store(Request $request)
    {
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (! $user || $user->role !== 'paciente') {
            abort(403);
        }

        $validated = $request->validate([
            'psicologo_id' => 'required|exists:users,id',
            'fecha_solicitada' => 'required|date_format:Y-m-d|after_or_equal:today',
            'motivo' => 'required|string|max:100',
            'bloques_sugeridos' => 'required|string|max:1000',
            'prioridad' => 'nullable|string|max:50',
        ]);

        [$isPass, $message, $cita] = Cita::crear($user, $validated);

        if (! $isPass) {
            return back()->withErrors(['bloques_sugeridos' => $message])->withInput();
        }

        return redirect()->route('citas.index')->with('success', $message);
    }

    /**
     * Muestra la información detallada de una cita en particular.
     */
    public function show($citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (!$user || ($user->role !== 'paciente' && $user->role !== 'psicologo' && $user->role !== 'admin')) {
            abort(403);
        }

        if (($user->role === 'admin')) {
            return view('citas.show', compact('cita'));
        }

        if (($user->role === 'paciente') && $cita->user_id !== $user->id) {
            abort(403);
        }

        if (($user->role === 'psicologo') && $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        return view('citas.show', compact('cita'));
    }

    /**
     * Carga el formulario para editar las notas clínicas de una sesión.
     */
    public function editNote($citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (! $user || ! ($user->role === 'psicologo')) {
            abort(403);
        }

        if ($cita->psicologo_id !== $user->id) {
            abort(403);
        }

        $avances = \Illuminate\Support\Facades\DB::table('avances_sesion')->orderBy('nombre', 'asc')->get();
        $estadosAnimo = \Illuminate\Support\Facades\DB::table('estado_animos')->orderBy('valor', 'asc')->get();

        // Obtener campos dinámicos guardados en la cita
        $camposGuardadosRaw = \App\Models\CitaNotaEvolucion::obtenerPorCita($citaId);
        
        $camposOcultos = [
            'Detalle del Avance', 
            'Plan de Tratamiento', 
            'Diagnósticos Oficiales', 
            'Estado de Ánimo del Paciente', 
            'Estado de Evolución', 
            'Próxima Cita Recomendada'
        ];

        // Si no tiene campos guardados, cargar los del sistema por defecto
        if ($camposGuardadosRaw->isEmpty()) {
            $camposDefault = \App\Models\NotaEvolucionCampo::obtenerCamposDisponibles(null)->whereNull('psicologo_id');
            $camposGuardados = $camposDefault->filter(function($campo) use ($camposOcultos) {
                return !in_array($campo->titulo, $camposOcultos);
            })->map(function($campo) {
                return (object)[
                    'campo_id' => $campo->id,
                    'titulo' => $campo->titulo,
                    'contenido' => ''
                ];
            })->values();
        } else {
            // Filtrar los que ahora son nativos
            $camposGuardados = $camposGuardadosRaw->filter(function($campo) use ($camposOcultos) {
                return !in_array($campo->titulo, $camposOcultos);
            })->values();
        }
        
        // Obtener todos los campos personalizados del psicologo (para el modal)
        $camposDisponibles = \App\Models\NotaEvolucionCampo::obtenerPorPsicologo($user->id);

        return view('citas.edit_note', compact('cita', 'avances', 'estadosAnimo', 'camposGuardados', 'camposDisponibles'));
    }

    /**
     * Genera y descarga el PDF con el resumen de la cita.
     */
    public function downloadPdf($citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        /** @var \App\Models\User|null $user */
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (!$user || (!($user->role === 'paciente') && !($user->role === 'psicologo') && !($user->role === 'admin'))) {
            abort(403);
        }

        if (($user->role === 'admin')) {
            $pdf = $this->generatePdfContent($cita);
            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="nota-sesion-' . $cita->id . '.pdf"',
            ]);
        }

        if (($user->role === 'paciente') && $cita->user_id !== $user->id) {
            abort(403);
        }

        if (($user->role === 'psicologo') && $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        $pdf = $this->generatePdfContent($cita);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="nota-sesion-' . $cita->id . '.pdf"',
        ]);
    }

    private function generatePdfContent($cita): string
    {
        $paciente = User::obtenerUsuarioPorId($cita->user_id);
        $psicologo = User::obtenerUsuarioPorId($cita->psicologo_id);
        $pacienteName = $paciente ? $paciente->name : 'Desconocido';
        $psicologoName = $psicologo ? $psicologo->name : 'Desconocido';

        $headerLines = [
            'Psico-Guía UPTP',
            'Nota de sesión',
            'Paciente: ' . ($pacienteName ?: 'Desconocido'),
            'Psicólogo: ' . ($psicologoName ?: 'Desconocido'),
            'Fecha de sesión: ' . ($cita->fecha ? \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') : 'Sin fecha'),
            'Motivo de Solicitud: ' . ($cita->motivo ?: 'No definido'),
            '',
        ];

        $noteLines = [];
        $rawNotas = $cita->notas;

        try {
            $data = json_decode($rawNotas, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $noteLines[] = '--- DETALLES CLINICOS ---';
                $noteLines[] = '1. MOTIVO DE CONSULTA:';
                $noteLines[] = $data['motivo_consulta'] ?? 'No registrado';
                $noteLines[] = '';
                $noteLines[] = '2. OBSERVACIONES CLINICAS:';
                $obs = explode("\n", wordwrap($data['observaciones'] ?? 'No registrado', 80));
                $noteLines = array_merge($noteLines, $obs);
                $noteLines[] = '';
                $noteLines[] = '3. INTERVENCIONES / RESUMEN:';
                $int = explode("\n", wordwrap($data['intervenciones'] ?? 'No registrado', 80));
                $noteLines = array_merge($noteLines, $int);
                $noteLines[] = '';

                if (!empty($data['diagnosticos'])) {
                    $noteLines[] = 'DIAGNOSTICOS (CIE-10):';
                    foreach ($data['diagnosticos'] as $diag) {
                        $noteLines[] = "- " . ($diag['codigo'] ?? '') . " " . ($diag['nombre'] ?? '');
                    }
                    $noteLines[] = '';
                }

                if (!empty($data['avance_estado']) || !empty($data['avance_detalle'])) {
                    $noteLines[] = 'AVANCES DE SESIÓN:';
                    $avanceNombreDisplay = 'N/A';
                    if (!empty($data['avance_estado'])) {
                        $avanceRecord = \Illuminate\Support\Facades\DB::table('avances_sesion')->where('id', $data['avance_estado'])->first();
                        $avanceNombreDisplay = $avanceRecord ? $avanceRecord->nombre : 'ID: ' . $data['avance_estado'];
                    }
                    $noteLines[] = 'Estado: ' . $avanceNombreDisplay;
                    if (!empty($data['avance_detalle'])) {
                        $det = explode("\n", wordwrap($data['avance_detalle'], 80));
                        $noteLines = array_merge($noteLines, $det);
                    }
                    $noteLines[] = '';
                }

                $noteLines[] = 'PLAN DE TRATAMIENTO:';
                $noteLines[] = $data['plan_tratamiento'] ?? 'No registrado';

                if (!empty($data['proxima_cita_fecha'])) {
                    $noteLines[] = '';
                    $noteLines[] = 'PROXIMA CITA RECOMENDADA:';
                    $noteLines[] = 'Fecha: ' . $data['proxima_cita_fecha'];
                    $noteLines[] = 'Razón: ' . ($data['proxima_cita_razon'] ?? 'N/A');
                }
            } else {
                $noteLines = $rawNotas ? explode("\n", trim($rawNotas)) : ['No se registraron notas para esta sesión.'];
            }
        } catch (\Exception $e) {
            $noteLines = $rawNotas ? explode("\n", trim($rawNotas)) : ['No se registraron notas para esta sesión.'];
        }

        $lines = array_merge($headerLines, $noteLines);

        $content = '';
        $y = 760;
        foreach ($lines as $line) {
            if ($y < 40) {
                break;
            }

            $encodedLine = @iconv('UTF-8', 'CP1252//TRANSLIT', $line);
            if ($encodedLine === false) {
                $encodedLine = $line;
            }

            $encodedLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encodedLine);
            $content .= "BT /F1 12 Tf 45 $y Td (" . $encodedLine . ") Tj ET\n";
            $y -= 18;
        }

        $streamLength = strlen($content);
        $pdfParts = [];
        $pdfParts[] = "%PDF-1.4\n";
        $pdfParts[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdfParts[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdfParts[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $pdfParts[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";
        $pdfParts[] = "5 0 obj\n<< /Length $streamLength >>\nstream\n" . $content . "endstream\nendobj\n";

        $pdf = '';
        $positions = [];
        foreach ($pdfParts as $part) {
            $positions[] = strlen($pdf);
            $pdf .= $part;
        }

        $xrefStart = strlen($pdf);
        $pdf .= "xref\n0 " . (count($pdfParts) + 1) . "\n";
        $pdf .= sprintf("%010d %05d f \n", 0, 65535);
        foreach ($positions as $position) {
            $pdf .= sprintf("%010d %05d n \n", $position, 0);
        }

        $pdf .= "trailer\n<< /Size " . (count($pdfParts) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n$xrefStart\n%%EOF";

        return $pdf;
    }

    /**
     * Obtiene los datos detallados de una cita para mostrarlos en modales.
     */
    public function showJson($citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $this->authorizeAccess($cita);

        $detalle = Cita::obtenerDetalle($cita->id);

        if (!$detalle) {
            return response()->json(['error' => 'No se encontró el detalle de la cita'], 404);
        }

        // Obtener el paciente para la foto de perfil
        $paciente = User::obtenerUsuarioPorId($detalle->user_id);
        $fotoUrl = null;

        if ($paciente && $paciente->profile_photo_path) {
            // Verificar si la foto existe
            $fotoPath = $paciente->profile_photo_path;
            if (file_exists(public_path('storage/' . $fotoPath))) {
                $fotoUrl = asset('storage/' . $fotoPath);
            } else {
                $fotoUrl = null;
            }
        }

        $pacienteNombresStr = isset($detalle->paciente_nombres) ? explode(' ', trim($detalle->paciente_nombres))[0] : '';
        $pacienteApellidosStr = isset($detalle->paciente_apellidos) ? explode(' ', trim($detalle->paciente_apellidos))[0] : '';
        $pacienteShortName = trim($pacienteNombresStr . ' ' . $pacienteApellidosStr);

        $psicologoNombresStr = isset($detalle->psicologo_nombres) ? explode(' ', trim($detalle->psicologo_nombres))[0] : '';
        $psicologoApellidosStr = isset($detalle->psicologo_apellidos) ? explode(' ', trim($detalle->psicologo_apellidos))[0] : '';
        $psicologoShortName = trim($psicologoNombresStr . ' ' . $psicologoApellidosStr);

        return response()->json([
            'id' => $detalle->id,
            'paciente' => $pacienteShortName,
            'paciente_foto' => $fotoUrl,
            'psicologo' => $psicologoShortName,
            'fecha_solicitud' => \Carbon\Carbon::parse($detalle->created_at)->format('g:i A'),
            'fecha_solicitud_iso' => \Carbon\Carbon::parse($detalle->created_at)->toIso8601String(),
            'fecha_confirmada' => $detalle->estado === 'pendiente' ? 'Pendiente' : \Carbon\Carbon::parse($detalle->fecha)->format('Y-m-d'),
            'bloque_confirmado' => $detalle->bloque_propuesto ?: null,
            'hora_confirmada' => $detalle->confirmado_en ? \Carbon\Carbon::parse($detalle->confirmado_en)->format('g:i A') : 'En espera',
            'hora_confirmada_iso' => $detalle->confirmado_en ? \Carbon\Carbon::parse($detalle->confirmado_en)->toIso8601String() : null,
            'estado' => $detalle->estado === 'no_asistio' ? 'Ausente' : ucfirst($detalle->estado),
            'prioridad' => $detalle->prioridad ?? 'media',
            'motivo' => $detalle->motivo ?: 'No especificado',
            'bloques_sugeridos' => $detalle->bloques_sugeridos ?? '',
            'bloque_propuesto' => $detalle->bloque_propuesto,
            'bloques_propuestos' => $detalle->bloques_propuestos ?? '',
            'propuesta_estado' => $detalle->propuesta_estado ?? null,
            'propuesta_bloque_seleccionado' => $detalle->propuesta_bloque_seleccionado ?? null,
            'motivo_rechazo_propuesta' => $detalle->motivo_rechazo_propuesta ?? null,
            'paciente_horario' => $paciente && isset($paciente->horario_path) && $paciente->horario_path ? asset('storage/' . $paciente->horario_path) : null,
            'email' => $paciente->email ?? null,
            'telefono' => $paciente->telefono ?? null,
            'registrado_en' => $paciente && isset($paciente->created_at) && $paciente->created_at ? \Carbon\Carbon::parse($paciente->created_at)->format('d M, Y') : null,
            'cedula' => $paciente->cedula ?? null,
            'edad' => $paciente && isset($paciente->fecha_nacimiento) && $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age : null,
            'genero' => $paciente->genero ?? null,
            'nacimiento' => $paciente->fecha_nacimiento ?? null,
            'ubicacion' => $paciente->ubicacion ?? null,
            'discapacidad' => $paciente->discapacidad ?? null,
            'hijos' => $paciente->hijos ?? null,
            'civil' => $paciente->estado_civil ?? null,
            'perfil_academico' => $paciente->perfil_academico ?? null,
            'pnf' => $paciente->pnf ?? null,
            'semestre' => $paciente->semestre ?? null,
        ]);
    }

    /**
     * Valida si el usuario actual tiene permiso para ver una cita.
     */
    private function authorizeAccess($cita)
    {
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (!$user || ($user->role !== 'paciente' && $user->role !== 'psicologo' && $user->role !== 'admin')) {
            abort(403);
        }

        if ($user->role === 'admin') return;

        if ($user->role === 'paciente' && $cita->user_id !== $user->id) abort(403);
        if ($user->role === 'psicologo' && $cita->psicologo_id !== $user->id) abort(403);
    }

    /**
     * Actualiza el nivel de prioridad de una cita en espera.
     */
    public function updatePriority(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (! $user || $user->role !== 'psicologo') {
            abort(403);
        }

        if ($cita->psicologo_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'prioridad' => 'required|string|max:50',
        ]);

        Cita::actualizarPrioridad($cita, $validated['prioridad']);

        return response()->json(['success' => true, 'prioridad' => $cita->prioridad]);
    }

    /**
     * Guarda las notas clínicas y el seguimiento de una sesión realizada.
     */
    public function updateNote(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (! $user || $user->role !== 'psicologo') {
            abort(403);
        }

        if ($cita->psicologo_id !== $user->id) {
            abort(403);
        }

        $marcarRealizada = ($cita->estado === 'confirmada');
        $isManual = ($cita->motivo === 'Nota de Evolución (Manual)');
        $requireFields = $marcarRealizada && !$isManual;

        if ($marcarRealizada) {
            if (!\App\Models\PlantillaGlobal::tienePlantillaGlobal($user->id)) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Debe activar su Esquema General para el historial clínico antes de completar citas.',
                        'redirect_template' => true
                    ], 400);
                }
                return redirect()->route('plantillas-globales.index')->with('error', 'Debe activar su Esquema General para el historial clínico.');
            }
        }

        // Si es una petición estructurada (nueva interfaz)
        if ($request->has('structured')) {
            $rules = [
                'titulo_manual' => 'nullable|string|max:255',
                'diagnosticos' => 'nullable|array',
                'estado_animo_id' => $requireFields ? 'required|integer|exists:estado_animos,id' : 'nullable|integer|exists:estado_animos,id',
                'estado_animo_detalle' => $requireFields ? 'required|string|max:2000' : 'nullable|string|max:2000',
                'avance_estado' => $requireFields ? 'required|integer|exists:avances_sesion,id' : 'nullable|integer|exists:avances_sesion,id',
                'avance_detalle' => $requireFields ? 'required|string|max:2000' : 'nullable|string|max:2000',
                'proxima_cita_fecha' => 'nullable|date',
                'campos_dinamicos' => 'nullable|array',
                'campos_dinamicos.*' => 'nullable|string',
            ];

            $messages = [
                'avance_estado.required' => 'Debe seleccionar un nivel de avance clínico para completar la cita.',
                'avance_estado.exists' => 'El nivel de avance seleccionado no es válido.',
                'estado_animo_id.required' => 'Debe seleccionar un estado de ánimo del paciente para completar la cita.',
                'estado_animo_id.exists' => 'El estado de ánimo seleccionado no es válido.',
                'avance_detalle.required' => 'Debe detallar el avance de la sesión.',
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $camposDinamicos = $request->input('campos_dinamicos', []);

            if ($isManual) {
                // Verificar que al menos un campo tenga contenido
                $hasContent = false;
                if (!empty(trim($request->input('titulo_manual')))) {
                    $hasContent = true;
                }
                foreach ($camposDinamicos as $contenido) {
                    if (!empty(trim($contenido))) {
                        $hasContent = true;
                        break;
                    }
                }
                if ($request->input('estado_animo_id') || $request->input('avance_estado') || !empty($request->input('diagnosticos'))) {
                    $hasContent = true;
                }

                if (!$hasContent) {
                    return back()->withInput()->withErrors(['campos_dinamicos' => 'La nota manual no puede estar completamente vacía. Por favor, llena al menos un campo.']);
                }
            }

            $validated = $validator->validated();
            
            // Guardar campos dinámicos
            if (isset($validated['campos_dinamicos'])) {
                foreach ($validated['campos_dinamicos'] as $campoId => $contenido) {
                    if (!empty(trim($contenido))) {
                        \App\Models\CitaNotaEvolucion::guardarCampo($cita->id, $campoId, trim($contenido));
                    }
                }
                unset($validated['campos_dinamicos']); // Quitar del json estructurado
            }

            // Actualizar el estado de animo en la tabla principal
            if (isset($validated['estado_animo_id'])) {
                \Illuminate\Support\Facades\DB::table('citas')
                    ->where('id', $cita->id)
                    ->update(['estado_animo_id' => $validated['estado_animo_id']]);
            }

            $notaJson = json_encode($validated);
            Cita::actualizarNota($cita, $notaJson);
        } else {
            // Petición antigua (texto plano)
            $rules = [
                'notas' => $marcarRealizada ? 'required|string|max:5000' : 'nullable|string|max:5000',
            ];

            $messages = [
                'notas.required' => 'La nota de evolución clínica es obligatoria para completar la cita.',
            ];

            $validated = $request->validate($rules, $messages);
            Cita::actualizarNota($cita, $validated['notas']);
        }

        if ($marcarRealizada) {
            $resultado = Cita::marcarRealizada($cita->id, $user->id);
            if (!$resultado[0]) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $resultado[1]], 400);
                }
                return back()->withInput()->withErrors(['error' => $resultado[1]]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'notas' => $cita->notas]);
        }

        if ($marcarRealizada) {
            return redirect()->route('historias.show', ['paciente' => $cita->user_id, 'tab' => 'evolucion'])->with('success', 'La cita se ha completado con éxito y la nota de evolución ha sido registrada.');
        }

        return redirect()->route('historias.show', ['paciente' => $cita->user_id, 'tab' => 'evolucion'])->with('success', 'Nota de sesión actualizada correctamente.');
    }

    /**
     * Guarda un nuevo campo personalizado desde AJAX
     */
    public function storeCampoAjax(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:100'
        ]);

        $psicologoId = Auth::id();

        if (\App\Models\NotaEvolucionCampo::existeTitulo($psicologoId, $request->titulo)) {
            return response()->json(['success' => false, 'message' => 'Ya existe un campo con este título.']);
        }

        $campoId = \App\Models\NotaEvolucionCampo::crearPersonalizado($psicologoId, $request->titulo);

        return response()->json([
            'success' => true,
            'campo' => [
                'id' => $campoId,
                'titulo' => $request->titulo,
                'psicologo_id' => $psicologoId
            ]
        ]);
    }

    /**
     * Permite al psicólogo rechazar una solicitud de cita pendiente.
     */
    public function reject($citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        // 1. Autorización de seguridad
        if (! $user || $user->role !== 'psicologo') {
            abort(403);
        }

        if ($cita->psicologo_id !== $user->id) {
            abort(403);
        }

        // 2. Validación de datos de entrada
        $validated = request()->validate([
            'motivo_rechazo' => 'nullable|string|max:1000',
        ]);

        // 3. Ejecución de la lógica en el modelo
        [$isPass, $message] = Cita::rechazar($cita->id, $validated['motivo_rechazo']);

        // 4. Respuesta estandarizada en formato JSON
        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    /**
     * Permite al paciente o administrador cancelar una cita activa.
     */
    public function cancel(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        // 1. Autorización: Solo pacientes o administradores pueden cancelar desde aquí
        if (!$user || ($user->role !== 'paciente' && $user->role !== 'admin')) {
            abort(403);
        }

        if ($user->role !== 'admin' && $cita->user_id !== $user->id) {
            abort(403);
        }

        // 2. Ejecución de la lógica en el modelo
        [$isPass, $message] = Cita::cancelar($cita->id, $user->id, $request->input('motivo_cancelacion'));

        // 3. Respuesta estandarizada JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => $isPass ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->route('citas.index')->with($isPass ? 'success' : 'error', $message);
    }

    /**
     * Permite al psicólogo cancelar una cita que ya había sido confirmada.
     */
    public function cancelConfirmedByPsicologo(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        // 1. Validación de seguridad
        if (! $user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        // 2. Validación del motivo de cancelación
        $validated = $request->validate([
            'motivo_cancelacion' => 'nullable|string|max:1000',
        ]);

        // 3. Ejecución de la anulación en el modelo
        [$isPass, $message] = Cita::cancelar($cita->id, $user->id, $validated['motivo_cancelacion'] ?? null);

        // 4. Retorno de respuesta JSON (Estándar Prestamo)
        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    /**
     * Posponer una cita confirmada, enviándola a estado pendiente
     * y vaciando sus propuestas/bloques para descartar el día.
     */
    public function posponer(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (! $user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        [$isPass, $message] = Cita::posponer($cita->id, $user->id);

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    /**
     * Propone un bloque de horario para una cita desde la vista de agenda.
     */
    public function proponer(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        // 1. Autorización de acceso
        if (!$user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        // 2. Validación del bloque sugerido
        $validated = $request->validate([
            'fecha' => 'required|date',
            'bloque' => 'required|string|max:255',
        ]);

        // 3. Registro de la propuesta en el modelo
        [$isPass, $message] = Cita::proponer($cita->id, $user->id, $validated['fecha'], $validated['bloque']);

        // 4. Respuesta estandarizada JSON (Estándar Prestamo)
        $status = is_string($isPass) ? $isPass : ($isPass ? 'success' : 'error');
        
        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    /**
     * Elimina una propuesta de horario enviada previamente.
     */
    public function quitarPropuesta(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        // 1. Autorización de acceso
        if (!$user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        // 2. Ejecución de la eliminación en el modelo
        $fecha = $request->input('fecha');
        if (!$fecha) {
            return response()->json(['status' => 'error', 'message' => 'La fecha es obligatoria']);
        }
        [$isPass, $message] = Cita::quitarPropuesta($cita->id, $fecha, $request->input('bloque'));

        // 3. Respuesta estandarizada JSON (Estándar Prestamo)
        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }



    /**
     * Acepta y confirma definitivamente un bloque horario para una cita.
     */
    public function accept(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        // 1. Validación de seguridad y rol
        if (!$user || ($user->role !== 'psicologo' && $user->role !== 'admin')) {
            abort(403);
        }
        if ($user->role === 'psicologo' && $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        // 2. Validación de los datos de la cita (fecha, hora, bloque)
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|string',
            'bloque' => 'required|string|max:255',
        ]);

        // 3. Confirmación de la cita en el modelo
        [$isPass, $message] = Cita::confirmar($cita->id, $user->id, $validated);

        // 4. Retorno de respuesta JSON (Estándar Prestamo)
        $paciente = $this->obtenerUsuario($cita->user_id);

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message,
            'paciente' => $paciente ? (explode(' ', $paciente->name)[0]) : 'Paciente',
            'paciente_id' => $cita->user_id,
            'cita_id' => $cita->id
        ]);
    }

    /**
     * Finaliza la cita y la marca como realizada con éxito.
     */
    public function complete(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        // 1. Autorización: Solo el psicólogo asignado
        if (!$user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        // 2. Validación: Solo se gestiona para citas en estado Confirmada
        if ($cita->estado !== 'confirmada') {
            return response()->json([
                'status' => 'error',
                'message' => 'Solo se pueden registrar notas para citas en estado Confirmada.'
            ], 400);
        }

        // [NUEVO] Validación: Debe poseer una plantilla global registrada
        if (!\App\Models\PlantillaGlobal::tienePlantillaGlobal($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debe activar su Esquema General para el historial clínico antes de completar citas.',
                'redirect_template' => true
            ], 400);
        }



        // 3. Respuesta JSON estandarizada con URL de redirección
        return response()->json([
            'status' => 'success',
            'message' => 'Redirigiendo a la creación de la nota de evolución...',
            'paciente_id' => $cita->user_id,
            'redirect_url' => route('citas.edit.note', $cita->id)
        ]);
    }

    /**
     * Marca una cita como 'No asistió' cuando el paciente falta a la sesión.
     */
    public function noAsistio($citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        // 1. Validación de seguridad (Psicólogo o Admin)
        if (!$user || ($user->role !== 'psicologo' && $user->role !== 'admin')) {
            abort(403);
        }

        if ($user->role === 'psicologo' && $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        // 2. Ejecución de la lógica de inasistencia en el modelo (Transaccional)
        [$isPass, $message] = Cita::marcarNoAsistio($cita->id);

        if (!$isPass) {
            $isWarning = str_contains($message, 'no ha comenzado');
            return response()->json([
                'status' => 'error',
                'is_warning' => $isWarning,
                'message' => $isWarning ? 'No es posible procesar la cita antes de la fecha y hora programadas. Por favor, aguarde al inicio de la sesión para registrar su estado.' : $message
            ], 400);
        }

        // 3. Respuesta estandarizada JSON (Estándar Prestamo)
        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    public function historyJson(Request $request)
    {
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (!$user || $user->role !== 'paciente') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // If 'start_date' is passed as empty, we fetch all. Otherwise default to last month.
        $startDate = $request->has('start_date') ? $request->input('start_date') : now()->subMonth()->toDateString();
        $endDate = $request->has('end_date') ? $request->input('end_date') : now()->toDateString();
        $prioridad = $request->input('prioridad');
        
        $citas = Cita::obtenerHistorialPaciente($user->id, 12, $startDate, $endDate, $prioridad);

        $citas->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'psicologo' => $c->psicologo_nombre ?? 'Sin asignar',
            'fecha' => $c->fecha ? \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') : 'S/F',
            'fecha_formateada' => $c->fecha ? \Carbon\Carbon::parse($c->fecha)->translatedFormat('l d \d\e F, Y') : 'S/F',
            'hora' => $c->hora ? \Carbon\Carbon::parse($c->hora)->format('g:i A') : 'S/H',
            'estado' => $c->estado,
            'cancelado_por' => $c->cancelado_por ?? null,
            'motivo' => $c->motivo,
            'notas' => $c->notas
        ]);

        return response()->json($citas);
    }

    public function enviarPropuesta($citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (!$user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id) {
            abort(403);
        }

        [$isPass, $message] = Cita::enviarPropuesta($cita->id);

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function responderPropuesta(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (!$user || $user->role !== 'paciente' || $cita->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'opcion' => 'required|in:cualquier_dia,sugerencia_aceptada,rechazada,aceptada',
            'bloque' => 'nullable|string|max:255',
            'motivo_rechazo' => 'nullable|string|max:500',
            'nuevos_bloques' => 'nullable|string|max:2000',
        ]);

        [$isPass, $message] = Cita::responderPropuesta($cita->id, $validated['opcion'], $validated['bloque'] ?? null, $validated['motivo_rechazo'] ?? null, $validated['nuevos_bloques'] ?? null);
 
        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }
 
    public function descargarConstanciaPdf($citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        abort_if(!$user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id, 403);
        abort_if($cita->estado !== 'realizada', 400, 'La constancia solo se puede generar de citas realizadas.');
        abort_if($cita->motivo === 'Nota de Evolución (Manual)', 400, 'No se puede generar constancia de asistencia para notas manuales.');

        $paciente = User::obtenerUsuarioPorId($cita->user_id);
        $psicologo = User::obtenerUsuarioPorId($cita->psicologo_id);

        if ($paciente) {
            $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));
        }
        if ($psicologo) {
            $psicologo->name = trim(($psicologo->nombres ?? '') . ' ' . ($psicologo->apellidos ?? ''));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('historias.constanciaPDF', compact('cita', 'paciente', 'psicologo'))
            ->setPaper('a4', 'portrait');

        $slugPaciente = $paciente ? \Illuminate\Support\Str::slug($paciente->name) : 'paciente';
        return $pdf->stream('Constancia_Asistencia_' . $slugPaciente . '.pdf');
    }
 
    public function destroy($citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        abort_if(!$user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id, 403);
        abort_if($cita->motivo !== 'Nota de Evolución (Manual)', 400, 'Solo se pueden eliminar notas de evolución creadas manualmente.');
 
        \Illuminate\Support\Facades\DB::table('citas')->where('id', $citaId)->update(['status' => 0]);
 
        return redirect()->route('historias.show', $cita->user_id)->with('success', 'Nota de evolución manual eliminada correctamente.');
    }

    /**
     * Oculta el mensaje de cancelación de la agenda.
     */
    public function dismissCancelMessage(Request $request, $citaId)
    {
        $cita = \App\Models\Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);
        
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        abort_if(!$user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id, 403);
        
        [$isPass, $message] = Cita::ocultarMensajeCancelacion($citaId);
        
        return response()->json([
            'success' => $isPass,
            'message' => $message
        ]);
    }
}
