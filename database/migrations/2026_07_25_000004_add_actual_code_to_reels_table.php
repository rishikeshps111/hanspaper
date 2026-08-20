<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reels', function (Blueprint $table) {
            $table->string('actual_code', 100)->nullable()->after('code');
        });

        DB::table('reels')->whereNull('actual_code')->update(['actual_code' => DB::raw('code')]);

        Schema::table('reels', function (Blueprint $table) {
            $table->unique('actual_code');
        });
    }

    public function down(): void
    {
        Schema::table('reels', function (Blueprint $table) {
            $table->dropUnique(['actual_code']);
            $table->dropColumn('actual_code');
        });
    }
};
