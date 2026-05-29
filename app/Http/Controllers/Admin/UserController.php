<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class UserController extends Controller
{
    /**
     * Muestra el listado de usuarios con filtros de búsqueda y estadísticas rápidas.
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $role = $request->input('role');
        $usuarios = $this->buscarUsuarios($buscar, $role, 8);
        

        // Conteos estadísticos ABSOLUTOS para el tablero (Independientes de búsqueda)
        $stats = $this->obtenerEstadisticasUsuarios();
        $countTotal = $stats['total'];
        $countPacientes = $stats['pacientes'];
        $countPsicologos = $stats['psicologos'];
        $countAdmins = $stats['admins'];

        if ($request->ajax()) {
            return view('admin.users.components.user_list', compact('usuarios', 'buscar', 'role'));
        }

        return view('admin.users.index', compact(
            'usuarios', 'buscar', 'role', 'countTotal', 'countPacientes', 'countPsicologos', 'countAdmins'
        ));
    }

    /**
     * Muestra el formulario para registrar un nuevo usuario.
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $password = $this->generarPasswordSegura();
        return view('admin.users.create', compact('password'));
    }

    /**
     * Procesa y almacena un nuevo usuario en la base de datos con validaciones estrictas de seguridad.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:admin,psicologo,paciente',
            'cedula' => 'nullable|string|max:20|unique:users',
            'password' => [
                'required', 
                'string', 
                'min:8', 
                'max:16', 
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
        ], [
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial.',
        ]);

        try {
            $data = $request->all();
            
            $this->crearUsuarioAdmin($data);

            return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
        } catch (Exception $e) {
            return back()->with('error', 'Error al crear el usuario: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Muestra la información detallada de un usuario específico.
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $usuario = $this->obtenerUsuario($id);
        abort_if(!$usuario, 404, 'Usuario no encontrado');

        return view('admin.users.show', compact('usuario'));
    }

    /**
     * Muestra el formulario de edición para un usuario existente.
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $usuario = $this->obtenerUsuario($id);
        abort_if(!$usuario, 404, 'Usuario no encontrado');

        return view('admin.users.edit', compact('usuario'));
    }

    /**
     * Valida y actualiza los datos de un usuario en la base de datos.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|string|in:admin,psicologo,paciente',
            'cedula' => 'nullable|string|max:20|unique:users,cedula,' . $id,
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
        ]);

        try {
            $data = $request->all();
            
            $this->actualizarUsuarioAdmin($id, $data);

            return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
        } catch (Exception $e) {
            return back()->with('error', 'Error al actualizar el usuario: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Procesa la actualización de contraseña para un usuario.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->actualizarContrasenaUsuario($id, $request->password);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
            }

            return redirect()->route('admin.users.index')->with('success', 'Contraseña actualizada correctamente.');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al actualizar la contraseña: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al actualizar la contraseña: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la vista para modificar la contraseña de un usuario.
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editPassword($id)
    {
        $usuario = $this->obtenerUsuario($id);
        abort_if(!$usuario, 404, 'Usuario no encontrado');

        return view('admin.users.password', compact('usuario'));
    }

    /**
     * Elimina lógicamente a un usuario del sistema (cambio de estatus).
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            // Regla de seguridad: Evitar el autochequeo/borrado del admin actual
            if (\Illuminate\Support\Facades\Auth::id() == $id) {
                return back()->with('error', 'No puedes eliminar tu propia cuenta administrativa.');
            }

            $this->eliminarUsuarioLogico($id);

            return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (Exception $e) {
            return back()->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
}

