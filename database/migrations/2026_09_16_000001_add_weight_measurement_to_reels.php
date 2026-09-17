<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reel_types', fn (Blueprint $table) => $table->enum('volume', ['length', 'weight'])->default('length'));
        Schema::table('reels', function (Blueprint $table) {
            $table->decimal('length', 12, 2)->nullable()->change();
            $table->decimal('weight_kg', 12, 3)->nullable();
        });
        Schema::table('reel_stocks', function (Blueprint $table) {
            $table->decimal('original_weight_kg', 12, 3)->nullable();
            $table->decimal('balance_weight_kg', 12, 3)->nullable();
        });
        foreach (['reel_stock_movements', 'reel_sale_items'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('weight_kg', 12, 3)->nullable();
                $table->decimal('weight_before_kg', 12, 3)->nullable();
                $table->decimal('weight_after_kg', 12, 3)->nullable();
            });
        }
        Schema::table('reel_stock_usages', function (Blueprint $table) {
            $table->decimal('weight_before_kg', 12, 3)->nullable();
            $table->decimal('consumed_weight_kg', 12, 3)->nullable();
            $table->decimal('remaining_weight_kg', 12, 3)->nullable();
            $table->decimal('wastage_weight_kg', 12, 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reel_stock_usages', fn (Blueprint $table) => $table->dropColumn(['weight_before_kg', 'consumed_weight_kg', 'remaining_weight_kg', 'wastage_weight_kg']));
        foreach (['reel_stock_movements', 'reel_sale_items'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['weight_kg', 'weight_before_kg', 'weight_after_kg']));
        }
        Schema::table('reel_stocks', fn (Blueprint $table) => $table->dropColumn(['original_weight_kg', 'balance_weight_kg']));
        Schema::table('reels', fn (Blueprint $table) => $table->dropColumn('weight_kg'));
        Schema::table('reel_types', fn (Blueprint $table) => $table->dropColumn('volume'));
    }
};
