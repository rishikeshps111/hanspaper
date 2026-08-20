<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('reel_stocks', 'reel_provider_id')) {
            Schema::table('reel_stocks', function (Blueprint $table) {
                $table->foreignId('reel_provider_id')->nullable()->after('reel_id')
                    ->constrained('reel_providers')->restrictOnDelete();
                $table->index(['reel_provider_id', 'reel_warehouse_id', 'status'], 'reel_stocks_provider_warehouse_status_index');
            });
        }
        if (!Schema::hasColumn('reel_stock_movements', 'reel_provider_id')) {
            Schema::table('reel_stock_movements', function (Blueprint $table) {
                $table->foreignId('reel_provider_id')->nullable()->after('reel_stock_id')
                    ->constrained('reel_providers')->restrictOnDelete();
            });
        }

        DB::statement('UPDATE reel_stocks INNER JOIN reels ON reels.id = reel_stocks.reel_id SET reel_stocks.reel_provider_id = reels.reel_provider_id');
        DB::statement('UPDATE reel_stock_movements INNER JOIN reel_stocks ON reel_stocks.id = reel_stock_movements.reel_stock_id SET reel_stock_movements.reel_provider_id = reel_stocks.reel_provider_id');
        DB::statement('ALTER TABLE reel_stocks MODIFY reel_provider_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE reel_stock_movements MODIFY reel_provider_id BIGINT UNSIGNED NOT NULL');

        $reels = DB::table('reels')
            ->orderBy('id')
            ->get(['id', 'reel_brand_id', 'reel_type_id', 'reel_gsm_id', 'width', 'length']);

        foreach ($reels->groupBy(fn ($reel) => implode('|', [
            $reel->reel_brand_id, $reel->reel_type_id, $reel->reel_gsm_id,
            number_format((float) $reel->width, 2, '.', ''),
            number_format((float) $reel->length, 2, '.', ''),
        ])) as $matchingReels) {
            $canonical = $matchingReels->first();
            $duplicateIds = $matchingReels->pluck('id')->reject(fn ($id) => $id === $canonical->id)->values();
            if ($duplicateIds->isEmpty()) {
                continue;
            }

            DB::table('reel_stocks')->whereIn('reel_id', $duplicateIds)->update(['reel_id' => $canonical->id]);
            DB::table('reels')->whereIn('id', $duplicateIds)->delete();
        }

        $brandNames = DB::table('reel_brands')->get()->keyBy('id');
        $typeNames = DB::table('reel_types')->get()->keyBy('id');
        $gsmValues = DB::table('reel_gsms')->get()->keyBy('id');
        foreach (DB::table('reels')->orderBy('id')->get() as $reel) {
            $clean = fn ($value) => strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $value));
            $number = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
            $brand = $brandNames[$reel->reel_brand_id];
            $type = $typeNames[$reel->reel_type_id];
            $code = implode('-', [
                $clean($brand->short_name ?: $brand->name),
                $clean($type->short_name ?: $type->name),
                $clean($gsmValues[$reel->reel_gsm_id]->gsm) . 'GSM',
                $number($reel->width),
                $number($reel->length),
            ]);
            DB::table('reels')->where('id', $reel->id)->update(['code' => $code]);
        }

        DB::table('reel_stocks')->orderBy('id')->get(['id'])->each(function ($stock) {
            DB::table('reel_stocks')->where('id', $stock->id)->update(['stock_code' => 'MIGRATION-' . $stock->id]);
        });
        foreach (DB::table('reel_stocks')->orderBy('id')->pluck('id') as $sequence => $stockId) {
            DB::table('reel_stocks')->where('id', $stockId)
                ->update(['stock_code' => str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT)]);
        }

        Schema::table('reels', function (Blueprint $table) {
            $table->index('reel_brand_id', 'reels_reel_brand_id_index');
            $table->dropConstrainedForeignId('reel_provider_id');
            $table->dropIndex('reels_setting_filters_index');
            $table->index(['reel_brand_id', 'reel_type_id', 'reel_gsm_id'], 'reels_setting_filters_index');
        });
    }

    public function down(): void
    {
        Schema::table('reels', function (Blueprint $table) {
            $table->dropIndex('reels_setting_filters_index');
            $table->foreignId('reel_provider_id')->nullable()->after('reel_brand_id')
                ->constrained('reel_providers')->restrictOnDelete();
            $table->index(['reel_brand_id', 'reel_provider_id', 'reel_type_id', 'reel_gsm_id'], 'reels_setting_filters_index');
        });
        DB::statement('UPDATE reels INNER JOIN reel_stocks ON reel_stocks.reel_id = reels.id SET reels.reel_provider_id = reel_stocks.reel_provider_id WHERE reels.reel_provider_id IS NULL');

        Schema::table('reel_stock_movements', fn (Blueprint $table) => $table->dropConstrainedForeignId('reel_provider_id'));
        Schema::table('reel_stocks', function (Blueprint $table) {
            $table->dropIndex('reel_stocks_provider_warehouse_status_index');
            $table->dropConstrainedForeignId('reel_provider_id');
        });
    }
};
