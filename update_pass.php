<?php
$file = 'app/Http/Controllers/Admin/UserController.php';
$content = file_get_contents($file);

$old = <<<EOT
    public function updatePassword(Request \$request, \$id)
    {
        \$request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
EOT;

$new = <<<EOT
    public function updatePassword(Request \$request, \$id)
    {
        \$request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'max:16',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
                'confirmed'
            ],
        ], [
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial.',
        ]);
EOT;

$content = str_replace($old, $new, $content);
file_put_contents($file, $content);
echo "UserController updated.";
