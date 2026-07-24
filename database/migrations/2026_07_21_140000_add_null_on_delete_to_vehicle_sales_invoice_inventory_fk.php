<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $fkExists = [];
        if (DB::getDriverName() === 'mysql') {
            $fkExists = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE CONSTRAINT_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'vehicle_sales_invoices' 
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME = 'vehicle_sales_invoices_vehicle_inventory_id_foreign'
            ");
        }

        Schema::table('vehicle_sales_invoices', function (Blueprint $table) use ($fkExists) {
            if (!empty($fkExists)) {
                $table->dropForeign(['vehicle_inventory_id']);
            }
            $table->unsignedBigInteger('vehicle_inventory_id')->nullable()->change();
            $table->foreign('vehicle_inventory_id')->references('id')->on('vehicle_inventories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['vehicle_inventory_id']);
            $table->foreign('vehicle_inventory_id')->references('id')->on('vehicle_inventories')->cascadeOnDelete();
        });
    }
};
