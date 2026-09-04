<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reel_stocks MODIFY status ENUM('full','bit','finished','sold','voided') NOT NULL DEFAULT 'full'");

        Schema::create('reel_stock_corrections', function (Blueprint $table) {
            $table->id();
            $table->uuid('stock_batch_uuid')->index();
            $table->foreignId('reel_id')->constrained('reels')->restrictOnDelete();
            $table->foreignId('reel_provider_id')->constrained('reel_providers')->restrictOnDelete();
            $table->foreignId('reel_warehouse_id')->constrained('reel_warehouses')->restrictOnDelete();
            $table->unsignedInteger('previous_quantity');
            $table->unsignedInteger('corrected_quantity');
            $table->integer('quantity_change');
            $table->json('affected_stock_codes')->nullable();
            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reel_detail_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reel_id')->constrained('reels')->restrictOnDelete();
            $table->json('before_values');
            $table->json('after_values');
            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reel_detail_corrections');
        Schema::dropIfExists('reel_stock_corrections');
        DB::statement("UPDATE reel_stocks SET status = 'finished', is_active = 0 WHERE status = 'voided'");
        DB::statement("ALTER TABLE reel_stocks MODIFY status ENUM('full','bit','finished','sold') NOT NULL DEFAULT 'full'");
    }
};
