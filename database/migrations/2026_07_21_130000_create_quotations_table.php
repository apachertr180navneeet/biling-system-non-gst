<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'vehicle' or 'parts'
            $table->string('quotation_number')->unique();
            $table->date('quotation_date');
            
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_mobile')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_gstin', 15)->nullable();
            $table->string('customer_pan', 10)->nullable();
            $table->string('place_of_supply')->default('Rajasthan');
            $table->string('tax_regime', 10)->default('cgst_sgst');

            // Vehicle specific columns
            $table->foreignId('vehicle_master_id')->nullable()->constrained('vehicle_masters')->nullOnDelete();
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('cgst_rate', 5, 2)->default(2.50);
            $table->decimal('sgst_rate', 5, 2)->default(2.50);
            $table->decimal('igst_rate', 5, 2)->default(5.00);
            $table->decimal('nemmp_incentive', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);

            // Calculations / Summaries
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_amount', 12, 2)->default(0);
            $table->decimal('igst_amount', 12, 2)->default(0);
            $table->decimal('round_off', 5, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
