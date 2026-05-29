<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\GrupoHorario;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // 4. Retorno de componente parcial para actualización asíncrona
        return view('agenda.components.pending-list', compact('citasPendientes'));
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
}

