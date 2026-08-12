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
        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->string('slip_number')->unique();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->unsignedInteger('month');
            $table->unsignedInteger('year');
            $table->unsignedInteger('total_days')->default(30);
            $table->decimal('present_days', 4, 1)->default(0.0);
            $table->decimal('absent_days', 4, 1)->default(0.0);
            $table->unsignedInteger('half_days')->default(0);
            $table->decimal('paid_leaves', 4, 1)->default(0.0);
            $table->decimal('basic_salary', 10, 2)->default(0.00);
            $table->decimal('earned_salary', 10, 2)->default(0.00);
            $table->decimal('allowances', 10, 2)->default(0.00);
            $table->decimal('deductions', 10, 2)->default(0.00);
            $table->decimal('net_salary', 10, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'paid'])->default('paid');
            $table->date('payment_date')->nullable();
            $table->string('payment_mode')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_slips');
    }
};
