<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cores', function (Blueprint $table) {
            $table->string('name')->nullable()->after('code');
        });

        DB::table('cores')->select('id', 'size_mm')->orderBy('id')->chunkById(100, function ($cores) {
            foreach ($cores as $core) {
                $size = rtrim(rtrim(number_format((float) $core->size_mm, 2, '.', ''), '0'), '.');
                DB::table('cores')->where('id', $core->id)->update(['name' => $size.' mm']);
            }
        });

        Schema::table('cores', function (Blueprint $table) {
            $table->dropColumn('size_mm');
        });
    }

    public function down(): void
    {
        Schema::table('cores', function (Blueprint $table) {
            $table->decimal('size_mm', 10, 2)->nullable()->after('code');
        });

        DB::table('cores')->select('id', 'name')->orderBy('id')->chunkById(100, function ($cores) {
            foreach ($cores as $core) {
                DB::table('cores')->where('id', $core->id)->update([
                    'size_mm' => is_numeric($core->name) ? $core->name : (float) $core->name,
                ]);
            }
        });

        Schema::table('cores', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
