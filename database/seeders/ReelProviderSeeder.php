<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReelProviderSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'VIVIDH', 'HANSOL', 'SURYATRA DELINKS', 'CHENNAI', 'ABHINAV', 'SUDHIR',
            'ASHITHA', 'HARISH', 'SREE VISHU', 'NPT PAPERS', 'SREE MEENAKSHI',
            'KUNNATH PAPER', 'COLOUR',
        ];
        $userId = DB::table('users')->value('id');
        foreach ($names as $name) {
            DB::table('reel_providers')->updateOrInsert(
                ['name' => $name],
                ['short_name' => $name, 'created_by' => $userId, 'updated_by' => $userId, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
