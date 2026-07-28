<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cases = [
    [
        'psych' => '2026-07-01|Miércoles 09:00 - 10:00',
        'patient' => '2026-07-01|9:00 AM - 10:00 AM'
    ],
    [
        'psych' => '2026-07-06|Lunes 17:00 - 18:30',
        'patient' => '2026-07-06|5:00 PM - 6:30 PM'
    ],
    [
        'psych' => '2026-07-08|Miércoles 07:00 - 08:30',
        'patient' => '2026-07-08|7:00 AM - 8:30 AM'
    ]
];

foreach ($cases as $case) {
    $pNorm = \App\Models\Cita::normalizarBloque($case['psych']);
    $patNorm = \App\Models\Cita::normalizarBloque($case['patient']);
    echo "Psych: {$pNorm} | Patient: {$patNorm} -> ";
    echo ($pNorm === $patNorm ? 'MATCH' : 'MISMATCH') . "\n";
}

