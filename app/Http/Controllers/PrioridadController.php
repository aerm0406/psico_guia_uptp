<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PrioridadController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $buscar = $request->input('buscar');
        
        $prioridades = \App\Models\Prioridad::buscarYPaginar($buscar, $userId);
            
        return view('agenda.prioridades.index', compact('prioridades', 'buscar'));
    }

    public function create()
    {
        return view('agenda.prioridades.create');
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            // Solo los niveles no usados por la base del sistema
            'nivel_gravedad' => 'required|integer|in:2,3,4,6,8,9',
        ], [
            'nivel_gravedad.in' => 'El nivel seleccionado no es válido o está reservado por el sistema.',
        ]);

        $nombreNormalizado = strtolower(trim($validated['nombre']));
        $nivel = $validated['nivel_gravedad'];

        // Check if name already exists globally or for this psychologist
        $existsName = DB::table('prioridades')
            ->where('nombre', $nombreNormalizado)
            ->where('activo', 1)
            ->where(function ($query) use ($userId) {
                $query->whereNull('psicologo_id')
                      ->orWhere('psicologo_id', $userId);
            })->exists();

        if ($existsName) {
            return back()->with('error', 'El nombre de esta prioridad ya está en uso.')->withInput();
        }

        // Check if level is already used by this psychologist
        $existsLevel = DB::table('prioridades')
            ->where('nivel_gravedad', $nivel)
            ->where('activo', 1)
            ->where('psicologo_id', $userId)
            ->exists();

        if ($existsLevel) {
            return back()->with('error', 'Este nivel de gravedad ya está asignado a otra de tus prioridades. Elimínala primero para liberar el nivel.')->withInput();
        }

        DB::table('prioridades')->insert([
            'nombre' => $nombreNormalizado,
            'nivel_gravedad' => $nivel,
            'psicologo_id' => $userId,
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('agenda.prioridades.index')->with('success', 'Prioridad creada correctamente.');
    }

    public function destroy($id)
    {
        $userId = Auth::id();

        $prioridad = DB::table('prioridades')->where('id', $id)->first();

        if (!$prioridad) {
            return back()->with('error', 'Prioridad no encontrada.');
        }

        if ($prioridad->psicologo_id !== $userId) {
            return back()->with('error', 'No puedes eliminar una prioridad base o que no te pertenece.');
        }

        // Prevent deletion if used in citas
        $citasConPrioridad = DB::table('citas')
            ->where('psicologo_id', $userId)
            ->where('prioridad', $prioridad->nombre)
            ->exists();
            
        if ($citasConPrioridad) {
            return back()->with('error', 'No puedes eliminar esta prioridad porque está asignada a una o más citas pendientes.');
        }

        DB::table('prioridades')->where('id', $id)->update([
            'activo' => 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('agenda.prioridades.index')->with('success', 'Prioridad eliminada. El nivel ha sido liberado.');
    }
}
