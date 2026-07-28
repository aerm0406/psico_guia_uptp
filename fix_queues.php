<?php
$dir = __DIR__ . '/app/Notifications';
foreach (glob($dir . '/*.php') as $file) {
    $content = file_get_contents($file);
    if (!str_contains($content, 'implements ShouldQueue')) {
        $content = preg_replace('/class\s+(\w+)\s+extends\s+Notification\s*\{/', 'class $1 extends Notification implements ShouldQueue' . "\n" . '{', $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
$dirMail = __DIR__ . '/app/Mail';
foreach (glob($dirMail . '/*.php') as $file) {
    $content = file_get_contents($file);
    if (!str_contains($content, 'implements ShouldQueue')) {
        $content = preg_replace('/class\s+(\w+)\s+extends\s+Mailable\s*\{/', 'class $1 extends Mailable implements ShouldQueue' . "\n" . '{', $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}

// Fix CitaConfirmedNotification constructor call to CitaConfirmada
$file = __DIR__ . '/app/Notifications/CitaConfirmedNotification.php';
$content = file_get_contents($file);
$content = str_replace('new \App\Mail\CitaConfirmada($this->cita, $this->cita->paciente, $this->cita->psicologo)', 'new \App\Mail\CitaConfirmada($this->cita)', $content);
file_put_contents($file, $content);
echo "Fixed CitaConfirmada constructor call.\n";
