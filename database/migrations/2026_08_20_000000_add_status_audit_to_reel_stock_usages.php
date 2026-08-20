<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reel_stock_usages', function (Blueprint $table) {
            $table->string('calculated_status', 20)->nullable()->after('source_status');
            $table->string('status_selection_type', 20)->default('automatic')->after('resulting_status');
        });
    }

    public function down(): void
    {
        Schema::table('reel_stock_usages', function (Blueprint $table) {
            $table->dropColumn(['calculated_status', 'status_selection_type']);
        });
    }
};
