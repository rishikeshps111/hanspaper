<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reel_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('stock_code', 255)->unique();
            $table->foreignId('reel_id')->constrained('reels')->restrictOnDelete();
            $table->foreignId('reel_warehouse_id')->constrained('reel_warehouses')->restrictOnDelete();
            $table->decimal('original_length', 12, 3);
            $table->decimal('balance_length', 12, 3);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->enum('status', ['full', 'bit', 'finished', 'sold'])->default('full');
            $table->date('received_at');
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['reel_id', 'reel_warehouse_id', 'status']);
        });

        Schema::create('reel_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_code', 30)->nullable()->unique();
            $table->foreignId('customer_id')->constrained('parties')->restrictOnDelete();
            $table->date('sale_date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reel_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reel_sale_id')->constrained('reel_sales')->cascadeOnDelete();
            $table->foreignId('reel_stock_id')->constrained('reel_stocks')->restrictOnDelete();
            $table->decimal('length', 12, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('balance_before', 12, 3);
            $table->decimal('balance_after', 12, 3);
            $table->timestamps();
        });

        Schema::create('reel_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reel_stock_id')->constrained('reel_stocks')->cascadeOnDelete();
            $table->enum('transaction_type', ['opening', 'sale', 'adjustment', 'transfer_in', 'transfer_out', 'return', 'consumption']);
            $table->decimal('length', 12, 3);
            $table->decimal('balance_before', 12, 3);
            $table->decimal('balance_after', 12, 3);
            $table->nullableMorphs('reference');
            $table->foreignId('customer_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('reel_warehouse_id')->constrained('reel_warehouses')->restrictOnDelete();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['transaction_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reel_stock_movements');
        Schema::dropIfExists('reel_sale_items');
        Schema::dropIfExists('reel_sales');
        Schema::dropIfExists('reel_stocks');
    }
};
