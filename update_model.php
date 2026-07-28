<?php
$file = 'app/Models/User.php';
$content = file_get_contents($file);

$oldCreate = <<<EOT
    public static function crearUsuario(\$data)
    {
        try {
            DB::beginTransaction();
            \$nombreCompleto = trim((\$data['nombres'] ?? '') . ' ' . (\$data['apellidos'] ?? ''));
            \$usuario = self::create([
                'name' => \$nombreCompleto,
                'nombres' => \$data['nombres'] ?? null,
                'apellidos' => \$data['apellidos'] ?? null,
                'email' => \$data['email'],
                'password' => Hash::make(\$data['password']),
                'role' => \$data['role'],
                'cedula' => \$data['cedula'] ?? null,
            ]);
EOT;

$newCreate = <<<EOT
    public static function crearUsuario(\$data)
    {
        try {
            DB::beginTransaction();
            \$nombreCompleto = trim((\$data['nombres'] ?? '') . ' ' . (\$data['apellidos'] ?? ''));
            \$usuario = self::create([
                'name' => \$nombreCompleto,
                'nombres' => \$data['nombres'] ?? null,
                'apellidos' => \$data['apellidos'] ?? null,
                'email' => \$data['email'],
                'password' => Hash::make(\$data['password']),
                'role' => \$data['role'],
                'cedula' => \$data['cedula'] ?? null,
                'genero' => \$data['genero'] ?? null,
                'fecha_nacimiento' => \$data['fecha_nacimiento'] ?? null,
                'telefono' => \$data['telefono'] ?? null,
                'estado_civil' => \$data['estado_civil'] ?? null,
                'ubicacion' => \$data['ubicacion'] ?? null,
                'discapacidad' => \$data['discapacidad'] ?? 'No',
                'tipo_discapacidad' => \$data['tipo_discapacidad'] ?? null,
                'tiene_hijos' => \$data['tiene_hijos'] ?? 'No',
                'numero_hijos' => \$data['numero_hijos'] ?? null,
                'perfil_academico' => \$data['perfil_academico'] ?? null,
                'pnf' => \$data['pnf'] ?? null,
                'semestre' => \$data['semestre'] ?? null,
            ]);
EOT;

$content = str_replace($oldCreate, $newCreate, $content);

$oldUpdate = <<<EOT
    public static function actualizarUsuario(\$id, \$data)
    {
        try {
            DB::beginTransaction();
            \$nombreCompleto = trim((\$data['nombres'] ?? '') . ' ' . (\$data['apellidos'] ?? ''));
            \$updateData = [
                'email' => \$data['email'] ?? null,
                'role' => \$data['role'],
                'cedula' => \$data['cedula'] ?? null,
                'nombres' => \$data['nombres'] ?? null,
                'apellidos' => \$data['apellidos'] ?? null,
                'updated_at' => now(),
            ];
EOT;

$newUpdate = <<<EOT
    public static function actualizarUsuario(\$id, \$data)
    {
        try {
            DB::beginTransaction();
            \$nombreCompleto = trim((\$data['nombres'] ?? '') . ' ' . (\$data['apellidos'] ?? ''));
            \$updateData = [
                'email' => \$data['email'] ?? null,
                'role' => \$data['role'],
                'cedula' => \$data['cedula'] ?? null,
                'nombres' => \$data['nombres'] ?? null,
                'apellidos' => \$data['apellidos'] ?? null,
                'genero' => \$data['genero'] ?? null,
                'fecha_nacimiento' => \$data['fecha_nacimiento'] ?? null,
                'telefono' => \$data['telefono'] ?? null,
                'estado_civil' => \$data['estado_civil'] ?? null,
                'ubicacion' => \$data['ubicacion'] ?? null,
                'discapacidad' => \$data['discapacidad'] ?? 'No',
                'tipo_discapacidad' => \$data['tipo_discapacidad'] ?? null,
                'tiene_hijos' => \$data['tiene_hijos'] ?? 'No',
                'numero_hijos' => \$data['numero_hijos'] ?? null,
                'perfil_academico' => \$data['perfil_academico'] ?? null,
                'pnf' => \$data['pnf'] ?? null,
                'semestre' => \$data['semestre'] ?? null,
                'updated_at' => now(),
            ];
EOT;

$content = str_replace($oldUpdate, $newUpdate, $content);
file_put_contents($file, $content);
echo "User model updated.\n";
