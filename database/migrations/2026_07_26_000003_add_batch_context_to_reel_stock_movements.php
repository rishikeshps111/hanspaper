<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reel_stock_movements', function (Blueprint $table) {
            $table->uuid('batch_uuid')->nullable()->after('id')->index();
            $table->string('stock_status', 20)->nullable()->after('transaction_type');
        });
    }

    public function down(): void
    {
        Schema::table('reel_stock_movements', function (Blueprint $table) {
            $table->dropIndex(['batch_uuid']);
            $table->dropColumn(['batch_uuid', 'stock_status']);
        });
    }
};
