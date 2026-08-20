<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barcode_work_orders', function (Blueprint $table) {
            $table->date('completed_date')->nullable()->after('status');
            $table->date('dispatched_date')->nullable()->after('completed_date');
            $table->date('delivered_date')->nullable()->after('dispatched_date');
        });
    }

    public function down(): void
    {
        Schema::table('barcode_work_orders', function (Blueprint $table) {
            $table->dropColumn(['completed_date', 'dispatched_date', 'delivered_date']);
        });
    }
};
