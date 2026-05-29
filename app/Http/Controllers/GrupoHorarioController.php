<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\GrupoHorario;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrupoHorarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Muestra la lista de grupos de horarios del usuario.
     * Incluye información sobre los bloques asociados a cada grupo.
     */
    public function index()
    {
        $userId = Auth::id();
        $grupos = GrupoHorario::obtenerConHorarios($userId);
        $tieneCitasPendientes = Horario::hasPendingCitas($userId);

        return view('grupos_horarios.index', compact('grupos', 'tieneCitasPendientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Muestra el formulario para crear un nuevo grupo de horarios.
     */
    public function create()
    {
        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        return view('grupos_horarios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Almacena un nuevo grupo de horarios vacío.
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        
        if (Horario::hasPendingCitas($userId)) {
            return redirect()->route('grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        // Validar nombre único dentro del usuario (grupos no eliminados)
        if (GrupoHorario::existeNombre($userId, $request->nombre)) {
            return redirect()->route('grupos_horarios.index')
                ->with('error', 'Ya existe un grupo con ese nombre. Usa un nombre diferente.');
        }

        // Verificar que haya al menos un bloque para el usuario
        if (!Horario::hasBloques($userId)) {
            return redirect()->route('grupos_horarios.index')
                ->with('error', 'No puedes crear un grupo sin bloques. Agrega al menos un bloque de horario primero.');
        }

        GrupoHorario::crear([
            'user_id' => $userId,
            'nombre' => $request->nombre,
            'activo' => GrupoHorario::STATUS_INACTIVE,
        ]);

        return redirect()->route('grupos_horarios.index')
            ->with('success', 'Grupo de horario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * Muestra el detalle de un grupo de horarios específico.
     */
    public function show($id)
    {
        $grupoHorario = \App\Models\GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            return redirect()->route('grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        $dias = Horario::diasSemana();
        $horarios = Horario::obtenerPorGrupo($grupoHorario->id);

        // Agrupar por día para el calendario semanal
        $horariosPorDia = [];
        foreach ($dias as $dia) {
            $horariosPorDia[$dia] = $horarios->where('dia', $dia);
        }

        $tieneCitasPendientes = Horario::hasPendingCitas(Auth::id());

        return view('grupos_horarios.show', compact('grupoHorario', 'horarios', 'horariosPorDia', 'dias', 'tieneCitasPendientes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Muestra el formulario para editar el nombre de un grupo de horarios.
     */
    public function edit($id)
    {
        $grupoHorario = \App\Models\GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            return redirect()->route('grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        return view('grupos_horarios.edit', compact('grupoHorario'));
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Actualiza el nombre de un grupo de horarios.
     */
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $grupoHorario = \App\Models\GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== $userId) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            return redirect()->route('grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        if (Horario::hasPendingCitas($userId)) {
            return redirect()->route('grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        if (GrupoHorario::existeNombre($userId, $request->nombre, $grupoHorario->id)) {
            return redirect()->route('grupos_horarios.index')
                ->with('error', 'Ya existe otro grupo con ese nombre. Cambia el nombre.');
        }

        GrupoHorario::actualizar($grupoHorario->id, [
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('grupos_horarios.index')
            ->with('success', 'Grupo de horario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Elimina un grupo de horarios y todos sus bloques asociados.
     */
    public function destroy(Request $request, $id)
    {
        $grupoHorario = \App\Models\GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Este grupo ya ha sido eliminado.'], 400);
            }
            return redirect()->route('grupos_horarios.index')->with('error', 'Este grupo ya ha sido eliminado.');
        }

        if (Horario::hasPendingCitas(Auth::id())) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.'], 400);
            }
            return redirect()->route('grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        GrupoHorario::eliminar($id);

        if ($request->wantsJson() || $request->ajax()) {
            session()->flash('success', 'Grupo de horario eliminado correctamente.');
            return response()->json([
                'status' => 'success',
                'message' => 'Grupo de horario eliminado correctamente.'
            ], 200);
        }

        return redirect()->route('grupos_horarios.index')
            ->with('success', 'Grupo de horario eliminado correctamente.');
    }

    /**
     * Crea un nuevo grupo a partir de los bloques configurados actualmente en la vista de horarios.
     * Esta función permite "congelar" la configuración actual como un grupo reutilizable.
     */
    public function storeFromHorarios(Request $request)
    {
        $rules = [
            'action' => 'required|in:update,create',
        ];

        if ($request->input('action') === 'create') {
            $rules['nombre'] = 'required|string|max:255';
        } else {
            $rules['nombre'] = 'nullable|string|max:255';
        }

        $request->validate($rules);

        $userId = Auth::id();
        $action = $request->input('action');
        $nombre = $request->input('nombre');

        if (Horario::hasPendingCitas($userId)) {
            return redirect()->route('horarios.index')->with('error', 'No puedes cambiar el grupo de horarios mientras tengas citas pendientes.');
        }

        try {
            $message = GrupoHorario::crearDesdeHorarios($userId, $action, $nombre);
            return redirect()->route('horarios.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('horarios.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Activa un grupo de horarios, convirtiéndolo en el horario vigente del psicólogo.
     */
    public function activate(Request $request, $id)
    {
        $grupoHorario = \App\Models\GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Este grupo ha sido eliminado.'
                ], 400);
            }

            return redirect()->route('grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        if (Horario::hasPendingCitas(Auth::id())) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes cambiar o activar otro grupo de horarios mientras tengas citas pendientes o confirmadas.'
                ], 400);
            }

            return redirect()->route('grupos_horarios.index')->with('error', 'No puedes cambiar o activar otro grupo de horarios mientras tengas citas pendientes o confirmadas.');
        }

        GrupoHorario::activate($id);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Grupo de horario activado correctamente.'
            ], 200);
        }

        return redirect()->route('grupos_horarios.index')
            ->with('success', 'Grupo de horario activado correctamente.');
    }

    /**
     * Muestra el detalle de los grupos de horarios (historial).
     */

    /**
     * Desactiva el grupo de horarios actual.
     */
    public function deactivate(Request $request, $id)
    {
        $grupoHorario = \App\Models\GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Este grupo ha sido eliminado.'
                ], 400);
            }

            return redirect()->route('grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        if (Horario::hasPendingCitas(Auth::id())) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes cambiar o desactivar el grupo de horarios mientras tengas citas pendientes o confirmadas.'
                ], 400);
            }

            return redirect()->route('grupos_horarios.index')->with('error', 'No puedes cambiar o desactivar el grupo de horarios mientras tengas citas pendientes o confirmadas.');
        }

        GrupoHorario::deactivate($id);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Grupo de horario desactivado correctamente.'
            ], 200);
        }

        return redirect()->route('grupos_horarios.index')
            ->with('success', 'Grupo de horario desactivado correctamente.');
    }
}
