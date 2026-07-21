<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
            $table->decimal('received_amount', 12, 2)->default(0)->after('grand_total');
            $table->decimal('balance', 12, 2)->default(0)->after('received_amount');
            $table->decimal('previous_balance', 12, 2)->default(0)->after('balance');
            $table->decimal('current_balance', 12, 2)->default(0)->after('previous_balance');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_sales_invoices', function (Blueprint $table) {
            $table->dropColumn(['received_amount', 'balance', 'previous_balance', 'current_balance']);
        });
    }
};
