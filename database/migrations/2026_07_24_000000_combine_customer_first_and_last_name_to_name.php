<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'name')) {
                $table->string('name')->after('type')->nullable();
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE customers MODIFY phone VARCHAR(255) NULL");
            DB::statement("ALTER TABLE customers MODIFY name VARCHAR(255) NULL");
            DB::statement("ALTER TABLE customers MODIFY company_name VARCHAR(255) NULL");
            DB::statement("ALTER TABLE customers MODIFY email VARCHAR(255) NULL");
            DB::statement("ALTER TABLE customers MODIFY address TEXT NULL");
            DB::statement("ALTER TABLE customers MODIFY state VARCHAR(255) NULL");
            DB::statement("ALTER TABLE customers MODIFY gstin VARCHAR(15) NULL");
            DB::statement("ALTER TABLE customers MODIFY pan_no VARCHAR(10) NULL");
            DB::statement("ALTER TABLE customers MODIFY aadhaar_no VARCHAR(12) NULL");
        }

        // Combine first_name and last_name into name for existing rows
        if (Schema::hasColumn('customers', 'first_name') && Schema::hasColumn('customers', 'last_name')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement("UPDATE customers SET name = TRIM(COALESCE(first_name, '') || ' ' || COALESCE(last_name, ''))");
            } else {
                DB::statement("UPDATE customers SET name = TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))");
            }
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('customers', 'last_name')) {
                $table->dropColumn('last_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('type');
            $table->string('last_name')->nullable()->after('first_name');
        });

        // Populate first_name and last_name from name
        DB::statement("UPDATE customers SET first_name = SUBSTRING_INDEX(name, ' ', 1), last_name = CASE WHEN LOCATE(' ', name) > 0 THEN SUBSTRING(name, LOCATE(' ', name) + 1) ELSE '' END");

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
