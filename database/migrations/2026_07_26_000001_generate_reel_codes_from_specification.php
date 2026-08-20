<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE reels MODIFY code VARCHAR(255) NOT NULL');

        $reels = DB::table('reels')
            ->join('reel_providers', 'reel_providers.id', '=', 'reels.reel_provider_id')
            ->join('reel_categories', 'reel_categories.id', '=', 'reels.reel_category_id')
            ->join('reel_types', 'reel_types.id', '=', 'reels.reel_type_id')
            ->join('reel_gsms', 'reel_gsms.id', '=', 'reels.reel_gsm_id')
            ->select([
                'reels.id', 'reels.length', 'reels.width',
                'reel_providers.name as provider_name', 'reel_providers.short_name as provider_short_name',
                'reel_categories.name as category_name', 'reel_categories.short_name as category_short_name',
                'reel_types.name as type_name', 'reel_types.short_name as type_short_name',
                'reel_gsms.gsm',
            ])->orderBy('reels.id')->get();

        foreach ($reels as $reel) {
            DB::table('reels')->where('id', $reel->id)->update(['code' => 'TMP-REEL-' . $reel->id]);
        }

        $usedCodes = [];
        foreach ($reels as $reel) {
            $baseCode = implode('-', [
                $this->clean($reel->provider_short_name ?: $reel->provider_name),
                $this->clean($reel->category_short_name ?: $reel->category_name),
                $this->clean($reel->type_short_name ?: $reel->type_name),
                $this->clean($reel->gsm),
                $this->numberPart($reel->length),
                $this->numberPart($reel->width),
            ]);
            $usedCodes[$baseCode] = ($usedCodes[$baseCode] ?? 0) + 1;
            $code = $usedCodes[$baseCode] === 1 ? $baseCode : $baseCode . '-' . $usedCodes[$baseCode];
            DB::table('reels')->where('id', $reel->id)->update(['code' => $code]);
        }
    }

    public function down(): void
    {
        // Specification codes remain valid and are intentionally preserved.
    }

    private function clean(mixed $value): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $value));
    }

    private function numberPart(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    }
};
