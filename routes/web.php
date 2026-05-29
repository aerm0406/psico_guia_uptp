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
use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
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
                'totalUsuarios', 'totalPacientes', 'totalPsicologos', 'totalAdmins', 'totalCitas', 'citasHoy'
            ));
        }

        // 2. Dashboard de Paciente
        if ($user?->role === 'paciente') {
            return view('dashboard_paciente');
        }

        // 3. Dashboard de Psicólogo (Default / Else)
        $diaActual = Carbon::now()->dayOfWeek; // 0=Domingo, 1=Lunes, etc.
        $grupoActivo = \App\Models\GrupoHorario::obtenerActivoPorPsicologo(Auth::id());

        $horariosHoy = collect();
        if ($grupoActivo) {
            $horariosHoy = \App\Models\GrupoHorario::obtenerHorariosHoy($grupoActivo->id, $diaActual);
        }

        $confirmadasHoy = \App\Models\Cita::obtenerCitasConfirmadasHoyPorPsicologo(Auth::id(), 2);

        return view('dashboard', compact('horariosHoy', 'confirmadasHoy'));
    })->name('dashboard');

    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':psicologo'])->group(function () {
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
        Route::get('/agenda/pending-list', [AgendaController::class, 'pendingList'])->name('agenda.pending.list');
        Route::get('/agenda/daily-citas', [AgendaController::class, 'dailyCitas'])->name('agenda.daily_citas');
        Route::get('/historias', [HistoriaController::class, 'index'])->name('historias.index');
        Route::get('/historias/{paciente}', [HistoriaController::class, 'show'])->name('historias.show');

        Route::resource('horarios', HorarioController::class);
        Route::patch('horarios/{horario}/activate', [HorarioController::class, 'activate'])->name('horarios.activate');
        Route::patch('horarios/{horario}/deactivate', [HorarioController::class, 'deactivate'])->name('horarios.deactivate');

        Route::resource('grupos_horarios', GrupoHorarioController::class);
        Route::post('grupos_horarios/store-from-horarios', [GrupoHorarioController::class, 'storeFromHorarios'])->name('grupos_horarios.store_from_horarios');
        Route::patch('grupos_horarios/{id}/activate', [GrupoHorarioController::class, 'activate'])->name('grupos_horarios.activate');
        Route::patch('grupos_horarios/{id}/deactivate', [GrupoHorarioController::class, 'deactivate'])->name('grupos_horarios.deactivate');

        // Rutas de pacientes - Estructura CRUD estándar
        Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
        Route::get('/pacientes/create', [PacienteController::class, 'create'])->name('pacientes.create');
        Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
        Route::get('/pacientes/{id}/edit', [PacienteController::class, 'edit'])->name('pacientes.edit');
        Route::post('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');
        Route::delete('/pacientes/{id}', [PacienteController::class, 'destroy'])->name('pacientes.destroy');
        Route::get('/pacientes/{id}', [PacienteController::class, 'show'])->name('pacientes.show');
        Route::get('/pacientes/{id}/json', [PacienteController::class, 'showJson'])->name('pacientes.show.json');
        Route::get('pacientes/registrados', [PacienteController::class, 'registrados'])->name('pacientes.registrados');

        Route::patch('/citas/{cita}/cancelar-psicologo', [CitaController::class, 'cancelConfirmedByPsicologo'])->name('citas.cancel.psicologo');
        Route::resource('enfermedades', \App\Http\Controllers\EnfermedadController::class)->parameters([
            'enfermedades' => 'enfermedad'
        ]);
    });

    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':paciente'])->group(function () {
        Route::get('/citas', [CitaController::class, 'index'])->name('citas.index');
        Route::get('/citas/create', [CitaController::class, 'create'])->name('citas.create');
        Route::get('/citas/historial-json', [CitaController::class, 'historyJson'])->name('citas.history.json');
        Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
        Route::patch('/citas/{cita}/cancel', [CitaController::class, 'cancel'])->name('citas.cancel');
    });

    Route::get('/citas/{cita}/json', [CitaController::class, 'showJson'])->name('citas.show.json');
    Route::patch('/citas/{cita}/prioridad', [CitaController::class, 'updatePriority'])->name('citas.update.prioridad');
    Route::get('/citas/{cita}/alerta-prioridad', [\App\Http\Controllers\CitaController::class, 'alertaPrioridadPsicologo'])->name('citas.alerta_prioridad');
    Route::post('/citas/{cita}/alerta-prioridad-update', [\App\Http\Controllers\CitaController::class, 'updateAlertaPrioridadPsicologo'])->name('citas.update_alerta_prioridad');

    Route::patch('/citas/{cita}/rechazar', [\App\Http\Controllers\CitaController::class, 'reject'])->name('citas.reject');
    Route::patch('/citas/{cita}/proponer', [CitaController::class, 'proponer'])->name('citas.proponer');
    Route::patch('/citas/{cita}/quitar-propuesta', [CitaController::class, 'quitarPropuesta'])->name('citas.quitar_propuesta');
    Route::patch('/citas/{cita}/aceptar', [CitaController::class, 'accept'])->name('citas.accept');
    Route::patch('/citas/{cita}/realizar', [CitaController::class, 'complete'])->name('citas.realizar');
    Route::patch('/citas/{cita}/no-asistio', [CitaController::class, 'noAsistio'])->name('citas.no_asistio');
    Route::get('/citas/{cita}/editar-nota', [CitaController::class, 'editNote'])->name('citas.edit.note');
    Route::get('/citas/{cita}/descargar-pdf', [CitaController::class, 'downloadPdf'])->name('citas.download.pdf');
    Route::patch('/citas/{cita}/notas', [CitaController::class, 'updateNote'])->name('citas.update.notas');

    // Mensajería Completa
    Route::get('/mensajes', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/mensajes/contactos/lista', [\App\Http\Controllers\ChatController::class, 'fetchContacts'])->name('chat.contacts');
    Route::get('/mensajes/{user}', [\App\Http\Controllers\ChatController::class, 'fetchMessages'])->name('chat.fetch');
    Route::post('/mensajes/{user}', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.store');

    // Historias Clínicas
    Route::get('/historias', [\App\Http\Controllers\HistoriaController::class, 'index'])->name('historias.index');
    Route::get('/historias/buscar/paciente', [\App\Http\Controllers\HistoriaController::class, 'buscarPaciente'])->name('historias.buscar');
    Route::get('/historias/{paciente}', [\App\Http\Controllers\HistoriaController::class, 'show'])->name('historias.show');
    Route::get('/historias/{paciente}/descargar-zip', [\App\Http\Controllers\HistoriaController::class, 'downloadZip'])->name('historias.downloadZip');
    Route::patch('/historias/{paciente}', [\App\Http\Controllers\HistoriaController::class, 'update'])->name('historias.update');
    Route::post('/historias/enfermedad/vincular', [\App\Http\Controllers\HistoriaController::class, 'vincularEnfermedad'])->name('historias.enfermedad.vincular');
    Route::delete('/historias/enfermedad/desvincular', [\App\Http\Controllers\HistoriaController::class, 'desvincularEnfermedad'])->name('historias.enfermedad.desvincular');
    Route::get('/enfermedades/api/search', [\App\Http\Controllers\EnfermedadController::class, 'search'])->name('enfermedades.api.search');
    // [NUEVO] Secciones Dinámicas
    Route::resource('plantillas', \App\Http\Controllers\PlantillaSeccionController::class)->parameters([
        'plantillas' => 'plantilla'
    ]);
    Route::post('/historias/{paciente}/secciones', [\App\Http\Controllers\HistoriaController::class, 'storeSeccion'])->name('historias.secciones.store');
    Route::delete('/historias/secciones/{seccion}', [\App\Http\Controllers\HistoriaController::class, 'destroySeccion'])->name('historias.secciones.destroy');
    Route::patch('/historias/secciones/{seccion}/reorder', [\App\Http\Controllers\HistoriaController::class, 'reorderSeccion'])->name('historias.secciones.reorder');
    Route::post('/historias/{paciente}/evolucion', [\App\Http\Controllers\HistoriaController::class, 'storeEvolucion'])->name('historias.evolucion.store');

    // Notificaciones
    Route::get('/notificaciones/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notificaciones/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

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
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
});

require __DIR__ . '/auth.php';
