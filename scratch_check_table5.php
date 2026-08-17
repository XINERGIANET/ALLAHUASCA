<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DB: " . config('database.connections.mysql.database') . "\n";
$tables = \Illuminate\Support\Facades\DB::table('tables')->get();
echo "TABLES COUNT: " . $tables->count() . "\n\n";

foreach ($tables as $t) {
    $lock = \Illuminate\Support\Facades\Cache::get("table_draft_lock:{$t->id}");
    $pendingOrder = \App\Models\OrderMovement::with('movement')->where('table_id', $t->id)->whereIn('status', ['PENDIENTE', 'P'])->first();
    echo "ID: {$t->id} | Name: '{$t->name}' | Situation: '{$t->situation}' | Lock: " . json_encode($lock) . " | PendingOrderUser: " . ($pendingOrder ? ($pendingOrder->movement?->user_id ?? 'no_user') : 'NONE') . "\n";
}
