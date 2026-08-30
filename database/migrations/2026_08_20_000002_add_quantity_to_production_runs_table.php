<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->decimal('production_quantity', 12, 3)->after('roll_length');
        });
    }

    public function down(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->dropColumn('production_quantity');
        });
    }
};
