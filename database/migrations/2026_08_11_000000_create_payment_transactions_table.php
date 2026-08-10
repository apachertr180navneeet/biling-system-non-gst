<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type', ['sales', 'purchase']);
            $table->string('bill_type'); // vehicle_sales, part_sales, vehicle_purchase, part_purchase
            $table->unsignedBigInteger('bill_id'); // ID of invoice or purchase order
            $table->string('party_type')->default('customer'); // customer, supplier
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name')->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->string('payment_mode')->default('Cash');
            $table->enum('type', ['payment', 'rollback'])->default('payment');
            $table->string('rollback_reason')->nullable();
            $table->unsignedBigInteger('reversed_payment_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['bill_type', 'bill_id']);
            $table->index(['party_type', 'party_id']);
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
