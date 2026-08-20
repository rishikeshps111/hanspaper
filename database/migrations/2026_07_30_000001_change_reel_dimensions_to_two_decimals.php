<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE reels MODIFY width DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE reels MODIFY length DECIMAL(12,2) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE reels MODIFY width DECIMAL(12,3) NOT NULL');
        DB::statement('ALTER TABLE reels MODIFY length DECIMAL(12,3) NOT NULL');
    }
};
