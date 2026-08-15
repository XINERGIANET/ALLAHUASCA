<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('deleted_by_user_name', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['deleted_by_user_id']);
            $table->dropColumn(['deleted_by_user_id', 'deleted_by_user_name']);
        });
    }
};
