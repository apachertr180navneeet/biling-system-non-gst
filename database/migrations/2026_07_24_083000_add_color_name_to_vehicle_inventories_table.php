<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicle_inventories', 'color_name')) {
            Schema::table('vehicle_inventories', function (Blueprint $table) {
                $table->string('color_name')->nullable()->after('engine_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicle_inventories', 'color_name')) {
            Schema::table('vehicle_inventories', function (Blueprint $table) {
                $table->dropColumn('color_name');
            });
        }
    }
};
