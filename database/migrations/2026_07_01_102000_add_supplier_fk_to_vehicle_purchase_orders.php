<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('supplier_name');
        });
        Schema::table('vehicle_purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('po_number')->constrained('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
        Schema::table('vehicle_purchase_orders', function (Blueprint $table) {
            $table->string('supplier_name')->nullable()->after('po_number');
        });
    }
};
