<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reel_sales', function (Blueprint $table) {
            $table->boolean('is_gst_applicable')->default(false)->after('discount');
            $table->decimal('sgst_percentage', 5, 2)->default(0)->after('is_gst_applicable');
            $table->decimal('sgst_amount', 15, 2)->default(0)->after('sgst_percentage');
            $table->decimal('cgst_percentage', 5, 2)->default(0)->after('sgst_amount');
            $table->decimal('cgst_amount', 15, 2)->default(0)->after('cgst_percentage');
        });

        Schema::table('reel_sale_items', function (Blueprint $table) {
            $table->decimal('discount', 15, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('reel_sale_items', function (Blueprint $table) {
            $table->dropColumn('discount');
        });

        Schema::table('reel_sales', function (Blueprint $table) {
            $table->dropColumn([
                'is_gst_applicable', 'sgst_percentage', 'sgst_amount',
                'cgst_percentage', 'cgst_amount',
            ]);
        });
    }
};
