<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('received_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('balance', 12, 2)->default(0)->after('received_amount');
        });

        Schema::table('vehicle_purchase_orders', function (Blueprint $table) {
            $table->decimal('received_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('balance', 12, 2)->default(0)->after('received_amount');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['received_amount', 'balance']);
        });

        Schema::table('vehicle_purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['received_amount', 'balance']);
        });
    }
};
