<?php

namespace App\Http\Controllers;

use App\Mail\CitaConfirmada;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
            $citas = Cita::obtenerPorPaciente($user->id);
            return view('citas.index', compact('citas'));
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

        $psicologos = $this->obtenerPsicologosDisponibles();

        return view('citas.create', compact('psicologos', 'tieneCitaPendiente'));
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
            'motivo' => 'required|string|max:255',
            'bloques_sugeridos' => 'nullable|string|max:1000',
            'prioridad' => 'nullable|in:baja,media,alta,super-alta',
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

        return view('citas.edit_note', compact('cita'));
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
                    $noteLines[] = 'Estado: ' . ucfirst(str_replace('_', ' ', $data['avance_estado'] ?? 'N/A'));
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

        return response()->json([
            'id' => $detalle->id,
            'paciente' => $detalle->paciente_short_name,
            'psicologo' => $detalle->psicologo_short_name,
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
            'prioridad' => 'required|in:baja,media,alta,super-alta',
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

        // Si es una petición estructurada (nueva interfaz)
        if ($request->has('structured')) {
            $rules = [
                'motivo_consulta' => 'nullable|string|max:2000',
                'observaciones' => $marcarRealizada ? 'required|string|max:5000' : 'nullable|string|max:5000',
                'intervenciones' => 'nullable|string|max:5000',
                'diagnosticos' => 'nullable|array',
                'avance_estado' => 'nullable|string|in:estancado,en_progreso,logrado',
                'avance_detalle' => 'nullable|string|max:2000',
                'plan_tratamiento' => 'nullable|string|max:2000',
                'proxima_cita_fecha' => 'nullable|date',
                'proxima_cita_razon' => 'nullable|string|max:1000',
            ];

            $messages = [
                'observaciones.required' => 'La nota de evolución clínica (Observaciones) es obligatoria para completar la cita.',
            ];

            $validated = $request->validate($rules, $messages);

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
            Cita::marcarRealizada($cita->id, $user->id);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'notas' => $cita->notas]);
        }

        if ($marcarRealizada) {
            return redirect()->route('historias.show', $cita->user_id)->with('success', 'La cita se ha completado con éxito y la nota de evolución ha sido registrada.');
        }

        return redirect()->back()->with('success', 'Nota de sesión actualizada correctamente.');
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

        // 4. Respuesta estandarizada en formato JSON (Estándar Prestamo)
        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    /**
     * Permite al paciente o administrador cancelar una cita activa.
     */
    public function cancel($citaId)
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
        [$isPass, $message] = Cita::cancelar($cita->id, $user->id);

        // 3. Respuesta estandarizada JSON (Estándar Prestamo)
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
            'bloque' => 'required|string|max:255',
        ]);

        // 3. Registro de la propuesta en el modelo
        [$isPass, $message] = Cita::proponer($cita->id, $user->id, $validated['bloque']);

        // 4. Respuesta estandarizada JSON (Estándar Prestamo)
        return response()->json([
            'status' => $isPass ? 'success' : 'error',
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
        [$isPass, $message] = Cita::quitarPropuesta($cita->id, $request->input('bloque'));

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
        if (!$user || $user->role !== 'psicologo' || $cita->psicologo_id !== $user->id) {
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

        // 3. Respuesta estandarizada JSON (Estándar Prestamo)
        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    /**
     * Obtiene el historial resumido de citas del paciente autenticado.
     */
    public function historyJson()
    {
        $userId = Auth::id(); $user = $this->obtenerUsuario($userId);

        if (!$user || $user->role !== 'paciente') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $citas = Cita::obtenerHistorialPaciente($user->id);

        return response()->json($citas->map(fn($c) => [
            'id' => $c->id,
            'psicologo' => $c->psicologo_nombre ?? 'Sin asignar',
            'fecha' => $c->fecha ? \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') : 'S/F',
            'fecha_formateada' => $c->fecha ? \Carbon\Carbon::parse($c->fecha)->translatedFormat('l d \d\e F, Y') : 'S/F',
            'hora' => $c->hora ? \Carbon\Carbon::parse($c->hora)->format('g:i A') : 'S/H',
            'estado' => $c->estado,
            'motivo' => $c->motivo,
            'notas' => $c->notas
        ]));
    }

}





