<?php

namespace Tests\Unit;

use App\Http\Controllers\Items\ProductionItemMasterController;
use App\Http\Controllers\ReelController;
use App\Http\Controllers\ReelDashboardController;
use App\Http\Controllers\ReelStockController;
use App\Http\Controllers\ReelSettingsController;
use App\Http\Controllers\ReelStockCorrectionController;
use App\Http\Requests\ReelRequest;
use App\Models\Items\ProductionItemMaster;
use App\Models\ProductionRun;
use App\Models\Reels\Reel;
use App\Models\Reels\ReelStock;
use App\Models\Reels\ReelStockMovement;
use App\Models\Reels\ReelStockUsage;
use App\Models\Reels\ReelType;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReelMeasurementTest extends TestCase
{
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('app.installation_status', false);
            $app['config']->set('telescope.enabled', false);
            $app['config']->set('debugbar.enabled', false);
            $app['config']->set('database.default', 'reel_measurement_test');
            $app['config']->set('database.connections.reel_measurement_test', [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
            ]);
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Only this dedicated in-memory connection is written to. Never migrate the application database.
        config(['database.default' => 'reel_measurement_test', 'database.connections.reel_measurement_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        DB::purge('reel_measurement_test');
        DB::connection()->getPdo()->sqliteCreateFunction('regexp', fn ($pattern, $value) => preg_match('/'.$pattern.'/', $value ?? ''), 2);
        $tables = [
            'reel_types' => 'name short_name is_active created_by updated_by',
            'reel_brands' => 'name short_name is_active', 'reel_gsms' => 'gsm is_active',
            'reel_providers' => 'name is_active', 'reel_warehouses' => 'name is_active warehouse_type',
            'reels' => 'code reel_brand_id reel_type_id reel_gsm_id width length unit_price selling_price is_active remarks',
            'reel_stocks' => 'stock_code actual_code reel_id reel_provider_id reel_warehouse_id original_length balance_length cut_width purchase_price status is_active remarks created_by updated_by',
            'reel_stock_movements' => 'batch_uuid reel_stock_id reel_provider_id transaction_type stock_status length balance_before balance_after reference_type reference_id customer_id reel_warehouse_id remarks created_by',
            'reel_stock_usages' => 'production_id production_run_id production_list_id reel_stock_id source_status calculated_status resulting_status status_selection_type source_width output_roll_width roll_length production_quantity consumed_length output_roll_count total_output_length width_waste balance_before balance_after remaining_output_length physical_remaining_length wastage_output_length physical_wastage_length machine_id created_by updated_by',
            'reel_sale_items' => 'reel_sale_id reel_stock_id length unit_price discount total balance_before balance_after',
            'reel_stock_corrections' => 'stock_batch_uuid original_reel_id original_reel_provider_id original_reel_warehouse_id reel_id reel_provider_id reel_warehouse_id previous_quantity corrected_quantity quantity_change affected_stock_codes reason created_by',
            'reel_detail_corrections' => 'reel_id before_values after_values reason created_by',
            'reel_sales' => 'sale_code invoice_number customer_id sale_date subtotal discount is_gst_applicable sgst_percentage sgst_amount cgst_percentage cgst_amount total remarks created_by updated_by',
            'production_item_masters' => 'requested_qty item_id status production_status assigned_machine_id assigned_production_user_id',
            'production_runs' => 'production_id reel_stock_id machine_id production_user_id core_id core_quantity source_reel_status output_roll_width roll_length production_quantity status active_key started_at finished_at started_by finished_by correction_history',
            'production_list' => 'production_item_master_id production_run_id machine_id produced_by quantity actual_quantity excess_stock_quantity real_id reel_stock_id core_id core_quantity deleted_at',
            'packing_list' => 'production_item_master_id quantity deleted_at',
            'cores' => 'code name quantity is_active updated_by',
            'core_stock_movements' => 'core_id transaction_type quantity_change quantity_before quantity_after reference_type reference_id remarks created_by',
            'machines' => 'machine_name status', 'employees' => 'name', 'parties' => 'first_name last_name',
        ];
        foreach ($tables as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns) {
                $table->id();
                foreach (explode(' ', $columns) as $column) {
                    $table->string($column)->nullable();
                }
                $table->timestamps();
            });
        }
        // SQLite cannot alter column nullability on this Laravel version; the fixture length is already nullable.
        Schema::table('reel_types', fn (Blueprint $table) => $table->string('volume')->default('length'));
        foreach (['reels' => ['weight_kg'], 'reel_stocks' => ['original_weight_kg', 'balance_weight_kg'],
            'reel_stock_movements' => ['weight_kg', 'weight_before_kg', 'weight_after_kg'],
            'reel_sale_items' => ['weight_kg', 'weight_before_kg', 'weight_after_kg'],
            'reel_stock_usages' => ['weight_before_kg', 'consumed_weight_kg', 'remaining_weight_kg', 'wastage_weight_kg'],
        ] as $name => $columns) {
            Schema::table($name, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) $table->decimal($column, 12, 3)->nullable();
            });
        }
        foreach (['reel_brands' => ['name' => 'APP', 'short_name' => 'APP'], 'reel_gsms' => ['gsm' => 55],
            'reel_providers' => ['name' => 'Provider'], 'reel_warehouses' => ['name' => 'Factory'],
        ] as $table => $values) {
            DB::table($table)->insert(['id' => 1, 'is_active' => 1, ...$values]);
        }
        DB::table('reel_warehouses')->insert(['id' => 2, 'name' => 'Godown', 'is_active' => 1]);
        DB::table('employees')->insert(['id' => 1, 'name' => 'Operator']);
        DB::table('machines')->insert(['id' => 1, 'machine_name' => 'Machine', 'status' => 'Active']);
        DB::table('cores')->insert(['id' => 1, 'name' => 'Core', 'quantity' => 1000, 'is_active' => 1]);
        DB::table('parties')->insert(['id' => 1, 'first_name' => 'Customer']);
        $user = new User();
        $user->id = 1;
        $this->actingAs($user);
    }

    private function reel(string $mode = 'weight'): Reel
    {
        $type = ReelType::create(['name' => 'Thermal '.$mode, 'short_name' => 'THERMALPAPER', 'volume' => $mode, 'is_active' => true]);
        return Reel::create(['code' => 'APP-THERMALPAPER-55GSM-485-'.($mode === 'weight' ? '50' : '6000'),
            'reel_brand_id' => 1, 'reel_type_id' => $type->id, 'reel_gsm_id' => 1,
            'width' => 485, 'length' => $mode === 'length' ? 6000 : null, 'weight_kg' => $mode === 'weight' ? 50 : null,
            'unit_price' => 100, 'selling_price' => 150, 'is_active' => true]);
    }

    private function stock(Reel $reel, int $quantity = 1): ReelStock
    {
        $response = app(ReelStockController::class)->bulkStore(Request::create('/', 'POST', [
            'reel_id' => $reel->id, 'reel_provider_id' => 1, 'reel_warehouse_id' => 1,
            'quantity' => $quantity,
        ]));
        $this->assertSame(200, $response->getStatusCode());
        return ReelStock::where('reel_id', $reel->id)->firstOrFail();
    }

    private function start(ReelStock $stock): ProductionRun
    {
        $production = ProductionItemMaster::create(['requested_qty' => 100, 'status' => 'Pending', 'production_status' => 'Pending']);
        $response = app(ProductionItemMasterController::class)->startProduction(Request::create('/', 'POST', [
            'production_id' => $production->id, 'reel_stock_id' => $stock->id, 'packed_by' => 1,
            'machines' => 1, 'core_id' => 1, 'roll_length' => 10, 'output_roll_width' => 100,
        ]));
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        return ProductionRun::where('production_id', $production->id)->firstOrFail();
    }

    private function finish(ProductionRun $run, string $status, ?float $weight = null): array
    {
        $response = app(ProductionItemMasterController::class)->storeProduction(Request::create('/', 'POST', [
            'production_id' => $run->production_id, 'production_run_id' => $run->id,
            'production_qty' => 10, 'reel_status_after_usage' => $status,
            'reel_status_selection_type' => 'manual', 'remaining_weight_kg' => $weight,
        ]));
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        return $response->getData(true);
    }

    public function test_weight_stock_production_and_repeated_bit_usage(): void
    {
        $stock = $this->stock($this->reel(), 2);
        $this->assertEquals(50, ReelStock::latest('id')->first()->balance_weight_kg);
        $this->assertEquals(0, $stock->balance_length);
        $first = $this->finish($this->start($stock), 'bit', 30.5);
        $this->assertSame('kg', $first['bit_reel_label']['measurement_unit']);
        $this->assertSame('30.50', $first['bit_reel_label']['actual_balance_length']);
        $this->assertEquals(30.5, $stock->fresh()->availableBalance());
        $this->assertEquals(19.5, ReelStockUsage::first()->consumed_weight_kg);
        $this->finish($this->start($stock->fresh()), 'finished');
        $this->assertSame('finished', $stock->fresh()->status);
        $this->assertEquals(0, $stock->fresh()->balance_weight_kg);
        $usage = ReelStockUsage::latest('id')->first();
        $this->assertEquals(30.5, $usage->consumed_weight_kg);
        $this->assertEquals(0, $usage->wastage_weight_kg);
        $this->assertSame(0, ReelStockMovement::where('transaction_type', 'production_wastage')->count());
        $this->assertEquals(980, DB::table('cores')->value('quantity'));
    }

    public function test_bulk_stock_uses_reel_master_weight_for_every_quantity(): void
    {
        $reel = $this->reel();
        $this->stock($reel, 3);
        $this->assertSame(3, ReelStock::count());
        $this->assertSame([50.0], ReelStock::pluck('balance_weight_kg')->map(fn ($weight) => (float) $weight)->unique()->values()->all());
        $this->assertSame([50.0], ReelStockMovement::where('transaction_type', 'opening')
            ->pluck('weight_kg')->map(fn ($weight) => (float) $weight)->unique()->values()->all());
        app(ReelStockController::class)->bulkStore(Request::create('/', 'POST', [
            'reel_id' => $reel->id, 'reel_provider_id' => 1, 'reel_warehouse_id' => 1,
            'quantity' => 1, 'actual_weight_kg' => 42.5,
        ]));
        $this->assertEquals(50, ReelStock::latest('id')->first()->balance_weight_kg);
    }

    public function test_length_production_keeps_width_split_calculation(): void
    {
        $stock = $this->stock($this->reel('length'));
        $result = $this->finish($this->start($stock), 'bit');
        $this->assertEquals(23900, $stock->fresh()->balance_length);
        $this->assertEquals(5975, $stock->fresh()->actualBalanceLength());
        $this->assertNull($stock->fresh()->balance_weight_kg);
        $this->assertSame('m', $result['bit_reel_label']['measurement_unit']);
    }

    public function test_invalid_remaining_weight_rolls_back_production(): void
    {
        $stock = $this->stock($this->reel());
        $run = $this->start($stock);
        foreach ([null, 0, 51] as $remaining) {
            try {
                $this->finish($run, 'bit', $remaining);
                $this->fail('An invalid balance was accepted.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('remaining_weight_kg', $e->errors());
            }
        }
        $this->assertSame('in_progress', $run->fresh()->status);
        $this->assertEquals(50, $stock->fresh()->balance_weight_kg);
        $this->assertEquals(0, ReelStockUsage::count());
    }

    public function test_finished_weight_reel_uses_zero_even_if_a_stale_form_sends_weight(): void
    {
        $stock = $this->stock($this->reel());
        $this->finish($this->start($stock), 'finished', 12.5);

        $this->assertEquals(0, $stock->fresh()->balance_weight_kg);
        $this->assertEquals(50, ReelStockUsage::first()->consumed_weight_kg);
        $this->assertEquals(0, ReelStockUsage::first()->wastage_weight_kg);
    }

    public function test_transfer_and_sale_preserve_weight_audit(): void
    {
        $reel = $this->reel();
        $stock = $this->stock($reel);
        $controller = app(ReelDashboardController::class);
        $controller->transfer(Request::create('/', 'POST', ['stock_ids' => [$stock->id], 'source_warehouse_id' => 1, 'destination_warehouse_id' => 2]), $reel);
        $this->assertEquals(2, $stock->fresh()->reel_warehouse_id);
        $this->assertEquals(50, ReelStockMovement::where('transaction_type', 'transfer_in')->first()->weight_after_kg);
        $controller->sale(Request::create('/', 'POST', ['stock_ids' => [$stock->id], 'customer_id' => 1, 'sale_date' => '2026-09-16']), $reel);
        $this->assertSame('sold', $stock->fresh()->status);
        $this->assertEquals(0, $stock->fresh()->balance_weight_kg);
        $this->assertEquals(50, DB::table('reel_sale_items')->value('weight_kg'));
        $this->assertEquals(50, ReelStockMovement::where('transaction_type', 'sale')->first()->weight_kg);
    }

    public function test_weight_master_validation_and_code(): void
    {
        $type = ReelType::create(['name' => 'Weight paper', 'short_name' => 'THERMALPAPER', 'volume' => 'weight']);
        $request = ReelRequest::create('/', 'POST', ['reel_brand_id' => 1, 'reel_type_id' => $type->id,
            'reel_gsm_id' => 1, 'width' => 485, 'weight_kg' => 50, 'unit_price' => 100,
            'selling_price' => 150, 'is_active' => 1, 'remarks' => 'Weight stock'], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->setContainer($this->app)->setRedirector($this->app['redirect']);
        $request->validateResolved();
        app(ReelController::class)->store($request);
        $this->assertSame('APP-THERMALPAPER-55GSM-485-50', Reel::first()->code);
        $this->assertNull(Reel::first()->length);
        $this->expectException(ValidationException::class);
        $type->update(['volume' => 'length']);
    }

    public function test_stock_correction_uses_master_weight_and_updates_untouched_stock(): void
    {
        $reel = $this->reel();
        $stock = $this->stock($reel);
        $request = Request::create('/', 'POST', [
            'stock_batch_uuid' => ReelStockMovement::first()->batch_uuid,
            'original_reel_id' => $reel->id, 'original_reel_provider_id' => 1, 'original_reel_warehouse_id' => 1,
            'reel_id' => $reel->id, 'reel_provider_id' => 1, 'reel_warehouse_id' => 1,
            'corrected_quantity' => 2, 'reason' => 'Missed a reel',
        ]);
        app(ReelStockCorrectionController::class)->correctStock($request);
        $this->assertEquals(50, $stock->fresh()->balance_weight_kg);
        $this->assertEquals(50, ReelStock::latest('id')->first()->balance_weight_kg);
        $this->assertEquals(50, ReelStockMovement::latest('id')->first()->weight_after_kg);
        app(ReelStockCorrectionController::class)->updateReel(Request::create('/', 'POST', [
            'reel_brand_id' => 1, 'reel_type_id' => $reel->reel_type_id, 'reel_gsm_id' => 1,
            'width' => 485, 'weight_kg' => 60, 'unit_price' => 100, 'selling_price' => 150,
            'is_active' => 1, 'reason' => 'Correct nominal weight',
        ]), $reel);
        $this->assertSame('APP-THERMALPAPER-55GSM-485-60', $reel->fresh()->code);
        $this->assertEquals(60, $stock->fresh()->balance_weight_kg);
    }

    public function test_dashboard_and_barcode_data_use_kilograms(): void
    {
        $reel = $this->reel();
        $stock = $this->stock($reel);
        $stock->update(['status' => 'bit', 'balance_weight_kg' => 30.5, 'cut_width' => 100]);
        $request = Request::create('/', 'GET');
        $rows = app(ReelDashboardController::class)->data($request)->getData(true);
        $this->assertSame('50 kg', $rows['data'][0]['length']);
        $rows = app(ReelDashboardController::class)->stocks($request, $reel)->getData(true);
        $this->assertStringContainsString('data-measurement-unit="kg"', $rows['data'][0]['action']);
        $this->assertStringContainsString('data-actual-balance-length="30.50"', $rows['data'][0]['select']);
        $rows = app(ReelStockController::class)->reelStockData($request, $reel)->getData(true);
        $this->assertStringContainsString('data-unit="kg"', $rows['data'][0]['action']);
        $this->assertStringContainsString('data-balance="30.50"', $rows['data'][0]['select']);
    }

    public function test_reel_settings_reject_deletion_of_records_in_use(): void
    {
        $reel = $this->reel();
        $this->stock($reel);
        foreach ([
            ['brands', 1], ['gsm', 1], ['types', $reel->reel_type_id],
            ['providers', 1], ['warehouses', 1],
        ] as [$type, $id]) {
            try {
                app(ReelSettingsController::class)->destroy($type, $id);
                $this->fail("An in-use {$type} setting was deleted.");
            } catch (ValidationException $exception) {
                $this->assertStringContainsString('cannot be deleted', $exception->errors()['delete'][0]);
            }
        }

        DB::table('reel_brands')->insert(['id' => 2, 'name' => 'Unused', 'is_active' => 1]);
        $this->assertSame(200, app(ReelSettingsController::class)->destroy('brands', 2)->getStatusCode());
        $this->assertFalse(DB::table('reel_brands')->where('id', 2)->exists());
    }
}
