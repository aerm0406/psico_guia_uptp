<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class PacienteController extends Controller
{
    /**
     * Si el usuario es administrador, muestra todos los pacientes registrados.
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);

        if ($user && $user->role === 'admin') {
            $pacientes = $this->obtenerTodosPacientes($buscar);
        } else {
            $pacientes = $this->obtenerPacientesConCitas($userId, $buscar);
        }
        
        if ($request->ajax()) {
            return view('pacientes.components.indexContent', compact('pacientes', 'buscar'));
        }
        return view('pacientes.index', compact('pacientes', 'buscar'));
    }

    /**
     * Retorna la información de un paciente específico en formato JSON para la vista modal.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showJson($id)
    {
        $paciente = $this->obtenerUsuario($id);
        abort_if(!$paciente, 404, 'Paciente no encontrado');

        return response()->json([
            'id' => $paciente->id,
            'nombre' => $paciente->name,
            'email' => $paciente->email,
            'telefono' => $paciente->telefono ?? 'No disponible',
            'registrado_en' => $paciente->created_at ? \Carbon\Carbon::parse($paciente->created_at)->format('d/m/Y') : 'Desconocido',
        ]);
    }
}

