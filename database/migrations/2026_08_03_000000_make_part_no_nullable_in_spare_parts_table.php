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
            DB::statement("ALTER TABLE spare_parts MODIFY part_no VARCHAR(100) NULL DEFAULT NULL");
        } else {
            Schema::table('spare_parts', function (Blueprint $table) {
                $table->string('part_no')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE spare_parts MODIFY part_no VARCHAR(100) NOT NULL");
        } else {
            Schema::table('spare_parts', function (Blueprint $table) {
                $table->string('part_no')->nullable(false)->change();
            });
        }
    }
};
