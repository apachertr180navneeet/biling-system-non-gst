<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->string('supplier_name')->nullable();
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vehicle_po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_po_id')->constrained('vehicle_purchase_orders')->cascadeOnDelete();
            $table->string('vehicle_description');
            $table->string('color_name')->nullable();
            $table->year('mfg_year')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->integer('received_quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('vehicle_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_po_id')->nullable()->constrained('vehicle_purchase_orders')->nullOnDelete();
            $table->string('vehicle_description');
            $table->string('chassis_number')->nullable();
            $table->string('engine_number')->nullable();
            $table->string('color_name')->nullable();
            $table->year('mfg_year')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->string('status')->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_inventories');
        Schema::dropIfExists('vehicle_po_items');
        Schema::dropIfExists('vehicle_purchase_orders');
    }
};
