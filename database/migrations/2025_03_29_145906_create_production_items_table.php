<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('production_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained('productions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('requested_qty');
            $table->integer('entered_qty')->default(0);
            $table->integer('remaining_qty')->default(0);
            $table->enum('status', ['Pending', 'Completed'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('production_item_masters', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['purchaseorder_id']);
            $table->dropForeign(['sale_id']);
            $table->dropColumn(['requested_by', 'approved_by', 'remarks', 'status', 'production_type', 'purchaseorder_id', 'sale_id', 'created_at', 'updated_at']);
        });
        Schema::dropIfExists('production_item_masters');
    }
};