<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->string('advance_number')->unique();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('advance_date');
            $table->decimal('amount', 10, 2);
            $table->decimal('deducted_amount', 10, 2)->default(0.00);
            $table->decimal('remaining_amount', 10, 2);
            $table->string('payment_mode')->default('Cash');
            $table->enum('status', ['pending', 'partially_deducted', 'fully_deducted', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_advances');
    }
};
