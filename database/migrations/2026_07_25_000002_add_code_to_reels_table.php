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
            $table->string('code', 40)->nullable()->after('id');
        });

        DB::table('reels')->orderBy('id')->each(function ($reel) {
            DB::table('reels')->where('id', $reel->id)->update([
                'code' => 'REEL' . str_pad((string) $reel->id, 4, '0', STR_PAD_LEFT),
            ]);
        });

        Schema::table('reels', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('reels', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
