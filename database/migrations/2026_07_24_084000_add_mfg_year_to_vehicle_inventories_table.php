<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicle_inventories', 'mfg_year')) {
            Schema::table('vehicle_inventories', function (Blueprint $table) {
                $table->year('mfg_year')->nullable()->after('color_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicle_inventories', 'mfg_year')) {
            Schema::table('vehicle_inventories', function (Blueprint $table) {
                $table->dropColumn('mfg_year');
            });
        }
    }
};
