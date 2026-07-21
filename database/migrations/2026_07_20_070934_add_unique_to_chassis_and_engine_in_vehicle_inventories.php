<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_inventories', function (Blueprint $table) {
            $table->string('chassis_number')->nullable(false)->change();
            $table->string('engine_number')->nullable(false)->change();
            $table->unique('chassis_number');
            $table->unique('engine_number');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_inventories', function (Blueprint $table) {
            $table->dropUnique(['chassis_number']);
            $table->dropUnique(['engine_number']);
            $table->string('chassis_number')->nullable()->change();
            $table->string('engine_number')->nullable()->change();
        });
    }
};
