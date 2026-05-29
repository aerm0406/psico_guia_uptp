<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Horario;
use App\Models\GrupoHorario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HorarioController extends Controller
{
    /**
     * Muestra la lista de bloques de horario del usuario autenticado.
     * Soporta filtrado por grupo específico y por día de la semana.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $dias = Horario::diasSemana();
        $filtroDia = $request->get('dia');
        $grupoId = $request->get('grupo');

        // Obtener el grupo activo del usuario
        $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo($userId);

        // Si se pasó un grupo específico por parámetro, cargarlo y usarlo
        $grupoSeleccionado = null;
        if ($grupoId) {
            $grupoSeleccionado = GrupoHorario::obtenerPorIdYUsuario($grupoId, $userId);

            if ($grupoActivo && $grupoSeleccionado && $grupoActivo->id === $grupoSeleccionado->id) {
                $request->session()->reflash();
                return redirect()->route('horarios.index', $filtroDia ? ['dia' => $filtroDia] : []);
            }
        }

        $currentGrupoId = $grupoSeleccionado ? $grupoSeleccionado->id : ($grupoActivo ? $grupoActivo->id : null);
        $horarios = Horario::obtenerPorFiltros($userId, $currentGrupoId, $filtroDia);

        // Agrupar por día para el calendario semanal
        $horariosPorDia = [];
        foreach ($dias as $dia) {
            $horariosPorDia[$dia] = $horarios->where('dia', $dia);
        }

        $tieneCitasPendientes = Horario::hasPendingCitas($userId);

        return view('horarios.index', compact('horarios', 'horariosPorDia', 'dias', 'filtroDia', 'grupoActivo', 'tieneCitasPendientes', 'grupoSeleccionado'));
    }

    /**
     * Mostrar el formulario para crear una nueva sugerencia/hora.
     */
    /**
     * Muestra el formulario para crear un nuevo bloque de horario.
     * Verifica que no existan citas pendientes que impidan la modificación de la disponibilidad.
     */
    public function create(Request $request)
    {
        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        $dias = Horario::diasSemana();
        $tieneCitasPendientes = false;
        $grupoRetorno = $request->query('grupo');

        return view('horarios.create', compact('dias', 'tieneCitasPendientes', 'grupoRetorno'));
    }

    /**
     * Mostrar el formulario para editar un horario.
     */
    /**
     * Muestra el formulario para editar un bloque de horario existente.
     * Valida la propiedad del bloque y la ausencia de citas pendientes.
     */
    public function edit(Request $request, $id)
    {
        $horario = \App\Models\Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->user_id !== Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        $dias = Horario::diasSemana();
        $grupoRetorno = $request->query('grupo', $horario->grupo_horario_id);

        return view('horarios.edit', compact('horario', 'dias', 'grupoRetorno'));
    }

    /**
     * Almacena un nuevo bloque de horario en la base de datos.
     * Incluye normalización de horas y validación de superposición (overlaps).
     */
    public function store(Request $request)
    {
        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        // Construir hora_inicio y hora_fin a partir de los campos individuales de hora de la vista
        $horaInicio = $this->parseTimeInput(
            $request->input('hora_inicio_hora'),
            $request->input('hora_inicio_minuto'),
            $request->input('hora_inicio_periodo')
        );
        $horaFin = $this->parseTimeInput(
            $request->input('hora_fin_hora'),
            $request->input('hora_fin_minuto'),
            $request->input('hora_fin_periodo')
        );

        $request->merge([
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
        ]);

        $grupoEspecificoId = $request->input('grupo_id');
        
        if ($grupoEspecificoId) {
            $grupoAModificar = GrupoHorario::obtenerPorIdYUsuario($grupoEspecificoId, Auth::id());
                
            $activoPorDefecto = ($grupoAModificar && $grupoAModificar->activo == GrupoHorario::STATUS_ACTIVE) ? Horario::STATUS_ACTIVE : Horario::STATUS_INACTIVE;
            $grupoAsignarId = $grupoAModificar ? $grupoAModificar->id : null;
        } else {
            // Asignar estado en base al grupo activo (auto-aplicar cambios).
            $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo(Auth::id());

            $activoPorDefecto = $grupoActivo ? Horario::STATUS_ACTIVE : Horario::STATUS_INACTIVE;
            $grupoAsignarId = $grupoActivo ? $grupoActivo->id : null;
        }

        $validated = $request->validate([
            'dia' => 'required|string|in:' . implode(',', Horario::diasSemana()),
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'descripcion' => 'nullable|string',
        ]);

        if (Horario::overlaps(Auth::id(), $validated['dia'], $validated['hora_inicio'], $validated['hora_fin'], null, $grupoAsignarId)) {
            return back()
                ->withErrors(['hora_inicio' => 'El bloque de horario se superpone con otro existente.'])
                ->withInput();
        }

        Horario::crear([
            'user_id' => Auth::id(),
            'dia' => $validated['dia'],
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fin' => $validated['hora_fin'],
            'descripcion' => $validated['descripcion'],
            'activo' => $activoPorDefecto,
            'grupo_horario_id' => $grupoAsignarId,
        ]);

        return redirect()
            ->route('horarios.index', $grupoAsignarId ? ['grupo' => $grupoAsignarId] : [])
            ->with('success', 'Bloque de tiempo creado correctamente.');
    }

    /**
     * Mostrar un bloque de horario.
     */
    /**
     * Muestra el detalle de un bloque de horario específico.
     */
    public function show(Request $request, $id)
    {
        $horario = \App\Models\Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->user_id !== Auth::id(), 403);
        $grupoRetorno = $request->query('grupo', $horario->grupo_horario_id);

        return view('horarios.show', compact('horario', 'grupoRetorno'));
    }

    /**
     * Actualiza la información de un bloque de horario existente.
     * Realiza validaciones de seguridad y superposición horaria.
     */
    public function update(Request $request, $id)
    {
        $horario = \App\Models\Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->user_id !== Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        // Construir hora_inicio y hora_fin a partir de los campos individuales de hora de la vista
        $horaInicio = $this->parseTimeInput(
            $request->input('hora_inicio_hora'),
            $request->input('hora_inicio_minuto'),
            $request->input('hora_inicio_periodo')
        );
        $horaFin = $this->parseTimeInput(
            $request->input('hora_fin_hora'),
            $request->input('hora_fin_minuto'),
            $request->input('hora_fin_periodo')
        );

        $request->merge([
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
        ]);

        $validated = $request->validate([
            'dia' => 'required|string|in:' . implode(',', Horario::diasSemana()),
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'descripcion' => 'nullable|string',
        ]);

        if (Horario::overlaps(Auth::id(), $validated['dia'], $validated['hora_inicio'], $validated['hora_fin'], $horario->id, $horario->grupo_horario_id)) {
            return back()
                ->withErrors(['hora_inicio' => 'El bloque de horario se superpone con otro existente.'])
                ->withInput();
        }

        // Actualizar solo los campos editables, preservando el grupo y el estado del bloque
        Horario::actualizar($horario->id, [
            'dia' => $validated['dia'],
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fin' => $validated['hora_fin'],
            'descripcion' => $validated['descripcion'],
            // grupo_horario_id y activo se preservan sin cambios
        ]);

        return redirect()
            ->route('horarios.index', $horario->grupo_horario_id ? ['grupo' => $horario->grupo_horario_id] : [])
            ->with('success', 'Bloque de tiempo actualizado correctamente.');
    }

    /**
     * Eliminar un horario.
     */
    /**
     * Elimina (marcado lógico) un bloque de horario.
     * Soporta tanto peticiones normales como AJAX.
     */
    public function destroy(Request $request, $id)
    {
        $horario = \App\Models\Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->user_id !== Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.'
                ], 400);
            }
            return redirect()->route('horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        $grupoId = $horario->grupo_horario_id;
        Horario::eliminar($horario->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Bloque de horario eliminado correctamente.',
                'grupo_id' => $grupoId
            ], 200);
        }

        return redirect()
            ->route('horarios.index', $grupoId ? ['grupo' => $grupoId] : [])
            ->with('success', 'Bloque de horario eliminado correctamente.');
    }

    /**
     * Activar un horario.
     */
    /**
     * Activa un bloque de horario previamente inactivo.
     */
    public function activate($id)
    {
        $horario = \App\Models\Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->user_id !== Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        Horario::actualizar($horario->id, ['activo' => Horario::STATUS_ACTIVE]);

        return redirect()->route('horarios.index')->with('success', 'Bloque de tiempo activado.');
    }

    /**
     * Desactivar un horario.
     */
    /**
     * Desactiva un bloque de horario para que no sea seleccionable en la agenda.
     */
    public function deactivate($id)
    {
        $horario = \App\Models\Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->user_id !== Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        Horario::actualizar($horario->id, ['activo' => Horario::STATUS_INACTIVE]);

        return redirect()->route('horarios.index')->with('success', 'Bloque de tiempo desactivado.');
    }

    /**
     * Parsea las entradas de hora individuales de la vista (hora, minuto, AM/PM) a formato de 24 horas H:i.
     */
    private function parseTimeInput($hora, $minuto, $periodo)
    {
        if (empty($hora) || empty($minuto) || empty($periodo)) {
            return null;
        }
        
        $hora = (int)$hora;
        if ($periodo === 'PM' && $hora < 12) {
            $hora += 12;
        } elseif ($periodo === 'AM' && $hora === 12) {
            $hora = 0;
        }
        
        return str_pad($hora, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minuto, 2, '0', STR_PAD_LEFT);
    }
}
