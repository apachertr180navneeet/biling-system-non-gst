<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('part_sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_sales_invoice_id')->constrained('part_sales_invoices')->onDelete('cascade');
            $table->foreignId('spare_part_id')->constrained('spare_parts')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('rate', 12, 2);
            $table->decimal('tax_percentage', 5, 2)->default(18.00);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('serial_no_warranty_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_sales_invoice_items');
    }
};
