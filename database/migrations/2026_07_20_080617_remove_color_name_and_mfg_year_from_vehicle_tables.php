<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Intentionally left empty - color_name and mfg_year are now handled
        // via separate alter migrations on the correct tables.
    }

    public function down(): void
    {
        //
    }
};
