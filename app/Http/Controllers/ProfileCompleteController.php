<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileCompleteController extends Controller
{
    /**
     * Muestra el formulario inicial para que el usuario complete sus datos obligatorios.
     * Esta vista es obligatoria para usuarios que entran por primera vez.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show()
    {
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        
        // Si el perfil ya el sistema detecta que está completo, enviamos al dashboard
        if ($user && $user->profile_completed) {
            return redirect()->route('dashboard');
        }

        return view('profile.complete', compact('user'));
    }

    /**
     * Valida y almacena la información detallada del perfil (Personal, Médico y Académico).
     * También gestiona el cambio obligatorio de contraseña si aplica.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);

        $rules = [
            'nombres'          => ['required', 'string', 'max:100'],
            'apellidos'        => ['required', 'string', 'max:100'],
            'cedula'           => ['required', 'string', 'max:20', 'unique:users,cedula,' . $user->id],
            'email'            => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'genero'           => ['required', 'string', 'in:Masculino,Femenino'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono'         => ['required', 'string', 'max:50'],
            'ubicacion'        => ['required', 'string', 'max:255'],
            'discapacidad'     => ['required', 'string', 'in:Si,No'],
            'tipo_discapacidad'=> ['nullable', 'string', 'max:100', 'required_if:discapacidad,Si'],
            'tiene_hijos'      => ['required', 'string', 'in:Si,No'],
            'numero_hijos'     => ['nullable', 'integer', 'min:1', 'max:50', 'required_if:tiene_hijos,Si'],
            'estado_civil'     => ['required', 'string', 'in:Soltero(a),Casado(a),Divorciado(a),Viudo(a)'],
        ];

        // Solo requerir datos académicos si es paciente
        if ($user->role === 'paciente') {
            $rules['perfil_academico'] = ['required', 'string', 'in:Estudiante,Profesor,Obrero,Administrativo'];
            $rules['pnf']              = ['nullable', 'string', 'in:Informatica,Agroalimentaria,Mecanica,Administracion,Electrica', 'required_if:perfil_academico,Estudiante'];
            $rules['semestre']         = ['nullable', 'integer', 'min:1', 'max:12', 'required_if:perfil_academico,Estudiante'];
            $rules['horario_file']     = ['nullable', 'file', 'mimes:pdf,jpg,png,jpeg', 'max:4096'];
        }

        $validated = $request->validate($rules, [
            'nombres.required'          => 'El nombre es obligatorio.',
            'apellidos.required'        => 'El apellido es obligatorio.',
            'cedula.required'           => 'La cédula es obligatoria.',
            'cedula.unique'             => 'Esta cédula ya está registrada.',
            'email.required'            => 'El correo electrónico es obligatorio.',
            'email.unique'              => 'Este correo ya está registrado.',
            'genero.required'           => 'El género es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before'   => 'La fecha de nacimiento debe ser anterior a hoy.',
            'telefono.required'         => 'El teléfono es obligatorio.',
            'ubicacion.required'        => 'La ubicación es obligatoria.',
            'discapacidad.required'     => 'Debes indicar si tienes discapacidad.',
            'tipo_discapacidad.required_if' => 'Debes especificar el tipo de discapacidad.',
            'tiene_hijos.required'      => 'Debes indicar si tienes hijos.',
            'numero_hijos.required_if'  => 'Debes indicar cuántos hijos tienes.',
            'numero_hijos.min'          => 'El número de hijos debe ser al menos 1.',
            'estado_civil.required'     => 'El estado civil es obligatorio.',
            'perfil_academico.required' => 'El perfil académico es obligatorio.',
            'pnf.required_if'           => 'El PNF es obligatorio para estudiantes.',
            'semestre.required_if'      => 'El semestre es obligatorio para estudiantes.',
            'horario_file.mimes'        => 'El horario debe ser un archivo PDF, JPG o PNG.',
        ]);

        // Manejar subida de archivo si existe
        if ($request->hasFile('horario_file')) {
            $path = $request->file('horario_file')->store('horarios', 'public');
            $validated['horario_path'] = $path;
        }

        // Si el usuario debe cambiar su contraseña
        if ($user->must_change_password) {
            $rules['password'] = [
                'required', 
                'string', 
                'min:8', 
                'max:16', 
                'confirmed',
                'regex:/[a-z]/', 
                'regex:/[A-Z]/', 
                'regex:/[0-9]/', 
                'regex:/[@$!%*?&]/'
            ];
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
            $validated['must_change_password'] = false;
        }

        // Actualizar nombre completo (columna 'name' eliminada, se reconstruye dinámicamente desde 'nombres' + 'apellidos')
        $validated['profile_completed'] = true;

        // Guardar cambios usando Query Builder para consistencia técnica
        $this->actualizarPerfilUsuario($user->id, array_merge($validated, ['updated_at' => now()]));

        return redirect()->route('dashboard')->with('success', '¡Bienvenido! Tu perfil ha sido completado con éxito.');
    }
}

