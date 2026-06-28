<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Check if column exists before trying to add it!
            if (!Schema::hasColumn('accounts', 'mobile_number')) {
                $table->string('mobile_number')->nullable()->after('group_type');
            }
            if (!Schema::hasColumn('accounts', 'address')) {
                $table->text('address')->nullable()->after('mobile_number');
            }
            if (!Schema::hasColumn('accounts', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('address');
            }
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['mobile_number', 'address', 'is_active']);
        });
    }
};