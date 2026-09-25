<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reels', fn (Blueprint $table) => $table->string('width_unit', 8)->nullable());
        Schema::table('production_runs', fn (Blueprint $table) => $table->string('dimension_unit', 8)->nullable());
        Schema::table('reel_stock_usages', fn (Blueprint $table) => $table->string('dimension_unit', 8)->nullable());
    }

    public function down(): void
    {
        Schema::table('reel_stock_usages', fn (Blueprint $table) => $table->dropColumn('dimension_unit'));
        Schema::table('production_runs', fn (Blueprint $table) => $table->dropColumn('dimension_unit'));
        Schema::table('reels', fn (Blueprint $table) => $table->dropColumn('width_unit'));
    }
};
