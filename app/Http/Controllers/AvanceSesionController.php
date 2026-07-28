<?php

namespace App\Http\Controllers;

use App\Models\AvanceSesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AvanceSesionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!in_array(Auth::user()->role, ['psicologo', 'admin'])) {
            abort(403);
        }

        $search = $request->input('search');
        
        $avances = AvanceSesion::obtenerPaginadoPorPsicologo(Auth::id(), $search, 6);

        if ($request->ajax()) {
            return view('avances_sesion.partials.table', compact('avances'))->render();
        }

        return view('avances_sesion.index', compact('avances', 'search'));
    }

    public function create()
    {
        if (!in_array(Auth::user()->role, ['psicologo', 'admin'])) {
            abort(403);
        }

        return view('avances_sesion.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['psicologo', 'admin'])) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'valor' => 'required|integer|min:1|max:10',
            'descripcion' => 'nullable|string',
        ]);

        try {
            $datos = $request->only(['nombre', 'valor', 'descripcion']);
            AvanceSesion::crear(Auth::id(), $datos);
            return redirect()->route('avances_sesion.index')->with('success', 'Avance de sesión registrado con éxito.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        if (!in_array(Auth::user()->role, ['psicologo', 'admin'])) {
            abort(403);
        }

        $avance = DB::table('avances_sesion')->where('id', $id)->first();
        if (!$avance) {
            abort(404);
        }

        return view('avances_sesion.edit', compact('avance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['psicologo', 'admin'])) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'valor' => 'required|integer|min:1|max:10',
            'descripcion' => 'nullable|string',
        ]);

        try {
            $datos = $request->only(['nombre', 'valor', 'descripcion']);
            AvanceSesion::actualizar($id, Auth::id(), $datos);
            return redirect()->route('avances_sesion.index')->with('success', 'Avance actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!in_array(Auth::user()->role, ['psicologo', 'admin'])) {
            abort(403);
        }

        try {
            AvanceSesion::eliminar($id, Auth::id());
            return redirect()->route('avances_sesion.index')->with('success', 'Avance de sesión eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
