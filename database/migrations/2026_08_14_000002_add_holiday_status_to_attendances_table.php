<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'half_day', 'leave', 'holiday') DEFAULT 'present'");
        } catch (\Throwable $e) {
            // Fallback for drivers that do not use MySQL ALTER TABLE syntax
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('status', 20)->default('present')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'half_day', 'leave') DEFAULT 'present'");
        } catch (\Throwable $e) {
            // No-op
        }
    }
};
