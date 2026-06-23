<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('opening_stock', 15, 2)->default(0.00)->after('name');
            $table->decimal('purchase_rate', 10, 2)->default(0.00)->after('opening_stock');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['opening_stock', 'purchase_rate']);
        });
    }
};