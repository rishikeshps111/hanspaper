<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reel_sales', function (Blueprint $table) {
            $table->string('invoice_number', 30)->nullable()->unique()->after('sale_code');
        });

        DB::table('reel_sales')->orderBy('id')->each(function ($sale) {
            DB::table('reel_sales')->where('id', $sale->id)->update([
                'invoice_number' => 'RINV' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('reel_sales', function (Blueprint $table) {
            $table->dropColumn('invoice_number');
        });
    }
};
