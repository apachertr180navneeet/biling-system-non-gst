<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_no')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('hsn_code', 8)->nullable();
            $table->boolean('is_gst_applicable')->default(true);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('mrp', 12, 2)->default(0);
            $table->string('unit', 10)->default('pcs');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
