<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Paddy, Rice, Broken Rice
            $table->string('category'); // 'Raw Material', 'Finished Goods', 'Byproduct', 'Wastage'
            $table->string('unit')->default('KG'); // KG, Bag, Quintal
            $table->decimal('current_stock', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};