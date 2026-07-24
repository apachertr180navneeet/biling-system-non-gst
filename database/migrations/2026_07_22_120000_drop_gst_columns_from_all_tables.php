<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'gstin')) {
                $table->dropColumn('gstin');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'gstin')) {
                $table->dropColumn('gstin');
            }
        });

        Schema::table('quotations', function (Blueprint $table) {
            $cols = ['customer_gstin', 'tax_regime', 'cgst_rate', 'sgst_rate', 'igst_rate', 'cgst_amount', 'sgst_amount', 'igst_amount'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('quotations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $cols = ['tax_percentage', 'tax_amount', 'cgst_amount', 'sgst_amount', 'igst_amount'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('quotation_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
            $cols = ['tax_regime', 'cgst_rate', 'cgst_amount', 'sgst_rate', 'sgst_amount', 'igst_amount'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('vehicle_sales_invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('part_sales_invoices', function (Blueprint $table) {
            $cols = ['customer_gstin', 'tax_regime', 'cgst_amount', 'sgst_amount', 'igst_amount'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('part_sales_invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('part_sales_invoice_items', function (Blueprint $table) {
            $cols = ['tax_percentage', 'tax_amount'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('part_sales_invoice_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('hsn_sac_master', function (Blueprint $table) {
            $cols = ['gst_rate', 'cess_rate'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('hsn_sac_master', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('gstin', 15)->nullable()->after('state');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('gstin', 15)->nullable()->after('name');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('customer_gstin', 15)->nullable()->after('customer_mobile');
            $table->string('tax_regime', 20)->default('cgst_sgst')->after('customer_address');
            $table->decimal('cgst_rate', 5, 2)->default(0)->after('tax_regime');
            $table->decimal('sgst_rate', 5, 2)->default(0)->after('cgst_rate');
            $table->decimal('igst_rate', 5, 2)->default(0)->after('sgst_rate');
            $table->decimal('cgst_amount', 12, 2)->default(0)->after('discount');
            $table->decimal('sgst_amount', 12, 2)->default(0)->after('cgst_amount');
            $table->decimal('igst_amount', 12, 2)->default(0)->after('sgst_amount');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('rate');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percentage');
            $table->decimal('cgst_amount', 12, 2)->default(0)->after('tax_amount');
            $table->decimal('sgst_amount', 12, 2)->default(0)->after('cgst_amount');
            $table->decimal('igst_amount', 12, 2)->default(0)->after('sgst_amount');
        });

        Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
            $table->string('tax_regime', 20)->default('cgst_sgst')->after('payment_mode');
            $table->decimal('cgst_rate', 5, 2)->default(0)->after('tax_regime');
            $table->decimal('cgst_amount', 12, 2)->default(0)->after('cgst_rate');
            $table->decimal('sgst_rate', 5, 2)->default(0)->after('cgst_amount');
            $table->decimal('sgst_amount', 12, 2)->default(0)->after('sgst_rate');
            $table->decimal('igst_amount', 12, 2)->default(0)->after('sgst_amount');
        });

        Schema::table('part_sales_invoices', function (Blueprint $table) {
            $table->string('customer_gstin', 15)->nullable()->after('customer_mobile');
            $table->string('tax_regime', 20)->default('cgst_sgst')->after('payment_mode');
            $table->decimal('cgst_amount', 12, 2)->default(0)->after('taxable_amount');
            $table->decimal('sgst_amount', 12, 2)->default(0)->after('cgst_amount');
            $table->decimal('igst_amount', 12, 2)->default(0)->after('sgst_amount');
        });

        Schema::table('part_sales_invoice_items', function (Blueprint $table) {
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('rate');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percentage');
        });

        Schema::table('hsn_sac_master', function (Blueprint $table) {
            $table->decimal('gst_rate', 5, 2)->default(0)->after('unit');
            $table->decimal('cess_rate', 5, 2)->default(0)->after('gst_rate');
        });
    }
};
