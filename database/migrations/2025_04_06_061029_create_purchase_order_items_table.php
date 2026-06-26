<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            // Foreign key to purchase_order_master
            $table->unsignedBigInteger('purchase_order_id');
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('purchase_order_master')
                  ->onDelete('cascade');

            // Foreign key to items
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                  ->references('id')
                  ->on('items')
                  ->onDelete('restrict');

            $table->text('product_remarks')->nullable();
            $table->integer('quantity')->default(0);

            $table->enum('status', [
                'Push To Production',
                'Ready to Dispatch'
            ])->default('Push To Production');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
}
