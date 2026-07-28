<?php
function normalizarBloque($bloque)
{
    $value = trim($bloque ?? '');

    $value = preg_replace_callback('/(\d{1,2}):(\d{2})\s*(am|pm)\b/i', function($matches) {
        $hours = (int)$matches[1];
        $ampm = strtolower($matches[3]);
        if ($ampm === 'pm' && $hours < 12) $hours += 12;
        if ($ampm === 'am' && $hours === 12) $hours = 0;
        return sprintf('%02d:%s', $hours, $matches[2]);
    }, $value);

    $value = preg_replace([
        '/\s*[-–—]\s*/u',
        '/(\d{1,2}:\d{2}):\d{2}/',
        '/\s+/'
    ], [
        '-',
        '$1',
        ' '
    ], $value);

    $value = preg_replace('/(^|\s|-)(\d):/', '${1}0$2:', $value);
    return strtolower(str_replace(' ', '', $value));
}

$nuevosBloques = 'Horarios propuestos: 2026-07-06: 7:00 AM - 8:00 AM; 2026-07-08: 9:00 AM - 10:00 AM';
$parts = explode('|', $nuevosBloques);
$horariosPart = '';
foreach ($parts as $p) {
    if (str_contains($p, 'Horarios propuestos:')) {
        $horariosPart = trim(str_replace('Horarios propuestos:', '', $p));
        break;
    }
}

$pacienteBloques = [];
$diasConSlots = explode(';', $horariosPart);
foreach ($diasConSlots as $diaConSlot) {
    $diaConSlot = trim($diaConSlot);
    if (!$diaConSlot) continue;
    $colonPos = strpos($diaConSlot, ':');
    if ($colonPos !== false) {
        $fecha = trim(substr($diaConSlot, 0, $colonPos));
        $slotsStr = trim(substr($diaConSlot, $colonPos + 1));
        $slots = array_filter(array_map('trim', explode(',', $slotsStr)));
        foreach ($slots as $slot) {
            $pacienteBloques[] = $fecha . '|' . $slot;
        }
    }
}
var_dump($pacienteBloques);

$propuestosArr = ['2026-07-06|7:00 AM - 8:00 AM', '2026-07-08|9:00 AM - 10:00 AM'];

$propuestosArrNorm = [];
foreach ($propuestosArr as $pb) {
    $propuestosArrNorm[] = normalizarBloque($pb);
}
var_dump($propuestosArrNorm);

foreach ($pacienteBloques as $pbPac) {
    $pbPacNorm = normalizarBloque($pbPac);
    echo "Paciente normalizado: $pbPacNorm\n";
    if (in_array($pbPacNorm, $propuestosArrNorm)) {
        echo "COINCIDE: $pbPac\n";
    }
}
