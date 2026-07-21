<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_masters', function (Blueprint $table) {
            $table->string('battery_type')->nullable();
            $table->string('battery_make')->nullable();
        });

        Schema::table('vehicle_inventories', function (Blueprint $table) {
            $table->string('motor_number')->nullable();
            $table->string('battery_number')->nullable();
            $table->string('charger_number')->nullable();
            $table->string('controller_number')->nullable();
            $table->string('convertor_number')->nullable();
            $table->string('manual_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_masters', function (Blueprint $table) {
            $table->dropColumn(['battery_type', 'battery_make']);
        });

        Schema::table('vehicle_inventories', function (Blueprint $table) {
            $table->dropColumn([
                'motor_number',
                'battery_number',
                'charger_number',
                'controller_number',
                'convertor_number',
                'manual_number'
            ]);
        });
    }
};
