<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Auth::loginUsingId(13); // psicólogo
$cita = DB::table('citas')->latest('id')->first();
$controller = new \App\Http\Controllers\CitaController();
try {
    $res = $controller->showJson($cita->id);
    echo $res->getContent();
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . ' En ' . $e->getFile() . ':' . $e->getLine();
}
