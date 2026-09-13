<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (!Schema::hasColumn('sales_movements', 'tip_amount')) {
    Schema::table('sales_movements', function ($table) {
        $table->decimal('tip_amount', 12, 2)->default(0.00);
    });
    echo "Added tip_amount to sales_movements\n";
} else {
    echo "sales_movements already has tip_amount\n";
}

if (!Schema::hasColumn('order_movements', 'tip_amount')) {
    Schema::table('order_movements', function ($table) {
        $table->decimal('tip_amount', 12, 2)->default(0.00);
    });
    echo "Added tip_amount to order_movements\n";
} else {
    echo "order_movements already has tip_amount\n";
}
