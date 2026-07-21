<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicle_inventories', 'vehicle_master_id')) {
            Schema::table('vehicle_inventories', function (Blueprint $table) {
                $table->foreignId('vehicle_master_id')->nullable()->after('vehicle_po_id')->constrained('vehicle_masters')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('vehicle_inventories', function (Blueprint $table) {
            $table->dropForeign(['vehicle_master_id']);
            $table->dropColumn('vehicle_master_id');
        });
    }
};
