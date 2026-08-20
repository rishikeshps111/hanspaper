<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReelBrandSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Thermal', 'APP', 'HANSOL', 'ECP', 'NPI', 'OJI', 'APP(TMPA)', 'KPAPERS',
            'TD DIMOND', 'TD DIAMOND (AKTB)', 'TL UNO', 'TD WHITE', 'TD NS', 'TD GOLD',
            'TD ECO G', 'CLASSIC', 'NPT', 'DELUXE MAPLITHO', 'SPB SILVERBRITE',
            'CREAMWOVE', 'SYNTHETIC',
        ];

        $this->seedNamed('reel_brands', $names);
    }

    private function seedNamed(string $table, array $names): void
    {
        $userId = DB::table('users')->value('id');
        foreach ($names as $name) {
            DB::table($table)->updateOrInsert(
                ['name' => $name],
                ['short_name' => $name, 'created_by' => $userId, 'updated_by' => $userId, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
