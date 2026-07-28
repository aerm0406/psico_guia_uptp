<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/citas', 'GET')
);

\Auth::loginUsingId(13);
$request = Illuminate\Http\Request::create('/agenda/pending-list', 'GET');
$controller = $app->make('App\Http\Controllers\AgendaController');
try {
    $result = $controller->pendingList($request);
    echo "Result:\n";
    echo substr($result->render(), 0, 500); // just print first 500 chars to see if it succeeds
} catch (\Throwable $e) {
    echo "Exception caught:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
