<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('sales_invoices', 'vehicle_sales_invoices');
    }

    public function down(): void
    {
        Schema::rename('vehicle_sales_invoices', 'sales_invoices');
    }
};
