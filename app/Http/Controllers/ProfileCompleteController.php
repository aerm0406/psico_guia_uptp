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

        $rules = [];

        // Campos de identidad: solo se validan si el usuario NO los tiene aún (fueron bloqueados en el formulario)
        if (!$user->nombres) {
            $rules['nombres'] = ['required', 'string', 'max:100'];
        }
        if (!$user->apellidos) {
            $rules['apellidos'] = ['required', 'string', 'max:100'];
        }
        if (!$user->cedula) {
            $rules['cedula'] = ['required', 'string', 'max:20', 'unique:users,cedula,' . $user->id];
        }
        if (!$user->email) {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id];
        }

        // Campos que siempre se piden en el completar perfil
        $rules['genero']           = ['required', 'string', 'in:Masculino,Femenino'];
        $rules['fecha_nacimiento'] = ['required', 'date', 'before:today'];
        $rules['telefono']         = ['required', 'string', 'max:50', 'unique:users,telefono,' . $user->id];
        $rules['ubicacion']        = ['required', 'string', 'max:255'];
        $rules['discapacidad']     = ['required', 'string', 'in:Si,No'];
        $rules['tipo_discapacidad']= ['nullable', 'string', 'max:100', 'required_if:discapacidad,Si'];
        $rules['tiene_hijos']      = ['required', 'string', 'in:Si,No'];
        $rules['numero_hijos']     = ['nullable', 'integer', 'min:1', 'max:50', 'required_if:tiene_hijos,Si'];
        $rules['estado_civil']     = ['required', 'string', 'in:Soltero(a),Casado(a),Divorciado(a),Viudo(a)'];

        // Solo requerir datos académicos si es paciente
        if ($user->role === 'paciente') {
            $rules['perfil_academico'] = ['required', 'string', 'in:Estudiante,Profesor,Obrero,Administrativo,Pre-escolar,Otros'];
            $rules['pnf']              = ['nullable', 'string', 'in:Informatica,Agroalimentaria,Agroalimentacion,Mecanica,Administracion,Electrica,Electricidad,Mantenimiento,Veterinaria,PDA,Distribucion_Logistica,Seguridad_Alimentaria_Nutricional', 'required_if:perfil_academico,Estudiante'];
            $rules['semestre']         = ['nullable', 'integer', 'min:1', 'max:12', 'required_if:perfil_academico,Estudiante'];
            $rules['horario_file']     = ['nullable', 'file', 'mimes:pdf,jpg,png,jpeg', 'max:4096'];
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
        }


        // Preguntas de Seguridad
        $rules['pregunta_seguridad_1'] = ['required', 'string', 'max:255'];
        $rules['respuesta_seguridad_1'] = ['required', 'string', 'max:255'];
        $rules['pregunta_seguridad_2'] = ['required', 'string', 'max:255', 'different:pregunta_seguridad_1'];
        $rules['respuesta_seguridad_2'] = ['required', 'string', 'max:255'];
        $rules['pregunta_seguridad_3'] = ['required', 'string', 'max:255', 'different:pregunta_seguridad_1', 'different:pregunta_seguridad_2'];
        $rules['respuesta_seguridad_3'] = ['required', 'string', 'max:255'];

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
            'telefono.unique'           => 'Este teléfono ya está registrado por otro usuario.',
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
            'password.required'         => 'Debes establecer una nueva contraseña.',
            'password.min'              => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'              => 'La contraseña no puede tener más de 16 caracteres.',
            'password.confirmed'        => 'Las contraseñas no coinciden.',
            'password.regex'            => 'La contraseña debe contener al menos una letra mayúscula, una minúscula, un número y un símbolo especial (@$!%*?&).',
            'pregunta_seguridad_1.required' => 'La primera pregunta de seguridad es obligatoria.',
            'respuesta_seguridad_1.required' => 'La respuesta a la primera pregunta es obligatoria.',
            'pregunta_seguridad_2.required' => 'La segunda pregunta de seguridad es obligatoria.',
            'pregunta_seguridad_2.different' => 'La segunda pregunta debe ser diferente a la primera.',
            'respuesta_seguridad_2.required' => 'La respuesta a la segunda pregunta es obligatoria.',
            'pregunta_seguridad_3.required' => 'La tercera pregunta de seguridad es obligatoria.',
            'pregunta_seguridad_3.different' => 'Las tres preguntas deben ser diferentes.',
            'respuesta_seguridad_3.required' => 'La respuesta a la tercera pregunta es obligatoria.',
        ]);

        // Eliminar campos bloqueados por seguridad (no deben poder ser sobreescritos)
        $camposBloqueados = ['nombres', 'apellidos', 'cedula', 'email'];
        foreach ($camposBloqueados as $campo) {
            if ($user->$campo) {
                unset($validated[$campo]);
            }
        }

        // Manejar subida de archivo si existe
        if ($request->hasFile('horario_file')) {
            $path = $request->file('horario_file')->store('horarios', 'public');
            $validated['horario_path'] = $path;
        }
        // Eliminar el campo de archivo temporal — no es columna de la tabla users
        unset($validated['horario_file']);

        // Hashear contraseña si era requerida
        if ($user->must_change_password) {
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
            $validated['must_change_password'] = false;
        }

        // Procesar las respuestas de seguridad
        $normalizeText = function($text) {
            $text = trim($text);
            $text = mb_strtolower($text, 'UTF-8');
            // Remover tildes y diacríticos
            $unwanted_array = ['Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' ];
            $text = strtr( $text, $unwanted_array );
            return $text;
        };

        $validated['respuesta_seguridad_1'] = \Illuminate\Support\Facades\Hash::make($normalizeText($validated['respuesta_seguridad_1']));
        $validated['respuesta_seguridad_2'] = \Illuminate\Support\Facades\Hash::make($normalizeText($validated['respuesta_seguridad_2']));
        $validated['respuesta_seguridad_3'] = \Illuminate\Support\Facades\Hash::make($normalizeText($validated['respuesta_seguridad_3']));

        // Actualizar nombre completo (columna 'name' eliminada, se reconstruye dinámicamente desde 'nombres' + 'apellidos')
        $validated['profile_completed'] = true;

        // Guardar cambios usando Query Builder para consistencia técnica
        $this->actualizarPerfilUsuario($user->id, array_merge($validated, ['updated_at' => now()]));

        return redirect()->route('dashboard')->with('success', '¡Bienvenido! Tu perfil ha sido completado con éxito.');
    }
}

