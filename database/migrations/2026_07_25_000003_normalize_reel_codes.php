<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('reels')->orderBy('id')->each(function ($reel) {
            DB::table('reels')->where('id', $reel->id)->update([
                'code' => 'REEL' . str_pad((string) $reel->id, 4, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        // Codes remain valid and unique; no destructive rollback is required.
    }
};
