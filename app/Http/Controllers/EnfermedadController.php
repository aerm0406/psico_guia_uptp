<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnfermedadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tipo = $request->get('tipo'); 
        $returnTo = $request->get('return_to');
        $editing = $request->get('editing');
        $search = $request->get('search');
        $categoriaFiltro = $request->get('categoria_filtro');

        $historia = $returnTo ? \App\Models\HistoriaClinica::obtenerPorPaciente($returnTo) : null;
        $historiaId = $historia ? $historia->id : null;
        
        $enfermedades = \App\Models\Enfermedad::obtenerEnfermedades(8, $search, $categoriaFiltro);
        
        if ($request->ajax()) {
            return view('enfermedades.components.disease_list', compact('enfermedades', 'tipo', 'returnTo', 'search', 'categoriaFiltro', 'editing', 'historiaId'));
        }
        
        return view('enfermedades.index', compact('enfermedades', 'tipo', 'returnTo', 'search', 'categoriaFiltro', 'editing', 'historiaId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $tipo = $request->get('tipo', 'fisica');
        $returnTo = $request->get('return_to');
        $editing = $request->get('editing');
        return view('enfermedades.create', compact('tipo', 'returnTo', 'editing'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|in:mental,fisica,biopsicosocial',
            'variacion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        // Verificar si ya existe esa combinación exacta
        $existe = \App\Models\Enfermedad::existeEnfermedad($validated['nombre'], $validated['variacion'], $validated['categoria']);

        if ($existe) {
            return back()->withErrors(['nombre' => 'Esta enfermedad con esa variación ya se encuentra registrada en la categoría seleccionada.'])->withInput();
        }

        \App\Models\Enfermedad::crearEnfermedad([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['variacion'], // Mapeo de variacion a tipo (BD)
            'categoria' => $validated['categoria'],
            'descripcion' => $validated['descripcion'],
        ]);

        return redirect()->route('enfermedades.index', [
            'tipo' => $request->tipo_contexto, 
            'return_to' => $request->return_to,
            'editing' => $request->editing
        ])->with('success', 'Enfermedad registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $enfermedad = \App\Models\Enfermedad::obtenerPorId($id);
        
        if (!$enfermedad) {
            return redirect()->route('enfermedades.index')->with('error', 'Enfermedad no encontrada.');
        }

        $tipo = $request->get('tipo');
        $returnTo = $request->get('return_to');
        $editing = $request->get('editing');

        return view('enfermedades.edit', compact('enfermedad', 'tipo', 'returnTo', 'editing'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|in:mental,fisica,biopsicosocial',
            'variacion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        // Verificar si ya existe esa combinación exacta (ignorando el registro actual)
        $existe = \App\Models\Enfermedad::existeEnfermedad($validated['nombre'], $validated['variacion'], $validated['categoria'], $id);

        if ($existe) {
            return back()->withErrors(['nombre' => 'Esta combinación de enfermedad y variación ya existe.'])->withInput();
        }

        \App\Models\Enfermedad::actualizarEnfermedad($id, [
            'nombre' => $validated['nombre'],
            'tipo' => $validated['variacion'], // Mapeo
            'categoria' => $validated['categoria'],
            'descripcion' => $validated['descripcion'],
        ]);

        return redirect()->route('enfermedades.index', [
            'tipo' => $request->tipo_contexto,
            'return_to' => $request->return_to,
            'editing' => $request->editing
        ])->with('success', 'Enfermedad actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        \App\Models\Enfermedad::eliminarEnfermedad($id);

        return redirect()->route('enfermedades.index', [
            'tipo' => $request->tipo_contexto, 
            'return_to' => $request->return_to,
            'editing' => $request->editing
        ])->with('success', 'Enfermedad eliminada correctamente.');
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
        $categoria = $request->get('categoria');
        
        $enfermedades = \App\Models\Enfermedad::obtenerEnfermedades(20, $search, $categoria);
        
        return response()->json($enfermedades->items());
    }
}
