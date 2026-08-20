<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reel_warehouses', function (Blueprint $table) {
            $table->enum('warehouse_type', ['factory', 'godown'])->default('godown')->after('short_name');
        });

        DB::table('reel_warehouses')
            ->whereRaw('UPPER(name) LIKE ?', ['%FACTORY%'])
            ->update(['warehouse_type' => 'factory']);
    }

    public function down(): void
    {
        Schema::table('reel_warehouses', fn (Blueprint $table) => $table->dropColumn('warehouse_type'));
    }
};
