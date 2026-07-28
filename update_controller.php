<?php
$file = 'app/Http/Controllers/Admin/UserController.php';
$content = file_get_contents($file);

// Replace store rules
$oldStore = <<<EOT
        \$request->validate([
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
EOT;

$newStore = <<<EOT
        \$request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:admin,psicologo,paciente',
            'cedula' => 'required|string|max:20|unique:users',
            'genero' => 'nullable|string|in:Masculino,Femenino',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:50',
            'estado_civil' => 'nullable|string|in:Soltero(a),Casado(a),Divorciado(a),Viudo(a)',
            'ubicacion' => 'nullable|string|max:255',
            'discapacidad' => 'nullable|string|in:Si,No',
            'tipo_discapacidad' => 'nullable|string|max:255',
            'tiene_hijos' => 'nullable|string|in:Si,No',
            'numero_hijos' => 'nullable|integer',
            'perfil_academico' => 'nullable|string',
            'pnf' => 'nullable|string|max:255',
            'semestre' => 'nullable|integer',
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
EOT;

$content = str_replace($oldStore, $newStore, $content);

// Replace update rules
$oldUpdate = <<<EOT
        \$request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,' . \$id,
            'role' => 'required|string|in:admin,psicologo,paciente',
            'cedula' => 'nullable|string|max:20|unique:users,cedula,' . \$id,
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
        ]);
EOT;

$newUpdate = <<<EOT
        \$request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,' . \$id,
            'role' => 'required|string|in:admin,psicologo,paciente',
            'cedula' => 'required|string|max:20|unique:users,cedula,' . \$id,
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'genero' => 'nullable|string|in:Masculino,Femenino',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:50',
            'estado_civil' => 'nullable|string|in:Soltero(a),Casado(a),Divorciado(a),Viudo(a)',
            'ubicacion' => 'nullable|string|max:255',
            'discapacidad' => 'nullable|string|in:Si,No',
            'tipo_discapacidad' => 'nullable|string|max:255',
            'tiene_hijos' => 'nullable|string|in:Si,No',
            'numero_hijos' => 'nullable|integer',
            'perfil_academico' => 'nullable|string',
            'pnf' => 'nullable|string|max:255',
            'semestre' => 'nullable|integer',
            'password' => [
                'nullable',
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
EOT;

$content = str_replace($oldUpdate, $newUpdate, $content);
file_put_contents($file, $content);
echo "UserController updated.\n";
