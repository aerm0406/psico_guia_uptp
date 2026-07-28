<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\GrupoHorario;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    /**
     * Muestra la vista principal de la agenda.
     * Soporta diferentes vistas (mes, semana, lista) y filtrado por psicólogo para administradores.
     * Coordina la obtención de citas y horarios mediante el modelo.
     */
    public function index(Request $request)
    {
        // 1. Verificación de permisos de acceso
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        if (!$user || ($user->role !== 'psicologo' && $user->role !== 'admin')) {
            abort(403);
        }

        // 2. Obtención delegada de datos (Agenda y Configuración) desde el Modelo
        $data = Cita::obtenerDataAgenda($request, $user);

        $data['avances'] = \Illuminate\Support\Facades\DB::table('avances_sesion')->orderBy('nombre', 'asc')->get();
        $data['estados_animo'] = \Illuminate\Support\Facades\DB::table('estado_animos')->orderBy('valor', 'asc')->get();
        $data['prioridades'] = \App\Models\Prioridad::obtenerParaPsicologo($userId);

        // 3. Retorno de la vista principal con la data procesada
        return view('agenda.index', $data);
    }

    /**
     * Devuelve la vista parcial de la lista de citas pendientes.
     * Se utiliza para actualizaciones dinámicas vía AJAX en el panel lateral de la agenda.
     */
    public function pendingList(Request $request)
    {
        // 1. Determinación del sujeto de consulta (Psicólogo o Admin filtrando)
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        $psicologoId = $userId;
        if ($user && $user->role === 'admin' && $request->has('psicologo_id')) {
            $psicologoId = $request->input('psicologo_id');
        }

        // 2. Extracción de filtros de búsqueda y prioridad
        $prioridadFilter = $request->input('prioridad');
        $q = $request->input('q');

        // 3. Consulta de registros pendientes delegada al modelo
        $citasPendientes = Cita::obtenerPendientes($psicologoId, $prioridadFilter, $q);

        $pacientesSinCita = collect();
        if ($q) {
            $pacientesSinCita = \App\Models\User::obtenerPacientesSinCita($q);
        }

        // 4. Retorno de componente parcial para actualización asíncrona
        return view('agenda.components.pending-list', compact('citasPendientes', 'pacientesSinCita'));
    }

    /**
     * Crea una cita manual (pendiente) para un paciente que no tiene cita en la agenda.
     */
    public function crearCitaManual(Request $request)
    {
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        if (!$user || ($user->role !== 'psicologo' && $user->role !== 'admin')) {
            abort(403);
        }

        $request->validate([
            'paciente_id' => 'required|exists:users,id'
        ]);

        $pacienteId = $request->input('paciente_id');

        // Verificar que el paciente no tenga ya una cita pendiente o confirmada
        $existe = DB::table('citas')->where('user_id', $pacienteId)
                      ->whereIn('estado', ['pendiente', 'confirmada'])
                      ->exists();

        if ($existe) {
            return response()->json(['success' => false, 'message' => 'El paciente ya tiene una cita pendiente o confirmada.']);
        }

        // Obtener paciente
        $paciente = DB::table('users')->where('id', $pacienteId)->where('role', 'paciente')->first();
        if (!$paciente) {
            return response()->json(['success' => false, 'message' => 'Paciente no válido.']);
        }

        $citaId = DB::table('citas')->insertGetId([
            'user_id' => $pacienteId,
            'psicologo_id' => $userId,
            'fecha' => now()->format('Y-m-d'),
            'hora' => null,
            'estado' => 'pendiente',
            'prioridad' => 'media',
            'motivo' => \Illuminate\Support\Facades\Crypt::encryptString('Gestionada por psicólogo'),
            'created_at' => now(),
            'updated_at' => null,
        ]);

        // Crear notificación interna (if notificaciones table exists or notifications)
        DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\NuevaCitaNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $pacienteId,
            'data' => json_encode([
                'type_id' => 'cita_requested',
                'body' => 'El psicólogo ha abierto una nueva solicitud de cita para ti en la plataforma.',
                'url' => route('citas.index'),
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Enviar correo
        $psicologo = DB::table('users')->where('id', $userId)->first();
        $citaData = (object)[
            'id' => $citaId,
            'paciente' => $paciente
        ];
        
        try {
            \Illuminate\Support\Facades\Mail::to($paciente->email)
                ->queue(new \App\Mail\CitaAsignadaManualMail($citaData, $psicologo));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de cita manual: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Paciente agregado a la lista de pendientes.']);
    }

    /**
     * Devuelve una respuesta JSON con las citas para un día específico.
     * Se utiliza principalmente en la vista mensual para mostrar el detalle diario al hacer clic en un día.
     */
    public function dailyCitas(Request $request)
    {
        // 1. Preparación de parámetros de búsqueda
        $psicologoId = $request->input('psicologo_id', Auth::id());
        $fecha = $request->input('fecha');

        // 2. Retorno de respuesta JSON delegada al modelo (Estándar MVC)
        return response()->json(Cita::obtenerCitasDiariasJson($psicologoId, $fecha));
    }

    /**
     * Exportar la agenda de la semana actual a PDF.
     */
    public function estadisticas(Request $request)
    {
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        
        if (!$user || ($user->role !== 'psicologo' && $user->role !== 'admin')) {
            abort(403);
        }

        $psicologoId = $request->input('psicologo_id', $userId);
        if ($user->role === 'psicologo' && $user->id != $psicologoId) {
            abort(403);
        }

        $fechaInicio = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $fechaFin = $request->input('end_date', Carbon::now()->toDateString());
        $estado = $request->input('estado');
        $avanceId = $request->input('avance_id');
        $estadoAnimoId = $request->input('estado_animo_id');
        $prioridad = $request->input('prioridad');
        $perfilAcademico = $request->input('perfil_academico');
        $pnf = $request->input('pnf');
        $format = $request->input('format', 'pdf');
        $reportType = $request->input('report_type', 'completo');

        $citas = Cita::obtenerEstadisticas($psicologoId, $fechaInicio, $fechaFin, $estado, $avanceId, $estadoAnimoId, $prioridad, $perfilAcademico, $pnf);
        $resumen = Cita::obtenerResumenEstadistico($citas, $fechaInicio, $fechaFin, $psicologoId);
        $psicologo = DB::table('users')->where('id', $psicologoId)->first();
        
        $avanceNombre = null;
        if ($avanceId) {
            $avance = DB::table('avances_sesion')->where('id', $avanceId)->first();
            $avanceNombre = $avance ? $avance->nombre : null;
        }

        $estadoAnimoNombre = null;
        if ($estadoAnimoId) {
            $animo = DB::table('estado_animos')->where('id', $estadoAnimoId)->first();
            $estadoAnimoNombre = $animo ? $animo->valor . ' - ' . $animo->nombre : null;
        }

        if ($format === 'json') {
            return response()->json([
                'citas' => $citas,
                'resumen' => $resumen,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'psicologo' => $psicologo
            ]);
        }

        if ($format === 'html') {
            return view('agenda.estadisticas-dashboard', [
                'psicologoId' => $psicologoId,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'avances' => DB::table('avances_sesion')->orderBy('nombre', 'asc')->where('status', 1)->get(),
                'estados_animo' => DB::table('estado_animos')->orderBy('valor', 'asc')->where('status', 1)->get(),
                'prioridades' => \App\Models\Prioridad::obtenerParaPsicologo($psicologoId)
            ]);
        }

        if ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Agenda\EstadisticasExport($citas, $fechaInicio, $fechaFin, $estado, $psicologo, $avanceNombre, $resumen, $estadoAnimoNombre, $prioridad),
                'estadisticas_citas.xlsx'
            );
        }
        
        if ($format === 'word') {
            $periodo = $request->input('periodo', 'mensual');
            $tempFile = \App\Exports\Agenda\EstadisticasWordExport::generate($citas, $resumen, $fechaInicio, $fechaFin, $estado, $avanceNombre, $estadoAnimoNombre, $prioridad, $psicologo, $periodo);
            return response()->download($tempFile)->deleteFileAfterSend(true);
        }

        ini_set('memory_limit', '512M');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('agenda.estadisticas-pdf', [
            'citas' => $citas,
            'resumen' => $resumen,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'estado' => $estado,
            'avance_id' => $avanceId,
            'avance_nombre' => $avanceNombre,
            'estado_animo_id' => $estadoAnimoId,
            'estado_animo_nombre' => $estadoAnimoNombre,
            'prioridad' => $prioridad,
            'perfil_academico' => $perfilAcademico,
            'pnf' => $pnf,
            'psicologo' => $psicologo,
            'periodo' => $request->input('periodo', 'mensual'),
            'reportType' => $reportType
        ]);

        return $pdf->stream('estadisticas_citas.pdf');
    }

    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        
        if (!$user || ($user->role !== 'psicologo' && $user->role !== 'admin')) {
            abort(403);
        }

        $psicologoId = $userId;
        $psicologo = $user;
        
        if ($user->role === 'admin' && $request->has('psicologo_id')) {
            $psicologoId = $request->input('psicologo_id');
            // Validar que el usuario solicitado sea psicólogo usando Query Builder
            $psicologo = \Illuminate\Support\Facades\DB::table('users')
                            ->where('id', $psicologoId)
                            ->where('role', 'psicologo')
                            ->first();
                            
            if (!$psicologo) {
                abort(404, 'Psicólogo no encontrado');
            }
        } elseif ($user->role !== 'psicologo') {
            abort(403, 'Solo los psicólogos pueden exportar su agenda.');
        }

        // Determinar el rango de la semana según la fecha seleccionada o la actual
        $viewType = $request->input('view', 'week');
        $dateStr = $request->input('date');
        $baseDate = $dateStr ? Carbon::parse($dateStr) : Carbon::now();
        
        $numSemanas = ($viewType === 'month') ? 4 : 1;
        $semanasInfo = [];

        for ($i = 0; $i < $numSemanas; $i++) {
            $currentDate = $baseDate->copy()->addWeeks($i);
            $inicioSemana = $currentDate->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            $finSemana = $currentDate->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

            // Consultar citas confirmadas con Query Builder para la semana específica
            $citas = \Illuminate\Support\Facades\DB::table('citas')
                ->select('citas.*', 'users.nombres', 'users.apellidos')
                ->leftJoin('users', 'citas.user_id', '=', 'users.id')
                ->where('citas.psicologo_id', $psicologoId)
                ->where('citas.estado', 'confirmada')
                ->whereBetween('citas.fecha', [$inicioSemana, $finSemana])
                ->get();

            // Formatear fechas para compatibilidad con la vista
            $citasCalendario = $citas->map(function($cita) {
                $cita->paciente_nombre = trim(($cita->nombres ?? '') . ' ' . ($cita->apellidos ?? ''));
                
                $pNombre = explode(' ', trim($cita->nombres ?? ''))[0];
                $pApellido = explode(' ', trim($cita->apellidos ?? ''))[0];
                $cita->paciente_short_name = trim($pNombre . ' ' . $pApellido) ?: 'Paciente';
                
                $cita->fecha = Carbon::parse($cita->fecha);
                return $cita;
            });

            $semanasInfo[] = [
                'currentDate' => $currentDate,
                'citasCalendario' => $citasCalendario,
            ];
        }

        $dias = Horario::diasSemana();
        
        $data = [
            'psicologo' => $psicologo,
            'dias' => $dias,
            'semanasInfo' => $semanasInfo,
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('agenda.pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Agenda_Semanal_' . \Illuminate\Support\Str::slug($psicologo->name ?? 'Psicologo') . '.pdf');
    }
}
