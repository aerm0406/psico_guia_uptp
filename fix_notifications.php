<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$notifications = DB::table('notifications')->get();
foreach($notifications as $n) {
    $data = json_decode($n->data, true);
    if(isset($data['message'])) {
        $data['body'] = $data['message'];
        unset($data['message']);
        DB::table('notifications')->where('id', $n->id)->update(['data' => json_encode($data)]);
        echo "Fixed notification {$n->id}\n";
    }
}
echo "Done.\n";
