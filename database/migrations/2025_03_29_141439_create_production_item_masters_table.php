<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('production_item_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('remarks')->nullable();
            $table->enum('status', ['Pending', 'Completed', 'Progress'])->default('Pending');
            $table->enum('production_type', ['Purchaseorder', 'Stock']);
            $table->foreignId('purchaseorder_id')->nullable()->constrained('purchaseorders')->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('cascade');
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
        });
        Schema::dropIfExists('production_item_masters');
    }
};
