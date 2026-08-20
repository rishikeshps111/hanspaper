<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReelWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        foreach (['KUREEKKAD FACTORY' => 'factory', 'KUNDANNUR GODOWN' => 'godown'] as $name => $warehouseType) {
            DB::table('reel_warehouses')->updateOrInsert(
                ['name' => $name],
                ['short_name' => $name, 'warehouse_type' => $warehouseType, 'created_by' => $userId, 'updated_by' => $userId, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
