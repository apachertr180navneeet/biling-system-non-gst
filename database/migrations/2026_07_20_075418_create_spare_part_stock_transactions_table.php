<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spare_part_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            $table->enum('transaction_type', ['in', 'out']);
            $table->integer('quantity');
            $table->string('reference_no')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // Populate existing received purchase orders
        $items = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_order_items.received_quantity', '>', 0)
            ->select('purchase_order_items.spare_part_id', 'purchase_order_items.received_quantity', 'purchase_orders.order_number', 'purchase_orders.created_at', 'purchase_orders.updated_at')
            ->get();

        foreach ($items as $item) {
            DB::table('spare_part_stock_transactions')->insert([
                'spare_part_id' => $item->spare_part_id,
                'transaction_type' => 'in',
                'quantity' => $item->received_quantity,
                'reference_no' => $item->order_number,
                'notes' => 'Backfilled from Purchase Order receipt',
                'created_at' => $item->updated_at ?? $item->created_at ?? now(),
                'updated_at' => $item->updated_at ?? $item->created_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_part_stock_transactions');
    }
};

