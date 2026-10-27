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
        // 1. Payment transactions: add discount and notes
        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_transactions', 'discount')) {
                    $table->decimal('discount', 12, 2)->default(0)->after('amount');
                }
                if (!Schema::hasColumn('payment_transactions', 'notes')) {
                    $table->text('notes')->nullable()->after('payment_mode');
                }
            });
        }

        // 2. Vehicle sales invoices: add payment_discount
        if (Schema::hasTable('vehicle_sales_invoices')) {
            Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicle_sales_invoices', 'payment_discount')) {
                    $table->decimal('payment_discount', 12, 2)->default(0)->after('discount');
                }
            });
        }

        // 3. Part sales invoices: add payment_discount
        if (Schema::hasTable('part_sales_invoices')) {
            Schema::table('part_sales_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('part_sales_invoices', 'payment_discount')) {
                    $table->decimal('payment_discount', 12, 2)->default(0)->after('received_amount');
                }
            });
        }

        // 4. Purchase orders: add payment_discount
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_orders', 'payment_discount')) {
                    $table->decimal('payment_discount', 12, 2)->default(0)->after('received_amount');
                }
            });
        }

        // 5. Vehicle purchase orders: add payment_discount
        if (Schema::hasTable('vehicle_purchase_orders')) {
            Schema::table('vehicle_purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicle_purchase_orders', 'payment_discount')) {
                    $table->decimal('payment_discount', 12, 2)->default(0)->after('received_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                if (Schema::hasColumn('payment_transactions', 'discount')) {
                    $table->dropColumn('discount');
                }
                if (Schema::hasColumn('payment_transactions', 'notes')) {
                    $table->dropColumn('notes');
                }
            });
        }

        if (Schema::hasTable('vehicle_sales_invoices')) {
            Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('vehicle_sales_invoices', 'payment_discount')) {
                    $table->dropColumn('payment_discount');
                }
            });
        }

        if (Schema::hasTable('part_sales_invoices')) {
            Schema::table('part_sales_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('part_sales_invoices', 'payment_discount')) {
                    $table->dropColumn('payment_discount');
                }
            });
        }

        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_orders', 'payment_discount')) {
                    $table->dropColumn('payment_discount');
                }
            });
        }

        if (Schema::hasTable('vehicle_purchase_orders')) {
            Schema::table('vehicle_purchase_orders', function (Blueprint $table) {
                if (Schema::hasColumn('vehicle_purchase_orders', 'payment_discount')) {
                    $table->dropColumn('payment_discount');
                }
            });
        }
    }
};
