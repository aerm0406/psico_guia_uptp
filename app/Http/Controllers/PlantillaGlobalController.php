<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlantillaGlobal;
use Illuminate\Support\Facades\Auth;

class PlantillaGlobalController extends Controller
{
    /**
     * Muestra el esquema general del expediente (plantilla global única).
     */
    public function index()
    {
        $plantilla = PlantillaGlobal::obtenerPorPsicologo(Auth::id());

        // Si no tiene plantilla por alguna razón, se la creamos (Idempotencia similar al seeder)
        if (!$plantilla) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'PlantillaGlobalSeeder',
                '--force' => true
            ]);
            $plantilla = PlantillaGlobal::obtenerPorPsicologo(Auth::id());
        }

        return view('plantillas_globales.index', compact('plantilla'));
    }

    /**
     * Actualiza la plantilla global única y la activa.
     */
    public function update(Request $request)
    {
        $plantilla = PlantillaGlobal::obtenerPorPsicologo(Auth::id());

        if (!$plantilla) {
            abort(404);
        }

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'secciones_estructura' => 'required|array|min:1',
            'secciones_estructura.*.titulo' => 'required|string|max:255',
            'secciones_estructura.*.descripcion_general' => 'nullable|string|max:255',
            'secciones_estructura.*.segmentos' => 'required|array|min:1',
            'secciones_estructura.*.segmentos.*' => 'required|string|max:255',
        ]);

        PlantillaGlobal::actualizar(Auth::id(), $data);

        $mensaje = 'Esquema general guardado y activado exitosamente.';
        
        if ($request->input('aplicar_a_todos') == '1') {
            $resultado = PlantillaGlobal::aplicarATodos(Auth::id());
            if ($resultado['success']) {
                $mensaje .= ' ' . $resultado['message'];
            } else {
                $mensaje .= ' Sin embargo, hubo un error al aplicar a pacientes: ' . $resultado['message'];
            }
        }

        return redirect()->route('plantillas-globales.index')
            ->with('success', $mensaje);
    }

    /**
     * Aplica la plantilla global a todos los pacientes del psicólogo.
     */
    public function apply()
    {
        $plantilla = PlantillaGlobal::obtenerPorPsicologo(Auth::id());

        if (!$plantilla || $plantilla->status != 1) {
            return redirect()->route('plantillas-globales.index')
                ->with('error', 'Debe activar el esquema general antes de aplicarlo a todos los pacientes.');
        }

        $resultado = PlantillaGlobal::aplicarATodos(Auth::id());

        if ($resultado['success']) {
            return redirect()->route('plantillas-globales.index')
                ->with('success', $resultado['message']);
        }

        return redirect()->route('plantillas-globales.index')
            ->with('error', $resultado['message']);
    }
}
