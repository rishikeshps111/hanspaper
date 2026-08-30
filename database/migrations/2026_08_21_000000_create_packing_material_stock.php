<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_materials', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['box', 'cover']);
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('capacity')->nullable();
            $table->decimal('size_mm', 10, 2)->nullable();
            $table->decimal('length_mm', 10, 2)->nullable();
            $table->decimal('width_mm', 10, 2)->nullable();
            $table->decimal('height_mm', 10, 2)->nullable();
            $table->string('cover_type')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['type', 'is_active']);
        });
        Schema::create('packing_material_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_material_id')->constrained('packing_materials')->restrictOnDelete();
            $table->string('transaction_type', 30);
            $table->integer('quantity_change');
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->index(['reference_type', 'reference_id'], 'packing_material_reference_index');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::table('packing_list', function (Blueprint $table) {
            $table->foreignId('packing_box_id')->nullable()->after('quantity')->constrained('packing_materials')->nullOnDelete();
            $table->unsignedInteger('packing_box_quantity')->nullable()->after('packing_box_id');
            $table->foreignId('packing_cover_id')->nullable()->after('packing_box_quantity')->constrained('packing_materials')->nullOnDelete();
            $table->unsignedInteger('packing_cover_quantity')->nullable()->after('packing_cover_id');
        });
    }

    public function down(): void
    {
        Schema::table('packing_list', function (Blueprint $table) {
            $table->dropConstrainedForeignId('packing_box_id'); $table->dropColumn('packing_box_quantity');
            $table->dropConstrainedForeignId('packing_cover_id'); $table->dropColumn('packing_cover_quantity');
        });
        Schema::dropIfExists('packing_material_stock_movements');
        Schema::dropIfExists('packing_materials');
    }
};
