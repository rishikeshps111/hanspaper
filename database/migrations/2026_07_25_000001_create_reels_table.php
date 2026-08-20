<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reel_category_id')->constrained('reel_categories')->restrictOnDelete();
            $table->foreignId('reel_provider_id')->constrained('reel_providers')->restrictOnDelete();
            $table->foreignId('reel_type_id')->constrained('reel_types')->restrictOnDelete();
            $table->foreignId('reel_gsm_id')->constrained('reel_gsms')->restrictOnDelete();
            $table->decimal('width', 12, 3);
            $table->decimal('length', 12, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->text('remarks');
            $table->timestamps();

            $table->index(['reel_category_id', 'reel_provider_id', 'reel_type_id', 'reel_gsm_id'], 'reels_setting_filters_index');
            $table->index(['is_active', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reels');
    }
};
