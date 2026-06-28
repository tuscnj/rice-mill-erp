<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('mobile_number')->nullable()->after('group_type');
            $table->text('address')->nullable()->after('mobile_number');
            $table->boolean('is_active')->default(true)->after('address');
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['mobile_number', 'address', 'is_active']);
        });
    }
};