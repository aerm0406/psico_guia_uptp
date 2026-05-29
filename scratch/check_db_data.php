<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('historia_clinicas')->get();
echo "Total rows: " . count($rows) . "\n";
foreach ($rows as $row) {
    echo "Row ID: {$row->id} | User ID: {$row->user_id}\n";
    foreach ($row as $k => $v) {
        if ($v !== null && $k !== 'id' && $k !== 'user_id' && $k !== 'psicologo_id' && $k !== 'created_at' && $k !== 'updated_at') {
            echo "  $k: " . substr($v, 0, 100) . "\n";
        }
    }
}
