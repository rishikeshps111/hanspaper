<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reel_stock_corrections', function (Blueprint $table) {
            $table->foreignId('original_reel_id')->nullable()->after('stock_batch_uuid')->constrained('reels')->restrictOnDelete();
            $table->foreignId('original_reel_provider_id')->nullable()->after('original_reel_id')->constrained('reel_providers')->restrictOnDelete();
            $table->foreignId('original_reel_warehouse_id')->nullable()->after('original_reel_provider_id')->constrained('reel_warehouses')->restrictOnDelete();
        });

        DB::table('reel_stock_corrections')->orderBy('id')->chunkById(100, function ($corrections) {
            foreach ($corrections as $correction) {
                DB::table('reel_stock_corrections')->where('id', $correction->id)->update([
                    'original_reel_id' => $correction->reel_id,
                    'original_reel_provider_id' => $correction->reel_provider_id,
                    'original_reel_warehouse_id' => $correction->reel_warehouse_id,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('reel_stock_corrections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('original_reel_warehouse_id');
            $table->dropConstrainedForeignId('original_reel_provider_id');
            $table->dropConstrainedForeignId('original_reel_id');
        });
    }
};
