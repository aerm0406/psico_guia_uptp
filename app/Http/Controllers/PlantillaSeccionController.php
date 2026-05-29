<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlantillaSeccion;
use Illuminate\Support\Facades\Auth;

class PlantillaSeccionController extends Controller
{
    /**
     * Muestra el listado de plantillas del psicólogo.
     */
    public function index()
    {
        $plantillas = PlantillaSeccion::obtenerPorPsicologo(Auth::id());
        
        return view('plantillas.index', compact('plantillas'));
    }

    public function create()
    {
        return view('plantillas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion_general' => 'nullable|string|max:255',
            'segmentos' => 'nullable|array',
            'segmentos.*' => 'required|string|max:255',
        ]);

        PlantillaSeccion::crear(Auth::id(), $data);

        return redirect()->route('plantillas.index')->with('success', 'Plantilla creada exitosamente.');
    }

    public function edit($id)
    {
        $plantilla = PlantillaSeccion::obtenerPorId($id, Auth::id());
        
        if (!$plantilla) {
            abort(404);
        }

        // Decodificar los segmentos si existen
        if ($plantilla->segmentos) {
            $plantilla->segmentos = json_decode($plantilla->segmentos, true);
        } else {
            $plantilla->segmentos = [];
        }

        return view('plantillas.edit', compact('plantilla'));
    }

    public function update(Request $request, $id)
    {
        $plantilla = PlantillaSeccion::obtenerPorId($id, Auth::id());
        
        if (!$plantilla) {
            abort(404);
        }

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion_general' => 'nullable|string|max:255',
            'segmentos' => 'nullable|array',
            'segmentos.*' => 'required|string|max:255',
        ]);

        PlantillaSeccion::actualizar($id, Auth::id(), $data);

        return redirect()->route('plantillas.index')->with('success', 'Plantilla actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $plantilla = PlantillaSeccion::obtenerPorId($id, Auth::id());
        
        if (!$plantilla) {
            abort(404);
        }

        PlantillaSeccion::eliminar($id, Auth::id());

        return redirect()->route('plantillas.index')->with('success', 'Plantilla eliminada exitosamente.');
    }
}
