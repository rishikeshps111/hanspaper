<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReelGsmSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        foreach ([55, 58, 48, 52, 76, 60, 72, 65, 62, 120, 85] as $gsm) {
            DB::table('reel_gsms')->updateOrInsert(
                ['gsm' => $gsm],
                ['created_by' => $userId, 'updated_by' => $userId, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
