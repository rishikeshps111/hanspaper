<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE production_list MODIFY real_id BIGINT UNSIGNED NULL');
        if (!Schema::hasColumn('production_list', 'reel_stock_id')) {
            Schema::table('production_list', function (Blueprint $table) {
                $table->foreignId('reel_stock_id')->nullable()->after('real_id')->constrained('reel_stocks')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('reel_stock_usages')) {
            Schema::create('reel_stock_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('production_id')->constrained('production_item_masters')->restrictOnDelete();
                // Legacy production_list cannot be referenced by a modern FK on all installations.
                $table->unsignedBigInteger('production_list_id')->nullable()->index();
                $table->foreignId('reel_stock_id')->constrained('reel_stocks')->restrictOnDelete();
                $table->string('source_status', 20);
                $table->string('resulting_status', 20);
                $table->decimal('source_width', 12, 3);
                $table->decimal('output_roll_width', 12, 3);
                $table->decimal('consumed_length', 12, 3);
                $table->unsignedInteger('output_roll_count');
                $table->decimal('total_output_length', 15, 3);
                $table->decimal('width_waste', 12, 3)->default(0);
                $table->decimal('balance_before', 12, 3);
                $table->decimal('balance_after', 12, 3);
                $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['reel_stock_id', 'created_at']);
            });
        }

        DB::statement("ALTER TABLE reel_stock_movements MODIFY transaction_type ENUM('opening','sale','adjustment','transfer_in','transfer_out','return','consumption','production_usage') NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('reel_stock_usages');
        if (Schema::hasColumn('production_list', 'reel_stock_id')) {
            Schema::table('production_list', function (Blueprint $table) {
                $table->dropConstrainedForeignId('reel_stock_id');
            });
        }
        DB::statement("ALTER TABLE reel_stock_movements MODIFY transaction_type ENUM('opening','sale','adjustment','transfer_in','transfer_out','return','consumption') NOT NULL");
    }
};
