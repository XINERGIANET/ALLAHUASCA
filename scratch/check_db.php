<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movement;
use App\Models\Branch;
use App\Services\ApisunatService;

$branch = Branch::first();
echo "Branch RUC: " . ($branch ? $branch->ruc : 'none') . "\n";

$boletas = Movement::where('document_type_id', 2)
    ->where('movement_type_id', 2)
    ->orderBy('moved_at')
    ->orderBy('id')
    ->get();

echo "Total boletas: " . $boletas->count() . "\n";
echo "Linked: " . $boletas->whereNotNull('electronic_invoice_external_id')->count() . "\n";

$byNum = [];
foreach ($boletas as $b) {
    $num = (int)$b->number;
    $byNum[$num][] = $b;
}

echo "Duplicate numbers count in DB:\n";
foreach ($byNum as $num => $list) {
    if (count($list) > 1) {
        echo "Correlative $num: " . count($list) . " movements: " . implode(', ', array_map(fn($m) => "ID: {$m->id} (num: {$m->number}, ext_id: {$m->electronic_invoice_external_id}, total: {$m->total})", $list)) . "\n";
    }
}
