<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("SHOW COLUMNS FROM `users`");
foreach ($columns as $c) {
    echo "{$c->Field} ({$c->Type}) | Null: {$c->Null} | Key: {$c->Key} | Default: {$c->Default}\n";
}
