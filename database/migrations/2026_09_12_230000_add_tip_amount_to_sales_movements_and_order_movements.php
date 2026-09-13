<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales_movements', 'tip_amount')) {
            Schema::table('sales_movements', function (Blueprint $table) {
                $table->decimal('tip_amount', 12, 2)->default(0.00)->after('total');
            });
        }

        if (!Schema::hasColumn('order_movements', 'tip_amount')) {
            Schema::table('order_movements', function (Blueprint $table) {
                $table->decimal('tip_amount', 12, 2)->default(0.00)->after('total');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_movements', 'tip_amount')) {
            Schema::table('sales_movements', function (Blueprint $table) {
                $table->dropColumn('tip_amount');
            });
        }

        if (Schema::hasColumn('order_movements', 'tip_amount')) {
            Schema::table('order_movements', function (Blueprint $table) {
                $table->dropColumn('tip_amount');
            });
        }
    }
};
