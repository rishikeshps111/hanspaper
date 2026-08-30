<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE production_runs MODIFY production_quantity DECIMAL(12,3) NULL');
        Schema::table('production_list', function (Blueprint $table) {
            $table->decimal('actual_quantity', 12, 3)->nullable()->after('quantity');
            $table->decimal('excess_stock_quantity', 12, 3)->default(0)->after('actual_quantity');
        });
        Schema::table('reel_stock_usages', function (Blueprint $table) {
            $table->decimal('remaining_output_length', 14, 3)->default(0)->after('balance_after');
            $table->decimal('physical_remaining_length', 14, 3)->default(0)->after('remaining_output_length');
            $table->decimal('wastage_output_length', 14, 3)->default(0)->after('physical_remaining_length');
            $table->decimal('physical_wastage_length', 14, 3)->default(0)->after('wastage_output_length');
        });
    }

    public function down(): void
    {
        Schema::table('reel_stock_usages', fn (Blueprint $table) => $table->dropColumn(['remaining_output_length','physical_remaining_length','wastage_output_length','physical_wastage_length']));
        Schema::table('production_list', fn (Blueprint $table) => $table->dropColumn(['actual_quantity','excess_stock_quantity']));
        DB::statement('ALTER TABLE production_runs MODIFY production_quantity DECIMAL(12,3) NOT NULL');
    }
};
