<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
            $table->string('tax_regime', 10)->default('cgst_sgst')->after('finance_name');
            $table->decimal('igst_amount', 12, 2)->default(0)->after('sgst_amount');
        });

        Schema::table('part_sales_invoices', function (Blueprint $table) {
            $table->string('tax_regime', 10)->default('cgst_sgst')->after('place_of_supply');
            $table->decimal('igst_amount', 12, 2)->default(0)->after('sgst_amount');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
            $table->dropColumn(['tax_regime', 'igst_amount']);
        });

        Schema::table('part_sales_invoices', function (Blueprint $table) {
            $table->dropColumn(['tax_regime', 'igst_amount']);
        });
    }
};
