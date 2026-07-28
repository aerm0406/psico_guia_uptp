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
use Barryvdh\DomPDF\Facade\Pdf as PDF;

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

    public function index(Request $request)
    {
        $search = $request->input('search');
        $filters = [
            'pnf' => $request->input('pnf'),
            'edad' => $request->input('edad'),
            'tipo_filtro_fecha' => $request->input('tipo_filtro_fecha', 'rango'),
            'fecha_desde' => $request->input('fecha_desde'),
            'fecha_hasta' => $request->input('fecha_hasta'),
            'enfermedad_id' => $request->input('enfermedad_id'),
            'prioridad' => $request->input('prioridad'),
            'avance_id' => $request->input('avance_id'),
            'estado_animo_id' => $request->input('estado_animo_id')
        ];
        $historias = HistoriaClinica::obtenerListado(Auth::id(), $search, $filters);

        $page = $request->get('page', 1);
        $cantidad = 9;
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $historias->slice(($page - 1) * $cantidad, $cantidad),
            $historias->count(),
            $cantidad,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $enfermedades = \App\Models\Enfermedad::obtenerTodasActivas();
        $avances = \App\Models\AvanceSesion::obtenerPorPsicologo(Auth::id());
        $estadosAnimo = \App\Models\EstadoAnimo::obtenerActivos();
        $prioridades = \App\Models\Prioridad::obtenerParaPsicologo(Auth::id());
        $pnfs = [
            'Administracion' => 'Administración',
            'Mecanica' => 'Mecánica',
            'Mantenimiento' => 'Mantenimiento',
            'Electricidad' => 'Electricidad',
            'Veterinaria' => 'Veterinaria',
            'Informatica' => 'Informática',
            'PDA' => 'PDA',
            'Distribucion_Logistica' => 'Distribución y Logística',
            'Agroalimentacion' => 'Agroalimentación',
            'Seguridad_Alimentaria_Nutricional' => 'Seguridad alimentaria y Cultura Nutricional'
        ];

        // Get the selected enfermedad name if available
        $enfermedadSeleccionada = null;
        if (!empty($filters['enfermedad_id'])) {
            $enfermedadSeleccionada = \App\Models\Enfermedad::obtenerPorId($filters['enfermedad_id']);
        }

        return view('historias.index', [
            'historias' => $paginator,
            'search' => $search,
            'filters' => $filters,
            'enfermedadSeleccionada' => $enfermedadSeleccionada,
            'enfermedades' => $enfermedades,
            'avances' => $avances,
            'estadosAnimo' => $estadosAnimo,
            'prioridades' => $prioridades,
            'pnfs' => $pnfs
        ]);
    }

    /**
     * Exportar el listado a PDF
     */
    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $search = $request->input('search');
        $filters = [
            'pnf' => $request->input('pnf'),
            'edad' => $request->input('edad'),
            'tipo_filtro_fecha' => $request->input('tipo_filtro_fecha', 'rango'),
            'fecha_desde' => $request->input('fecha_desde'),
            'fecha_hasta' => $request->input('fecha_hasta'),
            'enfermedad_id' => $request->input('enfermedad_id'),
            'prioridad' => $request->input('prioridad'),
            'avance_id' => $request->input('avance_id'),
            'estado_animo_id' => $request->input('estado_animo_id')
        ];
        $historias = HistoriaClinica::obtenerListado(Auth::id(), $search, $filters);

        $filterNames = [
            'pnf' => $filters['pnf'],
            'edad' => $filters['edad'],
            'fecha_desde' => $filters['fecha_desde'],
            'fecha_hasta' => $filters['fecha_hasta'],
            'prioridad' => $filters['prioridad'],
            'enfermedad' => null,
            'avance' => null,
            'estado_animo' => null,
        ];

        if (!empty($filters['enfermedad_id'])) {
            $filterNames['enfermedad'] = \App\Models\Enfermedad::obtenerNombrePorId($filters['enfermedad_id']);
        }
        if (!empty($filters['avance_id'])) {
            $filterNames['avance'] = \App\Models\AvanceSesion::obtenerNombrePorId($filters['avance_id']);
        }
        if (!empty($filters['estado_animo_id'])) {
            $filterNames['estado_animo'] = \App\Models\EstadoAnimo::obtenerNombrePorId($filters['estado_animo_id']);
        }

        $pdf = PDF::loadView('historias.listadoPDF', compact('historias', 'search', 'filterNames'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Listado_Pacientes_' . date('Y_m_d') . '.pdf');
    }

    /**
     * Exportar el listado a Excel
     */
    public function exportarExcel(Request $request)
    {
        $search = $request->input('search');
        $filters = [
            'pnf' => $request->input('pnf'),
            'edad' => $request->input('edad'),
            'tipo_filtro_fecha' => $request->input('tipo_filtro_fecha', 'rango'),
            'fecha_desde' => $request->input('fecha_desde'),
            'fecha_hasta' => $request->input('fecha_hasta'),
            'enfermedad_id' => $request->input('enfermedad_id'),
            'prioridad' => $request->input('prioridad'),
            'avance_id' => $request->input('avance_id'),
            'estado_animo_id' => $request->input('estado_animo_id')
        ];
        $filterNames = [
            'pnf' => $filters['pnf'],
            'edad' => $filters['edad'],
            'fecha_desde' => $filters['fecha_desde'],
            'fecha_hasta' => $filters['fecha_hasta'],
            'prioridad' => $filters['prioridad'],
            'enfermedad' => null,
            'avance' => null,
            'estado_animo' => null,
        ];

        if (!empty($filters['enfermedad_id'])) {
            $filterNames['enfermedad'] = \App\Models\Enfermedad::obtenerNombrePorId($filters['enfermedad_id']);
        }
        if (!empty($filters['avance_id'])) {
            $filterNames['avance'] = \App\Models\AvanceSesion::obtenerNombrePorId($filters['avance_id']);
        }
        if (!empty($filters['estado_animo_id'])) {
            $filterNames['estado_animo'] = \App\Models\EstadoAnimo::obtenerNombrePorId($filters['estado_animo_id']);
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\Historias\PacientesExport(Auth::id(), $search, $filters, $filterNames), 'Listado_Pacientes_' . date('Y_m_d') . '.xlsx');
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
 
        // Limpieza automática de notas manuales vacías
        $citasRaw = \App\Models\Cita::obtenerCitasPsicologoPacienteRaw($paciente->id, Auth::id());
        foreach ($citasRaw as $c) {
            $cDecrypted = Cita::obtenerDetalle($c->id);
            if ($cDecrypted && $cDecrypted->motivo === 'Nota de Evolución (Manual)') {
                $isEmpty = true;
                if ($cDecrypted->notas) {
                    $data = json_decode($cDecrypted->notas, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                        $hasContent = false;
                        $fieldsToCheck = ['motivo_consulta', 'observaciones', 'intervenciones', 'plan_tratamiento', 'proxima_cita_razon', 'titulo_manual', 'estado_animo_id', 'avance_estado', 'diagnosticos'];
                        foreach ($fieldsToCheck as $field) {
                            if (!empty($data[$field])) {
                                $hasContent = true;
                                break;
                            }
                        }
                        if ($hasContent) {
                            $isEmpty = false;
                        }
                    } else if (trim($cDecrypted->notas) !== '') {
                        $isEmpty = false;
                    }
                }
                if ($isEmpty) {
                    \App\Models\Cita::eliminarFisicamente($c->id);
                }
            }
        }

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
            'segmentos_titulos' => 'required|array|min:1|max:4',
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

        return redirect()->route('citas.edit.note', $cita->id);
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

    /**
     * Genera y descarga el reporte PDF del expediente general.
     */
    public function reportePdf($pacienteId)
    {
        ini_set('memory_limit', '512M');
        $userId = Auth::id();
        $paciente = $this->obtenerUsuario($pacienteId);
        abort_if(!$paciente, 404);

        // Añadir propiedades calculadas
        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));

        // Validar acceso
        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);

        $pdf = PDF::loadView('historias.reportePDF', compact('paciente', 'historia', 'seccionesPersonalizadas', 'stats', 'enfermedadesVinculadas'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Expediente_General_' . Str::slug($paciente->name) . '.pdf');
    }

    /**
     * Genera y descarga el reporte Word del expediente general.
     */
    public function reporteWord($pacienteId)
    {
        $userId = Auth::id();
        $paciente = $this->obtenerUsuario($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));
        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);

        $tempFile = \App\Exports\Historias\WordExport::generateExpedienteGeneral($paciente, $seccionesPersonalizadas, $enfermedadesVinculadas);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function expedienteCompletoPdf($pacienteId)
    {
        ini_set('memory_limit', '512M');
        $userId = Auth::id();
        $paciente = $this->obtenerUsuario($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));
        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);
        
        $citasSeleccionadas = Cita::obtenerCitasRealizadas($paciente->id, $userId);
        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);

        $pdf = PDF::loadView('historias.expedienteCompletoPDF', compact('paciente', 'historia', 'seccionesPersonalizadas', 'enfermedadesVinculadas', 'citasSeleccionadas', 'stats'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Expediente_Completo_' . Str::slug($paciente->name) . '.pdf');
    }

    public function expedienteCompletoWord($pacienteId)
    {
        $userId = Auth::id();
        $paciente = $this->obtenerUsuario($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));
        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);
        $citasSeleccionadas = Cita::obtenerCitasRealizadas($paciente->id, $userId);
        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);
        
        $psicologo = $this->obtenerUsuario($userId);
        $psicologoName = trim(($psicologo->nombres ?? '') . ' ' . ($psicologo->apellidos ?? ''));

        $tempFile = \App\Exports\Historias\WordExport::generateExpedienteCompleto($paciente, $historia, $seccionesPersonalizadas, $enfermedadesVinculadas, $citasSeleccionadas, $stats, $psicologoName);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    /**
     * Genera y descarga el reporte PDF de las notas de evolución seleccionadas.
     */
    public function evolucionPdf($pacienteId)
    {
        ini_set('memory_limit', '512M');
        $request = request();
        $userId = Auth::id();
        $paciente = $this->obtenerUsuario($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));

        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);
        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $citasIds = $request->input('citas_ids', []);
        $todasLasCitas = Cita::obtenerCitasRealizadas($paciente->id, $userId);

        if (!empty($citasIds)) {
            $citasSeleccionadas = $todasLasCitas->filter(fn($c) => in_array($c->id, $citasIds));
        } else {
            $citasSeleccionadas = $todasLasCitas;
        }

        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);

        $modoDescarga = $request->input('modo_descarga', 'unificado');

        if ($modoDescarga === 'individuales') {
            $zip = new \ZipArchive();
            $zipFileName = 'Evolucion_Individuales_' . Str::slug($paciente->name) . '.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            if (!file_exists(storage_path('app/public'))) {
                mkdir(storage_path('app/public'), 0755, true);
            }

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                $count = 1;
                foreach ($citasSeleccionadas as $cita) {
                    $citasUna = collect([$cita]);
                    $pdf = PDF::loadView('historias.evolucionPDF', ['paciente' => $paciente, 'historia' => $historia, 'citasSeleccionadas' => $citasUna, 'stats' => $stats])->setPaper('a4', 'portrait');
                    $fecha = $cita->fecha ? $cita->fecha->format('Y-m-d') : 'SinFecha';
                    $pdfContent = $pdf->output();
                    $zip->addFromString("Nota_{$count}_{$fecha}.pdf", $pdfContent);
                    $count++;
                }
                $zip->close();
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
            return back()->with('error', 'Error al crear el archivo ZIP.');
        }

        $pdf = PDF::loadView('historias.evolucionPDF', compact('paciente', 'historia', 'citasSeleccionadas', 'stats'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Evolucion_' . Str::slug($paciente->name) . '.pdf');
    }

    /**
     * Genera y descarga el reporte Word de las notas de evolución seleccionadas.
     */
    public function evolucionWord($pacienteId)
    {
        $request = request();
        $userId = Auth::id();
        $paciente = $this->obtenerUsuario($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));

        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);
        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $citasIds = $request->input('citas_ids', []);
        $todasLasCitas = Cita::obtenerCitasRealizadas($paciente->id, $userId);

        if (!empty($citasIds)) {
            $citasSeleccionadas = $todasLasCitas->filter(fn($c) => in_array($c->id, $citasIds));
        } else {
            $citasSeleccionadas = $todasLasCitas;
        }

        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);
        $psicologo = $this->obtenerUsuario($userId);
        $psicologoName = trim(($psicologo->nombres ?? '') . ' ' . ($psicologo->apellidos ?? ''));

        $modoDescarga = $request->input('modo_descarga', 'unificado');

        if (!file_exists(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }

        if ($modoDescarga === 'individuales') {
            $zip = new \ZipArchive();
            $zipFileName = 'Evolucion_Individuales_' . Str::slug($paciente->name) . '.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                $count = 1;
                $tempFiles = [];
                foreach ($citasSeleccionadas as $cita) {
                    $tempDocPath = storage_path('app/public/temp_doc_' . $count . '.docx');
                    \App\Exports\Historias\WordExport::generateEvolucion(collect([$cita]), $paciente, $historia, $stats, $psicologoName, $tempDocPath);
                    $fecha = $cita->fecha ? $cita->fecha->format('Y-m-d') : 'SinFecha';
                    $zip->addFile($tempDocPath, "Nota_{$count}_{$fecha}.docx");
                    $tempFiles[] = $tempDocPath;
                    $count++;
                }
                $zip->close();
                foreach($tempFiles as $f) @unlink($f);
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
            return back()->with('error', 'Error al crear el archivo ZIP.');
        }

        $fileName = 'Evolucion_' . Str::slug($paciente->name) . '.docx';
        $tempPath = storage_path('app/public/' . $fileName);
        
        \App\Exports\Historias\WordExport::generateEvolucion($citasSeleccionadas, $paciente, $historia, $stats, $psicologoName, $tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
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

