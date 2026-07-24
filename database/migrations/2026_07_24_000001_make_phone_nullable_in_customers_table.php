<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE customers MODIFY phone VARCHAR(255) NULL DEFAULT NULL");
        } else {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('phone')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE customers MODIFY phone VARCHAR(255) NOT NULL");
        } else {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('phone')->nullable(false)->change();
            });
        }
    }
};
