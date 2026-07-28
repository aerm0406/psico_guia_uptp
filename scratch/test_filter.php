<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "--- TEST ROLE: paciente ---\n";
$pacientes = User::buscarUsuarios('', 'paciente', 8);
foreach ($pacientes as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Role: {$u->role}\n";
}

echo "\n--- TEST ROLE: psicologo ---\n";
$psicologos = User::buscarUsuarios('', 'psicologo', 8);
foreach ($psicologos as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Role: {$u->role}\n";
}

echo "\n--- TEST SEARCH: 'Lucifer' ---\n";
$search1 = User::buscarUsuarios('Lucifer', '', 8);
foreach ($search1 as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Role: {$u->role}\n";
}

echo "\n--- TEST SEARCH: 'Lucifer Mañana' (Full Name) ---\n";
$search2 = User::buscarUsuarios('Lucifer Mañana', '', 8);
foreach ($search2 as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Role: {$u->role}\n";
}
