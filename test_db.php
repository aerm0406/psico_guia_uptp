<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$citas = \Illuminate\Support\Facades\DB::table('citas')->whereNotNull('bloques_propuestos')->get();
foreach($citas as $c) {
    try {
        echo 'ID: ' . $c->id . ' - Bloques: ' . \Illuminate\Support\Facades\Crypt::decryptString($c->bloques_propuestos) . "\n";
    } catch (\Exception $e) {
        echo 'ID: ' . $c->id . ' - Error decrypting' . "\n";
    }
}
