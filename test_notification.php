<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$citaId = DB::table('citas')->first()->id;

$citaModel = \App\Models\Cita::instanciarParaNotificacion($citaId);

try {
    $notification = new \App\Notifications\CitaConfirmedNotification($citaModel);
    // Simulate what the Mail channel does internally
    $mailable = $notification->toMail((object)['email' => 'test@example.com']);
    $html = $mailable->render();
    echo "CitaConfirmedNotification OK.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

try {
    $notification = new \App\Notifications\CitaCancelledNotification($citaModel, 'paciente');
    $mailable = $notification->toMail((object)['email' => 'test@example.com']);
    $html = $mailable->render();
    echo "CitaCancelledNotification (paciente) OK.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "All tests finished.\n";
