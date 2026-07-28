<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\HistoriaController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\GrupoHorarioController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\PlantillaGlobalController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\PublicacionReaccionController;
use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;

// ==========================================
// MODO DE PRUEBAS DE TIEMPO (BORRAR O COMENTAR AL TERMINAR)
// \Carbon\Carbon::setTestNow(\Carbon\Carbon::create(2026, 7, 9, 12, 0, 0, 'America/Caracas'));
// ==========================================

Route::get('/', function () {
    return view('welcome');
});

Route::get('/run-migrations-now', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return "Migraciones ejecutadas correctamente en el servidor.";
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/media/profile-photos/{filename}', [\App\Http\Controllers\MediaController::class, 'showProfilePhoto'])->name('media.profile_photos');
    Route::get('/media/publicaciones/{filename}', [\App\Http\Controllers\MediaController::class, 'showPublicacionMedia'])->name('media.publicaciones');

    Route::get('/dashboard', function () {
        $user = Auth::user();

        // 1. Dashboard de Administrador
        if ($user?->role === 'admin') {
            $totalUsuarios = \App\Models\User::contarUsuarios();
            $totalPacientes = \App\Models\User::contarUsuarios('paciente');
            $totalPsicologos = \App\Models\User::contarUsuarios('psicologo');
            $totalAdmins = \App\Models\User::contarUsuarios('admin');
            $totalCitas = \App\Models\Cita::contarCitas();
            $citasHoy = \App\Models\Cita::contarCitasHoy();

            return view('dashboard_admin', compact(
                'totalUsuarios',
                'totalPacientes',
                'totalPsicologos',
                'totalAdmins',
                'totalCitas',
                'citasHoy'
            ));
        }

        // 2. Dashboard de Paciente
        if ($user?->role === 'paciente') {
            $estadoAnimoHoy = \App\Models\EstadoAnimoDiario::getTodayForUser(Auth::id());
            
            // Lógica del Saludo
            $hora = (int) now()->format('H');
            if ($hora >= 5 && $hora < 12) {
                $saludo = 'Buenos días';
            } elseif ($hora >= 12 && $hora < 19) {
                $saludo = 'Buenas tardes';
            } else {
                $saludo = 'Buenas noches';
            }

            // Consultas Query Builder para Citas
            $proximaCita = \Illuminate\Support\Facades\DB::table('citas')
                ->join('users', 'citas.psicologo_id', '=', 'users.id')
                ->select('citas.*', 'users.nombres as psi_nombres', 'users.apellidos as psi_apellidos')
                ->where('citas.user_id', Auth::id())
                ->where('citas.estado', 'confirmada')
                ->where('citas.fecha', '>=', now()->format('Y-m-d'))
                ->orderBy('citas.fecha', 'asc')
                ->orderBy('citas.hora', 'asc')
                ->first();

            if ($proximaCita) {
                $fechaSoloP = \Carbon\Carbon::parse($proximaCita->fecha)->format('Y-m-d');
                $fechaHoraP = \Carbon\Carbon::parse($fechaSoloP . ' ' . ($proximaCita->hora ?? '00:00:00'));
                if ($fechaHoraP->isPast()) {
                    $proximaCita = null;
                } else {
                    $firstName = explode(' ', trim($proximaCita->psi_nombres ?? ''))[0] ?? '';
                    $firstLastName = explode(' ', trim($proximaCita->psi_apellidos ?? ''))[0] ?? '';
                    $proximaCita->psicologo_nombre = trim($firstName . ' ' . $firstLastName) ?: 'Psicólogo';
                }
            }

            $citaPendiente = \Illuminate\Support\Facades\DB::table('citas')
                ->join('users', 'citas.psicologo_id', '=', 'users.id')
                ->select('citas.*', 'users.nombres as psi_nombres', 'users.apellidos as psi_apellidos')
                ->where('citas.user_id', Auth::id())
                ->where('citas.estado', 'pendiente')
                ->orderBy('citas.created_at', 'desc')
                ->first();

            if ($citaPendiente) {
                $firstName = explode(' ', trim($citaPendiente->psi_nombres ?? ''))[0] ?? '';
                $firstLastName = explode(' ', trim($citaPendiente->psi_apellidos ?? ''))[0] ?? '';
                $citaPendiente->psicologo_nombre = trim($firstName . ' ' . $firstLastName) ?: 'Psicólogo';
            }

            $publicaciones = \Illuminate\Support\Facades\DB::table('publicaciones')
                ->where('estatus', 1) // Activa
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            $notificacionCita = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('notifiable_id', Auth::id())
                ->where('notifiable_type', 'App\Models\User')
                ->whereNull('read_at')
                ->whereIn('type', [
                    'App\Notifications\CitaConfirmedNotification', 
                    'App\Notifications\CitaRechazadaNotification',
                    'App\Notifications\CitaCancelledNotification'
                ])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($notificacionCita) {
                $data = json_decode($notificacionCita->data, true);
                if (isset($data['type_id']) && $data['type_id'] === 'cita_postponed') {
                    $notificacionCita = null;
                } else {
                    $citaId = $data['cita_id'] ?? null;
                    if ($citaId) {
                        $citaNotif = \Illuminate\Support\Facades\DB::table('citas')->where('id', $citaId)->first();
                        if ($citaNotif) {
                            if ($citaNotif->fecha && $citaNotif->hora) {
                                $fechaSoloN = \Carbon\Carbon::parse($citaNotif->fecha)->format('Y-m-d');
                                $fechaHoraN = \Carbon\Carbon::parse($fechaSoloN . ' ' . $citaNotif->hora);
                                if ($fechaHoraN->isPast()) {
                                    $notificacionCita = null;
                                }
                            }
                        } else {
                            $notificacionCita = null;
                        }
                    }
                }
            }

            if (!$notificacionCita && $proximaCita) {
                $notificacionCita = (object)[
                    'type' => 'App\Notifications\CitaConfirmedNotification',
                    'data' => json_encode([
                        'cita_id' => $proximaCita->id,
                        'body' => '¡Tienes una cita confirmada!'
                    ])
                ];
            }

            return view('dashboard_paciente', compact('estadoAnimoHoy', 'saludo', 'proximaCita', 'citaPendiente', 'publicaciones', 'notificacionCita'));
        }

        // 3. Dashboard de Psicólogo (Default / Else)
        $diaActual = Carbon::now()->dayOfWeek; // 0=Domingo, 1=Lunes, etc.
        $grupoActivo = \App\Models\GrupoHorario::obtenerActivoPorPsicologo(Auth::id());

        $horariosHoy = collect();
        if ($grupoActivo) {
            $horariosHoy = \App\Models\GrupoHorario::obtenerHorariosHoy($grupoActivo->id, $diaActual);
        }

        $confirmadasHoy = \App\Models\Cita::obtenerCitasConfirmadasHoyPorPsicologo(Auth::id(), 3);
        
        // Nuevas variables para el dashboard interactivo
        $ultimasConfirmadas = \App\Models\Cita::obtenerUltimasCitasConfirmadasPsicologo(Auth::id(), 5);
        $estadisticasCitas = \App\Models\Cita::obtenerEstadisticasCitasPsicologo(Auth::id());
        $tendenciaPacientes = \App\Models\Cita::obtenerTendenciaSemanalCitasPsicologo(Auth::id(), 4);
        $citasPendientesAntiguas = \App\Models\Cita::obtenerCitasPendientesAntiguasPsicologo(Auth::id(), 5);

        return view('dashboard', compact(
            'horariosHoy', 
            'confirmadasHoy', 
            'ultimasConfirmadas', 
            'estadisticasCitas', 
            'tendenciaPacientes', 
            'citasPendientesAntiguas'
        ));
    })->name('dashboard');

    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':psicologo'])->group(function () {
        Route::resource('publicaciones', \App\Http\Controllers\PublicacionController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
        Route::get('/agenda/pending-list', [AgendaController::class, 'pendingList'])->name('agenda.pending.list');
        Route::post('/agenda/crear-cita-manual', [AgendaController::class, 'crearCitaManual'])->name('agenda.crear_cita_manual');
        Route::get('/agenda/daily-citas', [AgendaController::class, 'dailyCitas'])->name('agenda.daily_citas');
        Route::get('/agenda/exportar-pdf', [AgendaController::class, 'exportarPdf'])->name('agenda.exportarPdf');
        Route::get('/agenda/estadisticas', [AgendaController::class, 'estadisticas'])->name('agenda.estadisticas');
        Route::resource('agenda/prioridades', \App\Http\Controllers\PrioridadController::class)
            ->names('agenda.prioridades')
            ->except(['show', 'edit', 'update']);
        Route::resource('agenda/estado-animos', \App\Http\Controllers\EstadoAnimoController::class)
            ->names('agenda.estado_animos')
            ->except(['show']);
        Route::get('/historias', [HistoriaController::class, 'index'])->name('historias.index');
        Route::get('/historias/{paciente}', [HistoriaController::class, 'show'])->name('historias.show');
        Route::get('/historias/{paciente}/download-zip', [HistoriaController::class, 'downloadZip'])->name('historias.downloadZip');

        Route::get('/horarios/exportar-pdf', [HorarioController::class, 'exportarPdf'])->name('horarios.exportarPdf');
        Route::resource('horarios', HorarioController::class);
        Route::patch('horarios/{horario}/activate', [HorarioController::class, 'activate'])->name('horarios.activate');
        Route::patch('horarios/{horario}/deactivate', [HorarioController::class, 'deactivate'])->name('horarios.deactivate');

        Route::resource('grupos_horarios', GrupoHorarioController::class);
        Route::post('grupos_horarios/store-from-horarios', [GrupoHorarioController::class, 'storeFromHorarios'])->name('grupos_horarios.store_from_horarios');
        Route::patch('grupos_horarios/{id}/activate', [GrupoHorarioController::class, 'activate'])->name('grupos_horarios.activate');
        Route::patch('grupos_horarios/{id}/deactivate', [GrupoHorarioController::class, 'deactivate'])->name('grupos_horarios.deactivate');

        // Módulo de Publicaciones (Mural de Avisos)
        Route::get('/publicaciones', [PublicacionController::class, 'index'])->name('publicaciones.index');
        Route::get('/publicaciones/create', [PublicacionController::class, 'create'])->name('publicaciones.create');
        Route::post('/publicaciones', [PublicacionController::class, 'store'])->name('publicaciones.store');
        Route::get('/publicaciones/{id}/edit', [PublicacionController::class, 'edit'])->name('publicaciones.edit');
        Route::put('/publicaciones/{id}', [PublicacionController::class, 'update'])->name('publicaciones.update');
        Route::delete('/publicaciones/{id}', [PublicacionController::class, 'destroy'])->name('publicaciones.destroy');



        Route::patch('/citas/{cita}/cancelar-psicologo', [CitaController::class, 'cancelConfirmedByPsicologo'])->name('citas.cancel.psicologo');
        Route::resource('enfermedades', \App\Http\Controllers\EnfermedadController::class)->parameters([
            'enfermedades' => 'enfermedad'
        ]);
    });

    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':paciente'])->group(function () {
        Route::get('/mural', [\App\Http\Controllers\PublicacionController::class, 'mural'])->name('mural.index');
        Route::post('/publicaciones/{id}/reaccionar', [\App\Http\Controllers\PublicacionReaccionController::class, 'toggle'])->name('publicaciones.reaccionar');
        Route::get('/citas', [CitaController::class, 'index'])->name('citas.index');
        Route::get('/citas/create', [CitaController::class, 'create'])->name('citas.create');
        Route::get('/citas/available-slots', [CitaController::class, 'getAvailableSlots'])->name('citas.available_slots');
        Route::get('/citas/historial-json', [CitaController::class, 'historyJson'])->name('citas.history.json');
        Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
        Route::patch('/citas/{cita}/cancel', [CitaController::class, 'cancel'])->name('citas.cancel');
        
        Route::post('/estado-animo-diario', [\App\Http\Controllers\EstadoAnimoDiarioController::class, 'store'])->name('estado_animo_diario.store');
    });

    Route::get('/citas/{cita}/json', [CitaController::class, 'showJson'])->name('citas.show.json');
    Route::patch('/citas/{cita}/prioridad', [CitaController::class, 'updatePriority'])->name('citas.update.prioridad');
    Route::get('/citas/{cita}/alerta-prioridad', [\App\Http\Controllers\CitaController::class, 'alertaPrioridadPsicologo'])->name('citas.alerta_prioridad');
    Route::post('/citas/{cita}/alerta-prioridad-update', [\App\Http\Controllers\CitaController::class, 'updateAlertaPrioridadPsicologo'])->name('citas.update_alerta_prioridad');

    Route::patch('/citas/{cita}/rechazar', [\App\Http\Controllers\CitaController::class, 'reject'])->name('citas.reject');
    Route::patch('/citas/{cita}/proponer', [CitaController::class, 'proponer'])->name('citas.proponer');
    Route::patch('/citas/{cita}/quitar-propuesta', [CitaController::class, 'quitarPropuesta'])->name('citas.quitar_propuesta');
    Route::patch('/citas/{cita}/enviar-propuesta', [CitaController::class, 'enviarPropuesta'])->name('citas.enviar_propuesta');
    Route::patch('/citas/{cita}/responder-propuesta', [CitaController::class, 'responderPropuesta'])->name('citas.responder_propuesta');
    Route::patch('/citas/{cita}/aceptar', [CitaController::class, 'accept'])->name('citas.accept');
    Route::patch('/citas/{cita}/posponer', [CitaController::class, 'posponer'])->name('citas.posponer');
    Route::patch('/citas/{cita}/realizar', [CitaController::class, 'complete'])->name('citas.realizar');
    Route::patch('/citas/{cita}/no-asistio', [CitaController::class, 'noAsistio'])->name('citas.no_asistio');
    Route::patch('/citas/{cita}/dismiss-cancel', [CitaController::class, 'dismissCancelMessage'])->name('citas.dismiss_cancel');
    Route::get('/citas/{cita}/editar-nota', [CitaController::class, 'editNote'])->name('citas.edit.note');
    Route::get('/citas/{cita}/descargar-pdf', [CitaController::class, 'downloadPdf'])->name('citas.download.pdf');
    Route::patch('/citas/{cita}/notas', [CitaController::class, 'updateNote'])->name('citas.update.notas');
    Route::post('/citas/campos-ajax', [CitaController::class, 'storeCampoAjax'])->name('campos.store.ajax');

    // Mensajería Completa
    Route::get('/mensajes', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/mensajes/contactos/lista', [\App\Http\Controllers\ChatController::class, 'fetchContacts'])->name('chat.contacts');
    Route::post('/mensajes/ping', [\App\Http\Controllers\ChatController::class, 'ping'])->name('chat.ping');
    Route::get('/mensajes/{user}', [\App\Http\Controllers\ChatController::class, 'fetchMessages'])->name('chat.fetch');
    Route::post('/mensajes/{user}', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.store');

    // Historias Clínicas
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':psicologo'])->group(function () {
        Route::get('/historias/buscar/paciente', [\App\Http\Controllers\HistoriaController::class, 'buscarPaciente'])->name('historias.buscar');
        Route::get('/historias/exportar/pdf', [\App\Http\Controllers\HistoriaController::class, 'exportarPdf'])->name('historias.exportar.pdf');
        Route::get('/historias/exportar/excel', [\App\Http\Controllers\HistoriaController::class, 'exportarExcel'])->name('historias.exportar.excel');
        Route::patch('/historias/{paciente}', [\App\Http\Controllers\HistoriaController::class, 'update'])->name('historias.update');
        Route::get('/historias/{paciente}/reporte-pdf', [\App\Http\Controllers\HistoriaController::class, 'reportePdf'])->name('historias.reportePdf');
        Route::get('/historias/{paciente}/reporte-word', [\App\Http\Controllers\HistoriaController::class, 'reporteWord'])->name('historias.reporteWord');
        Route::get('/historias/{paciente}/expediente-completo-pdf', [\App\Http\Controllers\HistoriaController::class, 'expedienteCompletoPdf'])->name('historias.expedienteCompletoPdf');
        Route::get('/historias/{paciente}/expediente-completo-word', [\App\Http\Controllers\HistoriaController::class, 'expedienteCompletoWord'])->name('historias.expedienteCompletoWord');
        Route::post('/historias/enfermedad/vincular', [\App\Http\Controllers\HistoriaController::class, 'vincularEnfermedad'])->name('historias.enfermedad.vincular');
        Route::delete('/historias/enfermedad/desvincular', [\App\Http\Controllers\HistoriaController::class, 'desvincularEnfermedad'])->name('historias.enfermedad.desvincular');
        Route::get('/enfermedades/api/search', [\App\Http\Controllers\EnfermedadController::class, 'search'])->name('enfermedades.api.search');

        // [NUEVO] Secciones Dinámicas
        Route::resource('plantillas', \App\Http\Controllers\PlantillaSeccionController::class)->parameters([
            'plantillas' => 'plantilla'
        ]);
        
        // [NUEVO] Campos de Evolución
        Route::resource('campos-evolucion', \App\Http\Controllers\NotaEvolucionCampoController::class)->parameters([
            'campos-evolucion' => 'campo'
        ]);
        Route::get('plantillas-globales', [PlantillaGlobalController::class, 'index'])->name('plantillas-globales.index');
        Route::post('plantillas-globales', [PlantillaGlobalController::class, 'update'])->name('plantillas-globales.update');
        Route::post('/plantillas-globales/aplicar', [PlantillaGlobalController::class, 'apply'])
            ->name('plantillas-globales.apply');
        Route::post('/historias/{paciente}/secciones', [\App\Http\Controllers\HistoriaController::class, 'storeSeccion'])->name('historias.secciones.store');
        Route::delete('/historias/secciones/{seccion}', [\App\Http\Controllers\HistoriaController::class, 'destroySeccion'])->name('historias.secciones.destroy');
        Route::patch('/historias/secciones/{seccion}/reorder', [\App\Http\Controllers\HistoriaController::class, 'reorderSeccion'])->name('historias.secciones.reorder');
        Route::post('/historias/{paciente}/evolucion', [\App\Http\Controllers\HistoriaController::class, 'storeEvolucion'])->name('historias.evolucion.store');
        Route::post('/historias/{paciente}/evolucion-pdf', [\App\Http\Controllers\HistoriaController::class, 'evolucionPdf'])->name('historias.evolucion.pdf');
        Route::post('/historias/{paciente}/evolucion-word', [\App\Http\Controllers\HistoriaController::class, 'evolucionWord'])->name('historias.evolucion.word');
        Route::get('/citas/{cita}/constancia-pdf', [\App\Http\Controllers\CitaController::class, 'descargarConstanciaPdf'])->name('citas.constancia.pdf');
        Route::delete('/citas/{cita}', [\App\Http\Controllers\CitaController::class, 'destroy'])->name('citas.destroy');
    });

    // Avances de sesión (Psicólogo y Admin)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':psicologo,admin'])->group(function () {
        Route::resource('avances_sesion', \App\Http\Controllers\AvanceSesionController::class)
            ->except(['show']);
    });

    // Notificaciones
    Route::get('/notificaciones/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notificaciones/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    Route::get('/profile/horario', [ProfileController::class, 'downloadHorario'])->name('profile.horario.download');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Completar perfil (primer inicio de sesión - solo pacientes)
    Route::get('/completar-perfil', [\App\Http\Controllers\ProfileCompleteController::class, 'show'])->name('profile.complete');
    Route::post('/completar-perfil', [\App\Http\Controllers\ProfileCompleteController::class, 'store'])->name('profile.complete.store');
});

// Rutas de Administración
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users/{id}/password', [\App\Http\Controllers\Admin\UserController::class, 'editPassword'])->name('users.password.edit');
    Route::patch('users/{id}/password', [\App\Http\Controllers\Admin\UserController::class, 'updatePassword'])->name('users.password.update');
    Route::patch('users/{id}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
    Route::delete('users/{id}/reject', [\App\Http\Controllers\Admin\UserController::class, 'reject'])->name('users.reject');
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
});

require __DIR__ . '/auth.php';
