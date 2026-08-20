<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reels', function (Blueprint $table) {
            $table->dropForeign(['reel_category_id']);
        });
        Schema::rename('reel_categories', 'reel_brands');
        Schema::table('reels', function (Blueprint $table) {
            $table->renameColumn('reel_category_id', 'reel_brand_id');
        });
        Schema::table('reels', function (Blueprint $table) {
            $table->foreign('reel_brand_id')->references('id')->on('reel_brands')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reels', function (Blueprint $table) {
            $table->dropForeign(['reel_brand_id']);
        });
        Schema::table('reels', function (Blueprint $table) {
            $table->renameColumn('reel_brand_id', 'reel_category_id');
        });
        Schema::rename('reel_brands', 'reel_categories');
        Schema::table('reels', function (Blueprint $table) {
            $table->foreign('reel_category_id')->references('id')->on('reel_categories')->restrictOnDelete();
        });
    }
};
