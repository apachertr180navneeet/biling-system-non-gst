<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name');
            $table->integer('customer_age')->nullable();
            $table->string('customer_occupation')->nullable();
            $table->string('customer_mobile')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_residence_phone')->nullable();

            $table->foreignId('vehicle_inventory_id')->constrained('vehicle_inventories');
            $table->decimal('rate', 12, 2);
            $table->decimal('sub_total', 12, 2);
            $table->decimal('cgst_rate', 5, 2)->default(2.50);
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->default(2.50);
            $table->decimal('sgst_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('nemmp_incentive', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);

            $table->string('payment_mode')->nullable();
            $table->text('warranty_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};
