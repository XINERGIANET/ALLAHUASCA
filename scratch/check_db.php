<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- sales_movements ---\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('sales_movements'));

echo "\n--- sales_movement_details ---\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('sales_movement_details'));

echo "\n--- movements ---\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('movements'));
