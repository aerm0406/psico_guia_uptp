<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\SeccionPersonalizada;
use App\Models\SegmentoPersonalizado;
use App\Models\PlantillaSeccion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HistoriaController extends Controller
{
    /**
     * Busca pacientes en tiempo real para el autocompletado en la creación de historias.
     * Uso: Query Builder (Joins) para mayor velocidad en la búsqueda.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarPaciente(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            return response()->json([]);
        }

        $pacientes = HistoriaClinica::buscarPacientes($query, Auth::id());

        return response()->json($pacientes);
    }

    /**
     * Muestra el listado de pacientes con sus historiales clínicos asociados.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $historias = HistoriaClinica::obtenerListado(Auth::id());

        $page = request()->get('page', 1);
        $cantidad = 6;
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $historias->slice(($page - 1) * $cantidad, $cantidad),
            $historias->count(),
            $cantidad,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('historias.index', [
            'historias' => $paginator,
        ]);
    }

    /**
     * Muestra el expediente completo de un paciente.
     * @param User $paciente
     * @return \Illuminate\View\View
     */
    public function show($pacienteId)
    {
        $paciente = $this->obtenerUsuario($pacienteId);
        abort_if(!$paciente, 404);
 
        // Añadir propiedades calculadas al objeto stdClass del paciente (Query Builder no tiene accesores Eloquent)
        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));
        $paciente->primera_cita = Cita::obtenerFechaPrimeraCita($paciente->id);
 
        // Usamos el nuevo método del modelo para un inicio limpio (MVD)
        $historia = HistoriaClinica::iniciarHistoria($paciente->id, Auth::id());
 
        // Conteo granular de sesiones para el modal de resumen
        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, Auth::id());
 
        // Citas realizadas para la linea de evolucion
        $citasPaciente = Cita::obtenerCitasRealizadas($paciente->id, Auth::id());
 
        // Obtener enfermedades vinculadas agrupadas por contexto
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);
 
        // [NUEVO] Secciones personalizadas y plantillas
        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $plantillas = PlantillaSeccion::obtenerPorPsicologo(Auth::id());
 
        return view('historias.show', [
            'paciente' => $paciente,
            'historia' => $historia,
            'citasPaciente' => $citasPaciente,
            'stats' => $stats,
            'enfermedadesVinculadas' => $enfermedadesVinculadas,
            'seccionesPersonalizadas' => $seccionesPersonalizadas,
            'plantillas' => $plantillas,
        ]);
    }
 
    /**
     * Vincula una enfermedad a un contexto específico de la historia clínica.
     */
    public function vincularEnfermedad(Request $request)
    {
        $request->validate([
            'historia_clinica_id' => 'required|exists:historia_clinicas,id',
            'enfermedad_id' => 'required|exists:enfermedades,id',
            'contexto' => 'required|string', // Permite fijos (pers_psq, etc) y dinámicos (seg_ID)
        ]);
 
        $result = HistoriaClinica::vincularEnfermedad($request->historia_clinica_id, $request->enfermedad_id, $request->contexto);
        return response()->json($result);
    }
 
    /**
     * Desvincula una enfermedad de la historia clínica.
     */
    public function desvincularEnfermedad(Request $request)
    {
        $request->validate([
            'link_id' => 'required'
        ]);
 
        HistoriaClinica::desvincularEnfermedad($request->link_id);
 
        return response()->json(['success' => true]);
    }
 
    /**
     * Actualiza la información clínica (Cifrada de forma segura)
     * @param Request $request
     * @param int $pacienteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $pacienteId)
    {
        $paciente = $this->obtenerUsuario($pacienteId);
        abort_if(!$paciente, 404);
        $historia = HistoriaClinica::obtenerPorPaciente($paciente->id);
        abort_if(!$historia, 404);
 
        // [NUEVO] Actualizar segmentos personalizados si vienen en el request
        SegmentoPersonalizado::actualizarSegmentosExtra($request->segmentos_extra);
 
        // [NUEVO] Actualizar metadata de segmentos (títulos)
        SegmentoPersonalizado::actualizarMetadata($request->segmentos_metadata);
 
        return back()->with('success', 'Historia clínica actualizada correctamente.');
    }

    /**
     * Crea una nueva sección personalizada para un historial clínico.
     */
    public function storeSeccion(Request $request, $pacienteId)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion_general' => 'nullable|string|max:255',
            'segmentos_titulos' => 'required|array|min:1',
            'segmentos_titulos.*' => 'required|string|max:255',
        ]);

        $historia = HistoriaClinica::obtenerPorPacienteOrFail($pacienteId);

        SeccionPersonalizada::crear($historia, $request->all());

        return back()->with('success', "Sección \"{$request->titulo}\" creada.");
    }

    public function destroySeccion($seccionId)
    {
        $seccion = SeccionPersonalizada::obtenerPorId($seccionId);
        if (!$seccion) abort(404);

        // Consultar la historia clínica por su ID real
        $historia = HistoriaClinica::obtenerPorId($seccion->historia_clinica_id);

        // Validar que la sección pertenezca a una historia que el psicólogo autenticado gestiona
        if (!$historia || $historia->psicologo_id != Auth::id()) {
            abort(403);
        }

        $titulo = $seccion->titulo;
        SeccionPersonalizada::eliminar($seccionId);

        return back()->with('success', "Sección \"{$titulo}\" eliminada.");
    }

    public function reorderSeccion(Request $request, $seccionId)
    {
        $request->validate([
            'direccion' => 'required|in:up,down',
        ]);

        $seccion = SeccionPersonalizada::obtenerPorId($seccionId);
        if (!$seccion) {
            abort(404);
        }

        $historia = HistoriaClinica::obtenerPorId($seccion->historia_clinica_id);
        if (!$historia || $historia->psicologo_id != Auth::id()) {
            abort(403);
        }

        SeccionPersonalizada::reordenar($seccionId, $request->input('direccion'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Sección reordenada correctamente.');
    }



    /**
     * Crea una nota de evolución manual (sin cita previa programada)
     */
    public function storeEvolucion($pacienteId)
    {
        // Verificar que el paciente pertenezca a este psicólogo
        $historia = HistoriaClinica::verificarAcceso($pacienteId, Auth::id());

        if (!$historia) {
            abort(403);
        }

        $cita = Cita::crearNotaManual($pacienteId, Auth::id());

        return redirect()->route('citas.edit.note', $cita->id)->with('success', 'Nueva nota de sesión creada. Puedes comenzar a redactar.');
    }

    public function downloadZip($pacienteId)
    {
        $userId = Auth::id();
        $paciente = $this->obtenerUsuario($pacienteId);
        abort_if(!$paciente, 404);

        // Validar acceso (solo el psicólogo puede ver sus historias)
        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $citasPaciente = Cita::obtenerCitasRealizadas($paciente->id, $userId);

        $zip = new \ZipArchive();
        $zipFileName = 'Expedientes_' . Str::slug($paciente->name) . '_' . time() . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        if (!file_exists(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            $zip->addFromString('Expediente_General_' . Str::slug($paciente->name) . '.pdf', $this->generateGeneralPdfContent($paciente, $historia));

            foreach ($citasPaciente as $index => $cita) {
                // Numerar las sesiones progresivamente
                $numeroSesion = $citasPaciente->count() - $index;
                $fechaStr = $cita->fecha ? $cita->fecha->format('Y-m-d') : 'SinFecha';
                $fileName = "Sesion_{$numeroSesion}_{$fechaStr}.pdf";
                $zip->addFromString($fileName, $this->generateSesionPdfContent($cita));
            }

            $zip->close();
        } else {
            abort(500, 'No se pudo crear el archivo ZIP.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function generateGeneralPdfContent($paciente, $historia): string
    {
        $headerLines = [
            'Psico-Guia UPTP',
            'Expediente General',
            'Paciente: ' . $paciente->name,
            'Email: ' . $paciente->email,
            'Generado el: ' . now()->format('d/m/Y H:i A'),
            '',
            '=== SECCIONES CLINICAS ===',
            '',
        ];

        $secciones = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        foreach ($secciones as $seccion) {
            $headerLines[] = '=== ' . mb_strtoupper($seccion->titulo) . ' ===';
            if (!empty($seccion->descripcion_general)) {
                $headerLines[] = '(' . $seccion->descripcion_general . ')';
            }
            $headerLines[] = '';

            foreach ($seccion->segmentos as $segmento) {
                $headerLines[] = '- ' . $segmento->titulo . ':';
                $contenido = $segmento->contenido ?: 'Sin registro.';
                $lineasContenido = explode("\n", trim($contenido));
                foreach ($lineasContenido as $linea) {
                    $headerLines[] = '  ' . $linea;
                }
                $headerLines[] = '';
            }
        }

        return $this->buildRawPdf($headerLines);
    }

    private function generateSesionPdfContent($cita): string
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
            'Fecha de sesión: ' . ($cita->fecha ? $cita->fecha->format('d/m/Y') : 'Sin fecha'),
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

        return $this->buildRawPdf($lines);
    }

    private function buildRawPdf(array $lines): string
    {
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
}

