<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReelTypeSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        foreach (['Thermal Paper', 'Normal Paper', 'Color', 'Synthetic'] as $name) {
            DB::table('reel_types')->updateOrInsert(
                ['name' => $name],
                ['short_name' => $name, 'created_by' => $userId, 'updated_by' => $userId, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
