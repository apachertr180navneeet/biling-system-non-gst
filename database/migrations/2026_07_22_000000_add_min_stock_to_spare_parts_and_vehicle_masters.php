<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('spare_parts') && !Schema::hasColumn('spare_parts', 'min_stock')) {
            Schema::table('spare_parts', function (Blueprint $table) {
                $table->integer('min_stock')->default(0)->after('unit');
            });
        }

        if (Schema::hasTable('vehicle_masters') && !Schema::hasColumn('vehicle_masters', 'min_stock')) {
            Schema::table('vehicle_masters', function (Blueprint $table) {
                $table->integer('min_stock')->default(0)->after('ex_showroom_price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('spare_parts') && Schema::hasColumn('spare_parts', 'min_stock')) {
            Schema::table('spare_parts', function (Blueprint $table) {
                $table->dropColumn('min_stock');
            });
        }

        if (Schema::hasTable('vehicle_masters') && Schema::hasColumn('vehicle_masters', 'min_stock')) {
            Schema::table('vehicle_masters', function (Blueprint $table) {
                $table->dropColumn('min_stock');
            });
        }
    }
};
