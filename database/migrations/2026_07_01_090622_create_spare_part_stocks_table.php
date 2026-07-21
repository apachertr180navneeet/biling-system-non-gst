<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('spare_part_stocks')) {
            Schema::create('spare_part_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('spare_part_id')->unique()->constrained('spare_parts')->cascadeOnDelete();
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                $table->integer('quantity')->default(0);
                $table->integer('min_quantity')->default(0);
                $table->string('location')->nullable();
                $table->decimal('purchase_price', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_part_stocks');
    }
};
