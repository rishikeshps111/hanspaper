<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cores', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('size_mm', 10, 2);
            $table->unsignedInteger('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('core_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('core_id')->constrained('cores')->restrictOnDelete();
            $table->string('transaction_type', 30);
            $table->integer('quantity_change');
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');
            $table->nullableMorphs('reference');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('production_runs', function (Blueprint $table) {
            $table->foreignId('core_id')->nullable()->after('production_user_id')->constrained('cores')->restrictOnDelete();
            $table->unsignedInteger('core_quantity')->nullable()->after('production_quantity');
        });
        Schema::table('production_list', function (Blueprint $table) {
            $table->foreignId('core_id')->nullable()->after('reel_stock_id')->constrained('cores')->nullOnDelete();
            $table->unsignedInteger('core_quantity')->nullable()->after('core_id');
        });
    }

    public function down(): void
    {
        Schema::table('production_list', function (Blueprint $table) {
            $table->dropConstrainedForeignId('core_id');
            $table->dropColumn('core_quantity');
        });
        Schema::table('production_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('core_id');
            $table->dropColumn('core_quantity');
        });
        Schema::dropIfExists('core_stock_movements');
        Schema::dropIfExists('cores');
    }
};
