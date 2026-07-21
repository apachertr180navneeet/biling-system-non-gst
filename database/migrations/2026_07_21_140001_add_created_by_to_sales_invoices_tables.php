<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicle_sales_invoices', 'created_by')) {
            Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('part_sales_invoices', 'created_by')) {
            Schema::table('part_sales_invoices', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        Schema::table('part_sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
