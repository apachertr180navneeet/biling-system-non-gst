<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'model_maker_name')) {
                $table->string('model_maker_name')->nullable()->default('E- PASSENGER/ARZOO/PASSANGER');
            }
            if (!Schema::hasColumn('quotations', 'gross_weight')) {
                $table->string('gross_weight')->nullable()->default('60 KG');
            }
            if (!Schema::hasColumn('quotations', 'charging_time')) {
                $table->string('charging_time')->nullable()->default('3-4 HR');
            }
            if (!Schema::hasColumn('quotations', 'performance')) {
                $table->string('performance')->nullable()->default('HIGH SPEED 25 KM/HR');
            }
            if (!Schema::hasColumn('quotations', 'charger_output')) {
                $table->string('charger_output')->nullable()->default('DC 51V 105 AH (1 LITHIUM BATTERY)');
            }
            if (!Schema::hasColumn('quotations', 'motor_output')) {
                $table->string('motor_output')->nullable()->default('1200 W');
            }
            if (!Schema::hasColumn('quotations', 'seating_capacity')) {
                $table->string('seating_capacity')->nullable()->default('5');
            }
            if (!Schema::hasColumn('quotations', 'type_of_break')) {
                $table->string('type_of_break')->nullable()->default('DRUM BREAK');
            }
            if (!Schema::hasColumn('quotations', 'roof_top_abs')) {
                $table->string('roof_top_abs')->nullable()->default('YES');
            }
            if (!Schema::hasColumn('quotations', 'front_fiber_wind_shield')) {
                $table->string('front_fiber_wind_shield')->nullable()->default('YES');
            }
            if (!Schema::hasColumn('quotations', 'meter_type')) {
                $table->string('meter_type')->nullable()->default('DIGITAL');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                'model_maker_name',
                'gross_weight',
                'charging_time',
                'performance',
                'charger_output',
                'motor_output',
                'seating_capacity',
                'type_of_break',
                'roof_top_abs',
                'front_fiber_wind_shield',
                'meter_type',
            ], function($col) {
                return Schema::hasColumn('quotations', $col);
            });

            if (!empty($columnsToDrop)) {
                $table->dropColumn(array_values($columnsToDrop));
            }
        });
    }
};
