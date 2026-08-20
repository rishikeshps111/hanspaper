<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReelPdfStockSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $now = now();
        $defaultLength = 6000;

        // Provider, brand, GSM, width (mm), length (m). Blank MTR values use 6000 m.
        $rows = [
            ['VIVIDH','APP',55,485,6000], ['VIVIDH','APP',55,400,6000], ['VIVIDH','APP',55,800,12000],
            ['HANSOL','HANSOL',55,400,6000], ['HANSOL','HANSOL',55,800,12000], ['HANSOL','HANSOL',55,800,15000],
            ['HANSOL','HANSOL',58,650,12000], ['HANSOL','HANSOL',58,400,6000], ['HANSOL','HANSOL',55,450,6000],
            ['HANSOL','HANSOL',58,803,8500], ['HANSOL','HANSOL',58,635,6000], ['HANSOL','HANSOL',58,689,12000],
            ['HANSOL','HANSOL',48,800,$defaultLength],
            ['SURYATRA DELINKS','ECP/TDC',58,400,6000], ['SURYATRA DELINKS','ECP/TDC',58,450,$defaultLength],
            ['SURYATRA DELINKS','ECP/TDC',58,800,$defaultLength], ['SURYATRA DELINKS','ECP/TDC',48,400,$defaultLength],
            ['CHENNAI','HANSOL',52,490,12000], ['CHENNAI','NPI',55,480,6000], ['CHENNAI','OJI',58,350,6000],
            ['CHENNAI','OJI',58,793,6000], ['CHENNAI','OJI',58,800,6000], ['CHENNAI','OJI',76,340,6000],
            ['CHENNAI','APP(TMPA)',55,400,6000], ['CHENNAI','OJI',76,342,9000], ['CHENNAI','OJI',76,350,3000],
            ['CHENNAI','OJI',76,440,12000], ['CHENNAI','KPAPERS',55,445,6000], ['CHENNAI','HANSOL',58,645,6000],
            ['CHENNAI','HANSOL',58,714,12000], ['CHENNAI','APP(TMPA)',58,400,6000], ['CHENNAI','ALLIED',58,445,$defaultLength],
            ['SUDHIR','TL UNO',55,400,6000], ['SUDHIR','TL UNO',55,400,3000], ['SUDHIR','TL UNO',58,400,6000],
            ['SUDHIR','TL UNO',58,405,6000], ['SUDHIR','TL UNO',58,405,5700], ['SUDHIR','TL UNO',58,485,6000],
            ['SUDHIR','TL UNO',58,485,5700], ['SUDHIR','TL UNO',55,405,6000], ['SUDHIR','TL UNO',55,405,6100],
            ['SUDHIR','TL UNO',55,405,3100], ['ASHITHA','OJI',72,406,9000], ['ASHITHA','OJI',65,403,9000],
            ['HARISH','TD WHITE',65,400,$defaultLength], ['HARISH','TD NS',65,400,$defaultLength],
            ['HARISH','TD WHITE',62,400,$defaultLength], ['HARISH','TD WHITE',62,325,$defaultLength],
            ['HARISH','TD WHITE',62,800,$defaultLength], ['HARISH','TD WHITE',55,405,$defaultLength],
            ['HARISH','TD NS',55,405,$defaultLength], ['HARISH','TD WHITE',55,800,$defaultLength],
            ['HARISH','TD NS',62,400,$defaultLength], ['HARISH','TD NS',55,560,6000],
            ['HARISH','TD GOLD',75,315,$defaultLength], ['HARISH','TD GOLD',75,450,$defaultLength],
            ['HARISH','TD ECO G',52,400,$defaultLength], ['HARISH','TD ECO G',55,430,$defaultLength],
            ['NPT PAPERS','NPT',67,400,$defaultLength], ['NPT PAPERS','NPT',54,400,$defaultLength],
            ['NPT PAPERS','NPT',56,400,$defaultLength],
            ['ADITYAASWIN PAPERS','SUNSHINE MAPLITHO',60,46,$defaultLength],
            ['ADITYAASWIN PAPERS','SUNSHINE MAPLITHO',60,50.8,$defaultLength],
            ['KUNNATH PAPER','DELUXE MAPLITHO',60,50.8,$defaultLength], ['KUNNATH PAPER','DELUXE MAPLITHO',60,46,$defaultLength],
            ['KUNNATH PAPER','DELUXE MAPLITHO',60,43,$defaultLength], ['KUNNATH PAPER','DELUXE MAPLITHO',70,23,$defaultLength],
            ['KUNNATH PAPER','DELUXE MAPLITHO',60,38.1,$defaultLength], ['KUNNATH PAPER','DELUXE MAPLITHO',60,30.5,$defaultLength],
            ['KUNNATH PAPER','DELUXE MAPLITHO',70,25.4,$defaultLength], ['KUNNATH PAPER','DELUXE MAPLITHO',70,38.1,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',60,23,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',60,25.4,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',60,30.5,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',60,38.1,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',60,40.6,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',60,43,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',60,46,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',60,50.8,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',70,23,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',70,25.4,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',70,30.5,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',70,38.1,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',70,40.6,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',70,43,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',70,50.8,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',80,23,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',80,25.4,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',80,30.5,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',80,38.1,$defaultLength], ['SREE MEENAKSHI','SPB SILVERBRITE',120,23,$defaultLength],
            ['SREE MEENAKSHI','SPB SILVERBRITE',120,25.4,$defaultLength],
            ['COLOUR','TD DIAMOND',55,400,$defaultLength], ['COLOUR','TL UNO',58,400,$defaultLength],
            ['COLOUR','SYNTHETIC',65,350,$defaultLength], ['COLOUR','SYNTHETIC',65,450,$defaultLength],
            ['COLOUR','SYNTHETIC',85,440,$defaultLength],
        ];

        $typeId = $this->namedId('reel_types', 'Thermal Paper', $userId, $now);
        $warehouses = DB::table('reel_warehouses')->where('is_active', true)->orderBy('id')->get();
        mt_srand(24072026);

        foreach (collect($rows)->unique(fn ($row) => implode('|', $row)) as [$provider, $brand, $gsm, $width, $length]) {
            $providerId = $this->namedId('reel_providers', $provider, $userId, $now);
            $brandId = $this->namedId('reel_brands', $brand, $userId, $now);
            $gsmId = $this->gsmId($gsm, $userId, $now);
            $code = implode('-', [
                $this->clean($brand), 'THERMALPAPER',
                $this->numberPart($gsm) . 'GSM', $this->numberPart($width), $this->numberPart($length),
            ]);
            $priceSeed = abs(crc32($code));
            $unitPrice = 500 + ($priceSeed % 1501);
            $sellingPrice = round($unitPrice * 1.20, 2);

            $reelId = DB::table('reels')->where([
                'reel_brand_id' => $brandId,
                'reel_type_id' => $typeId, 'reel_gsm_id' => $gsmId,
                'width' => $width, 'length' => $length,
            ])->value('id');

            if (!$reelId) {
                $reelId = DB::table('reels')->insertGetId([
                    'code' => $code, 'reel_brand_id' => $brandId,
                    'reel_type_id' => $typeId, 'reel_gsm_id' => $gsmId, 'width' => $width, 'length' => $length,
                    'unit_price' => $unitPrice, 'selling_price' => $sellingPrice, 'is_active' => true,
                    'remarks' => 'Seeded from 24th July Reel PDF', 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $targets = [];
            foreach ($warehouses as $warehouse) $targets[$warehouse->id] = mt_rand(0, 6);
            if (array_sum($targets) === 0 && $warehouses->isNotEmpty()) $targets[$warehouses->first()->id] = 1;

            foreach ($targets as $warehouseId => $target) {
                $existing = DB::table('reel_stocks')->where('reel_id', $reelId)
                    ->where('reel_provider_id', $providerId)
                    ->where('reel_warehouse_id', $warehouseId)->count();
                for ($index = $existing + 1; $index <= $target; $index++) {
                    $nextCode = (int) DB::table('reel_stocks')
                        ->whereRaw("stock_code REGEXP '^[0-9]+$'")
                        ->max(DB::raw('CAST(stock_code AS UNSIGNED)')) + 1;
                    $stockCode = str_pad((string) $nextCode, 6, '0', STR_PAD_LEFT);
                    $stockId = DB::table('reel_stocks')->insertGetId([
                        'stock_code' => $stockCode, 'reel_id' => $reelId, 'reel_provider_id' => $providerId,
                        'reel_warehouse_id' => $warehouseId,
                        'original_length' => $length, 'balance_length' => $length, 'purchase_price' => $unitPrice,
                        'status' => 'full', 'is_active' => true, 'remarks' => 'Random seeded PDF stock',
                        'created_by' => $userId, 'updated_by' => $userId, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                    DB::table('reel_stock_movements')->insert([
                        'batch_uuid' => (string) Str::uuid(), 'reel_stock_id' => $stockId, 'reel_provider_id' => $providerId,
                        'transaction_type' => 'opening', 'stock_status' => 'full', 'length' => $length,
                        'balance_before' => 0, 'balance_after' => $length, 'reel_warehouse_id' => $warehouseId,
                        'remarks' => 'Opening stock seeded from 24th July Reel PDF',
                        'created_by' => $userId, 'created_at' => $now,
                    ]);
                }
            }
        }
    }

    private function namedId(string $table, string $name, mixed $userId, mixed $now): int
    {
        DB::table($table)->updateOrInsert(['name' => $name], [
            'short_name' => $name, 'is_active' => true, 'created_by' => $userId,
            'updated_by' => $userId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        return (int) DB::table($table)->where('name', $name)->value('id');
    }

    private function gsmId(mixed $gsm, mixed $userId, mixed $now): int
    {
        DB::table('reel_gsms')->updateOrInsert(['gsm' => $gsm], [
            'is_active' => true, 'created_by' => $userId, 'updated_by' => $userId,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        return (int) DB::table('reel_gsms')->where('gsm', $gsm)->value('id');
    }

    private function clean(mixed $value): string
    {
        return Str::upper(preg_replace('/[^A-Z0-9]/i', '', (string) $value));
    }

    private function numberPart(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    }
}
