<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cita = DB::table('citas')->first();
if (!$cita) {
    echo "No citas found.\n";
    exit;
}

$paciente = DB::table('users')->where('id', $cita->user_id)->first();
$psicologo = DB::table('users')->where('id', $cita->psicologo_id)->first();

echo "Testing Mailables...\n";

try {
    $mail = new \App\Mail\CitaCanceladaMail($cita, $paciente, $psicologo, 'Psicólogo');
    $html = $mail->render();
    echo "CitaCanceladaMail OK.\n";
} catch (\Exception $e) {
    echo "Error CitaCanceladaMail: " . $e->getMessage() . "\n";
}

try {
    $mail = new \App\Mail\CitaConfirmada($cita, $paciente, $psicologo);
    $html = $mail->render();
    echo "CitaConfirmada OK.\n";
} catch (\Exception $e) {
    echo "Error CitaConfirmada: " . $e->getMessage() . "\n";
}

try {
    $mail = new \App\Mail\ContrapropuestaCitaMail($cita, $psicologo);
    $html = $mail->render();
    echo "ContrapropuestaCitaMail OK.\n";
} catch (\Exception $e) {
    echo "Error ContrapropuestaCitaMail: " . $e->getMessage() . "\n";
}

try {
    $mail = new \App\Mail\PropuestaAceptadaMail($cita, $paciente);
    $html = $mail->render();
    echo "PropuestaAceptadaMail OK.\n";
} catch (\Exception $e) {
    echo "Error PropuestaAceptadaMail: " . $e->getMessage() . "\n";
}

try {
    $mail = new \App\Mail\PropuestaRechazadaMail($cita, $paciente);
    $html = $mail->render();
    echo "PropuestaRechazadaMail OK.\n";
} catch (\Exception $e) {
    echo "Error PropuestaRechazadaMail: " . $e->getMessage() . "\n";
}

try {
    $mail = new \App\Mail\CitaPospuestaMail($cita, $psicologo);
    $html = $mail->render();
    echo "CitaPospuestaMail OK.\n";
} catch (\Exception $e) {
    echo "Error CitaPospuestaMail: " . $e->getMessage() . "\n";
}

try {
    $mail = new \App\Mail\CitaRechazadaMail($cita, $psicologo);
    $html = $mail->render();
    echo "CitaRechazadaMail OK.\n";
} catch (\Exception $e) {
    echo "Error CitaRechazadaMail: " . $e->getMessage() . "\n";
}

echo "All tests finished.\n";
