<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('spare_parts') && Schema::hasColumn('spare_parts', 'unit')) {
            Schema::table('spare_parts', function (Blueprint $table) {
                $table->dropColumn('unit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('spare_parts') && !Schema::hasColumn('spare_parts', 'unit')) {
            Schema::table('spare_parts', function (Blueprint $table) {
                $table->string('unit', 10)->default('pcs')->after('mrp');
            });
        }
    }
};
