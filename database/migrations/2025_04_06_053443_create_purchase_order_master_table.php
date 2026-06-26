<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderMasterTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order_master', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_order_id', 50);
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('cascade');

            $table->date('po_date');
            $table->date('due_date');
            $table->text('purchase_order_remarks')->nullable();

            $table->enum('purchase_order_status', [
                'Pending',
                'Processing',
                'Cancelled',
                'Completed',
                'Production',
                'Dispatched',
                'Ready to Dispatch',
            ])->default('Pending');

            $table->enum('mode_of_dispatch', [
                'Self Pickup',
                'Courier',
                'Delivery Vehicle'
            ])->default('Self Pickup');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_master');
    }
}
