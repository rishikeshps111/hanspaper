<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barcode_work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->foreignId('customer_id')->constrained('parties')->restrictOnDelete();
            $table->unsignedBigInteger('representative_id')->index();
            $table->date('work_order_date');
            $table->date('due_date');
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('barcode_work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barcode_work_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number_of_rolls');
            $table->unsignedInteger('stickers_per_roll');
            $table->decimal('sticker_length', 10, 2);
            $table->decimal('sticker_width', 10, 2);
            $table->string('type', 20);
            $table->string('gap', 20);
            $table->decimal('gap_mm', 10, 2)->nullable();
            $table->boolean('is_printing')->default(false);
            $table->string('printing_colors', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcode_work_order_items');
        Schema::dropIfExists('barcode_work_orders');
    }
};
