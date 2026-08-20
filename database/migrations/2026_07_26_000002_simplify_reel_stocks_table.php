<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reel_stocks', function (Blueprint $table) {
            $table->dropColumn(['selling_price', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::table('reel_stocks', function (Blueprint $table) {
            $table->decimal('selling_price', 15, 2)->default(0)->after('purchase_price');
            $table->date('received_at')->nullable()->after('status');
        });
    }
};
