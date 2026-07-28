<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotaEvolucionCampo;
use Illuminate\Support\Facades\Auth;

class NotaEvolucionCampoController extends Controller
{
    /**
     * Muestra el listado de campos personalizados del psicólogo.
     */
    public function index()
    {
        $campos = NotaEvolucionCampo::obtenerCamposDisponiblesPaginados(Auth::id());
        return view('campos_evolucion.index', compact('campos'));
    }

    /**
     * Muestra el formulario para crear un nuevo campo.
     */
    public function create()
    {
        return view('campos_evolucion.create');
    }

    /**
     * Guarda un nuevo campo en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
        ]);

        if (NotaEvolucionCampo::existeTitulo(Auth::id(), $data['titulo'])) {
            return redirect()->back()->withInput()->with('error', 'Ya existe un campo con ese nombre. Por favor, elige un título diferente.');
        }

        NotaEvolucionCampo::crearPersonalizado(Auth::id(), $data['titulo']);

        return redirect()->route('campos-evolucion.index')->with('success', 'Campo de evolución creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un campo.
     */
    public function edit($id)
    {
        $campo = NotaEvolucionCampo::obtenerPorId($id, Auth::id());
        
        if (!$campo) {
            abort(404);
        }

        return view('campos_evolucion.edit', compact('campo'));
    }

    /**
     * Actualiza el campo especificado.
     */
    public function update(Request $request, $id)
    {
        $campo = NotaEvolucionCampo::obtenerPorId($id, Auth::id());
        
        if (!$campo) {
            abort(404);
        }

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
        ]);

        if (NotaEvolucionCampo::existeTitulo(Auth::id(), $data['titulo'], $id)) {
            return redirect()->back()->withInput()->with('error', 'Ya existe otro campo con ese nombre. Por favor, elige un título diferente.');
        }

        NotaEvolucionCampo::actualizar($id, Auth::id(), $data['titulo']);

        return redirect()->route('campos-evolucion.index')->with('success', 'Campo de evolución actualizado exitosamente.');
    }

    /**
     * Elimina (lógicamente) el campo especificado.
     */
    public function destroy($id)
    {
        $campo = NotaEvolucionCampo::obtenerPorId($id, Auth::id());
        
        if (!$campo) {
            abort(404);
        }

        NotaEvolucionCampo::eliminar($id, Auth::id());

        return redirect()->route('campos-evolucion.index')->with('success', 'Campo de evolución eliminado exitosamente.');
    }
}
