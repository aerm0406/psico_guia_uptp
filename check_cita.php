<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Cita;
use App\Models\GrupoHorario;
use App\Models\Horario;

$psicologoId = 13;

// Simulate what obtenerPendientes returns
$citasPendientes = Cita::obtenerPendientes($psicologoId);

echo "=== citasPendientes count: " . $citasPendientes->count() . " ===\n";
foreach ($citasPendientes as $cp) {
    echo "  ID={$cp->id}, bloques_sugeridos='{$cp->bloques_sugeridos}'\n";
}

// Now simulate the exact Blade logic
$normalizeBlock = function ($text) {
    $value = trim($text ?? '');
    $value = preg_replace_callback('/(\d{1,2}):(\d{2})\s*(am|pm)\b/i', function($matches) {
        $hours = (int)$matches[1];
        $ampm = strtolower($matches[3]);
        if ($ampm === 'pm' && $hours < 12) $hours += 12;
        if ($ampm === 'am' && $hours === 12) $hours = 0;
        return sprintf('%02d:%s', $hours, $matches[2]);
    }, $value);
    $value = preg_replace(['/\s*[-–—]\s*/u', '/(\d{1,2}:\d{2}):\d{2}/', '/\s+/'], ['-', '$1', ' '], $value);
    $value = preg_replace('/(^|\s|-)(\d):/', '${1}0$2:', $value);
    return strtolower($value);
};

$currentDate = \Carbon\Carbon::parse('2026-06-08')->startOfWeek(\Carbon\Carbon::MONDAY); // Monday of the week shown
$dias = ['Lunes','Martes','Miércoles','Jueves','Viernes'];

$grupoActivo = GrupoHorario::obtenerActivoPorPsicologo($psicologoId);
$horarios = $grupoActivo ? Horario::obtenerPorGrupo($grupoActivo->id) : collect();

echo "\n=== Testing Lunes slot 07:00-08:00 ===\n";
$dia = 'Lunes';
$horaInicio = '07:00';
$horaFin = '08:00';
$diaIndex = 0;
$fechaDelDia = $currentDate->copy()->addDays($diaIndex)->toDateString();

echo "fechaDelDia: $fechaDelDia\n";

$result = $citasPendientes->filter(function ($cita) use ($normalizeBlock, $dia, $horaInicio, $horaFin, $fechaDelDia) {
    if (!$cita->bloques_sugeridos) { echo "  -> no bloques_sugeridos\n"; return false; }
    
    $raw = $cita->bloques_sugeridos;
    echo "  -> raw: '$raw'\n";
    
    $excepcionesStr = '';
    $horariosStr = $raw;
    if (str_contains($raw, '|')) {
        $parts = explode('|', $raw);
        $excepcionesStr = trim(str_replace('Días exceptuados:', '', $parts[0]));
        $horariosStr = trim(str_replace('Horarios:', '', $parts[1]));
    }
    
    // BUG: when there's no |, we don't strip "Horarios: " prefix!
    // Let's test as-is first
    
    $bloques = array_map('trim', explode(',', $horariosStr));
    echo "  -> parsed " . count($bloques) . " bloques\n";
    
    foreach ($bloques as $bloque) {
        $normalized = $normalizeBlock($bloque);
        $diaLower = strtolower($dia);
        $containsDay = str_contains($normalized, $diaLower);
        echo "  -> bloque='$bloque' normalized='$normalized' containsDay($diaLower)=" . ($containsDay?'Y':'N') . "\n";
        
        if ($containsDay) {
            if (preg_match('/(\d{1,2}:\d{2}.*?)\s*[-\x96\x97]\s*(\d{1,2}:\d{2}.*?)/i', $bloque, $m)) {
                $sI = \Carbon\Carbon::parse($m[1]);
                $sF = \Carbon\Carbon::parse($m[2]);
                echo "    -> regex matched: sI={$sI->format('H:i')}, sF={$sF->format('H:i')}\n";
                echo "    -> overlap: horaInicio($horaInicio) < sF({$sF->format('H:i')})? " . (\Carbon\Carbon::parse($horaInicio)->lt($sF) ? 'Y' : 'N') . "\n";
                echo "    -> overlap: horaFin($horaFin) > sI({$sI->format('H:i')})? " . (\Carbon\Carbon::parse($horaFin)->gt($sI) ? 'Y' : 'N') . "\n";
                if (\Carbon\Carbon::parse($horaInicio)->lt($sF) && \Carbon\Carbon::parse($horaFin)->gt($sI)) {
                    echo "    -> MATCH!\n";
                    return true;
                }
            } else {
                echo "    -> regex DID NOT MATCH\n";
            }
        }
    }
    return false;
});

echo "\nResult count: " . $result->count() . "\n";
