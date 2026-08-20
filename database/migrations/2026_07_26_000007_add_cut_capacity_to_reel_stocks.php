<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reel_stocks', function (Blueprint $table) {
            $table->decimal('cut_width', 12, 3)->nullable()->after('balance_length');
        });
        Schema::table('reel_stock_usages', function (Blueprint $table) {
            $table->decimal('roll_length', 12, 3)->default(0)->after('output_roll_width');
            $table->decimal('production_quantity', 15, 3)->default(0)->after('roll_length');
        });
    }

    public function down(): void
    {
        Schema::table('reel_stock_usages', function (Blueprint $table) {
            $table->dropColumn(['roll_length', 'production_quantity']);
        });
        Schema::table('reel_stocks', function (Blueprint $table) {
            $table->dropColumn('cut_width');
        });
    }
};
