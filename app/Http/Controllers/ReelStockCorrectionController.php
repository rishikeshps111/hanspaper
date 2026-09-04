<?php

namespace App\Http\Controllers;

use App\Models\Reels\Reel;
use App\Models\Reels\ReelBrand;
use App\Models\Reels\ReelDetailCorrection;
use App\Models\Reels\ReelGsm;
use App\Models\Reels\ReelProvider;
use App\Models\Reels\ReelStock;
use App\Models\Reels\ReelStockCorrection;
use App\Models\Reels\ReelStockMovement;
use App\Models\Reels\ReelType;
use App\Models\Reels\ReelWarehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReelStockCorrectionController extends Controller
{
    public function index(): View
    {
        return view('reels.corrections.index', [
            'reels' => Reel::with(['brand', 'type', 'gsm'])->orderBy('code')->get(),
            'brands' => ReelBrand::orderBy('name')->get(),
            'types' => ReelType::orderBy('name')->get(),
            'gsms' => ReelGsm::orderBy('gsm')->get(),
            'providers' => ReelProvider::orderBy('name')->get(),
            'warehouses' => ReelWarehouse::orderBy('name')->get(),
        ]);
    }

    public function stockBatches(Request $request): JsonResponse
    {
        $query = DB::table('reel_stock_movements as opening')
            ->join('reel_stocks as stocks', 'stocks.id', '=', 'opening.reel_stock_id')
            ->join('reels', 'reels.id', '=', 'stocks.reel_id')
            ->join('reel_providers as providers', 'providers.id', '=', 'stocks.reel_provider_id')
            ->join('reel_warehouses as warehouses', 'warehouses.id', '=', 'stocks.reel_warehouse_id')
            ->where('opening.transaction_type', 'opening')
            ->whereNotNull('opening.batch_uuid')
            ->when($request->filled('reel_id'), fn ($q) => $q->where('stocks.reel_id', $request->integer('reel_id')))
            ->when($request->filled('reel_provider_id'), fn ($q) => $q->where('stocks.reel_provider_id', $request->integer('reel_provider_id')))
            ->when($request->filled('reel_warehouse_id'), fn ($q) => $q->where('stocks.reel_warehouse_id', $request->integer('reel_warehouse_id')))
            ->when($request->filled('added_date'), fn ($q) => $q->whereDate('opening.created_at', $request->input('added_date')))
            ->selectRaw('opening.batch_uuid, stocks.reel_id, stocks.reel_provider_id, stocks.reel_warehouse_id,
                reels.code as reel_code, providers.name as provider_name, warehouses.name as warehouse_name,
                MIN(opening.created_at) as added_at, COUNT(DISTINCT stocks.id) as recorded_quantity,
                COUNT(DISTINCT CASE WHEN stocks.status <> "voided" THEN stocks.id END) as current_quantity')
            ->groupBy('opening.batch_uuid', 'stocks.reel_id', 'stocks.reel_provider_id', 'stocks.reel_warehouse_id', 'reels.code', 'providers.name', 'warehouses.name')
            ->orderByDesc('added_at');

        return DataTables::of($query)
            ->addColumn('original_quantity', function ($row) {
                $addedByCorrections = ReelStockCorrection::where('stock_batch_uuid', $row->batch_uuid)
                    ->where('reel_id', $row->reel_id)->where('reel_provider_id', $row->reel_provider_id)
                    ->where('reel_warehouse_id', $row->reel_warehouse_id)->where('quantity_change', '>', 0)
                    ->sum('quantity_change');
                return max(0, (int) $row->recorded_quantity - (int) $addedByCorrections);
            })
            ->addColumn('eligible_quantity', fn ($row) => $this->eligibleStocks($row->batch_uuid, $row->reel_id, $row->reel_provider_id, $row->reel_warehouse_id)->count())
            ->editColumn('added_at', fn ($row) => $row->added_at ? date('d M Y h:i a', strtotime($row->added_at)) : '—')
            ->addColumn('action', fn ($row) => '<button class="btn btn-sm btn-outline-primary correct-stock"'
                . ' data-batch="' . e($row->batch_uuid) . '" data-reel="' . $row->reel_id
                . ' data-provider="' . $row->reel_provider_id . '" data-warehouse="' . $row->reel_warehouse_id
                . ' data-code="' . e($row->reel_code) . '" title="Correct Quantity"><i class="bx bx-edit"></i></button>')
            ->rawColumns(['action'])->toJson();
    }

    public function reels(Request $request): JsonResponse
    {
        $query = Reel::with(['brand:id,name', 'type:id,name', 'gsm:id,gsm'])->withCount('stocks')->latest();
        return DataTables::eloquent($query)
            ->addColumn('brand_name', fn (Reel $reel) => $reel->brand?->name ?? '—')
            ->addColumn('type_name', fn (Reel $reel) => $reel->type?->name ?? '—')
            ->addColumn('gsm_value', fn (Reel $reel) => $reel->gsm?->gsm ?? '—')
            ->editColumn('is_active', fn (Reel $reel) => (int) $reel->is_active)
            ->addColumn('action', fn (Reel $reel) => '<button class="btn btn-sm btn-outline-primary edit-reel" data-id="' . $reel->id . '" title="Correct Reel Details"><i class="bx bx-edit"></i></button>')
            ->rawColumns(['action'])->toJson();
    }

    public function reel(Reel $reel): JsonResponse
    {
        return response()->json(['reel' => $reel->only([
            'id', 'code', 'reel_brand_id', 'reel_type_id', 'reel_gsm_id', 'width', 'length',
            'unit_price', 'selling_price', 'is_active', 'remarks',
        ])]);
    }

    public function correctStock(Request $request): JsonResponse
    {
        $data = $request->validate([
            'stock_batch_uuid' => ['required', 'uuid'],
            'reel_id' => ['required', 'integer', 'exists:reels,id'],
            'reel_provider_id' => ['required', 'integer', 'exists:reel_providers,id'],
            'reel_warehouse_id' => ['required', 'integer', 'exists:reel_warehouses,id'],
            'corrected_quantity' => ['required', 'integer', 'min:0', 'max:10000'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $result = DB::transaction(function () use ($data) {
            $stockIds = DB::table('reel_stock_movements')->where('batch_uuid', $data['stock_batch_uuid'])
                ->where('transaction_type', 'opening')->pluck('reel_stock_id')->unique();
            $stocks = ReelStock::withoutGlobalScope('not_voided')->whereIn('id', $stockIds)
                ->where('reel_id', $data['reel_id'])->where('reel_provider_id', $data['reel_provider_id'])
                ->where('reel_warehouse_id', $data['reel_warehouse_id'])->lockForUpdate()->get();
            if ($stocks->isEmpty()) {
                throw ValidationException::withMessages(['stock_batch_uuid' => 'The selected stock-addition batch was not found.']);
            }

            $previous = $stocks->where('status', '!=', 'voided')->count();
            $change = $data['corrected_quantity'] - $previous;
            if ($change === 0) {
                throw ValidationException::withMessages(['corrected_quantity' => 'Enter a quantity different from the current quantity.']);
            }

            $affectedCodes = [];
            if ($change > 0) {
                $reel = Reel::lockForUpdate()->findOrFail($data['reel_id']);
                for ($i = 0; $i < $change; $i++) {
                    $stock = ReelStock::create([
                        'stock_code' => $this->nextStockCode(), 'reel_id' => $reel->id,
                        'reel_provider_id' => $data['reel_provider_id'], 'reel_warehouse_id' => $data['reel_warehouse_id'],
                        'original_length' => $reel->length, 'balance_length' => $reel->length,
                        'purchase_price' => $reel->unit_price, 'status' => 'full', 'is_active' => true,
                    ]);
                    ReelStockMovement::create([
                        'batch_uuid' => $data['stock_batch_uuid'], 'reel_stock_id' => $stock->id,
                        'transaction_type' => 'opening', 'stock_status' => 'full', 'length' => $stock->original_length,
                        'balance_before' => 0, 'balance_after' => $stock->balance_length,
                        'reel_warehouse_id' => $stock->reel_warehouse_id,
                        'remarks' => 'Added through stock quantity correction: ' . $data['reason'],
                        'created_by' => auth()->id(), 'created_at' => now(),
                    ]);
                    $affectedCodes[] = $stock->stock_code;
                }
            } else {
                $remove = abs($change);
                $eligible = $this->eligibleStocks($data['stock_batch_uuid'], $data['reel_id'], $data['reel_provider_id'], $data['reel_warehouse_id'])
                    ->lockForUpdate()->limit($remove)->get();
                if ($eligible->count() !== $remove) {
                    throw ValidationException::withMessages([
                        'corrected_quantity' => "Only {$eligible->count()} untouched Full reel(s) can be removed from this batch.",
                    ]);
                }
                foreach ($eligible as $stock) {
                    $affectedCodes[] = $stock->stock_code;
                    $stock->update(['status' => 'voided', 'is_active' => false]);
                    ReelStockMovement::create([
                        'batch_uuid' => (string) Str::uuid(), 'reel_stock_id' => $stock->id,
                        'transaction_type' => 'adjustment', 'stock_status' => 'voided', 'length' => 0,
                        'balance_before' => $stock->balance_length, 'balance_after' => $stock->balance_length,
                        'reel_warehouse_id' => $stock->reel_warehouse_id,
                        'remarks' => 'Voided through stock quantity correction: ' . $data['reason'],
                        'created_by' => auth()->id(), 'created_at' => now(),
                    ]);
                }
            }

            ReelStockCorrection::create([
                'stock_batch_uuid' => $data['stock_batch_uuid'], 'reel_id' => $data['reel_id'],
                'reel_provider_id' => $data['reel_provider_id'], 'reel_warehouse_id' => $data['reel_warehouse_id'],
                'previous_quantity' => $previous, 'corrected_quantity' => $data['corrected_quantity'],
                'quantity_change' => $change, 'affected_stock_codes' => $affectedCodes,
                'reason' => $data['reason'], 'created_by' => auth()->id(),
            ]);

            return ['message' => 'Reel stock quantity corrected successfully.', 'affected_codes' => $affectedCodes];
        });

        return response()->json($result);
    }

    public function updateReel(Request $request, Reel $reel): JsonResponse
    {
        $data = $request->validate([
            'reel_brand_id' => ['required', 'integer', 'exists:reel_brands,id'],
            'reel_type_id' => ['required', 'integer', 'exists:reel_types,id'],
            'reel_gsm_id' => ['required', 'integer', 'exists:reel_gsms,id'],
            'width' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'length' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $reel) {
            $locked = Reel::lockForUpdate()->findOrFail($reel->id);
            $before = $locked->only(['code', 'reel_brand_id', 'reel_type_id', 'reel_gsm_id', 'width', 'length', 'unit_price', 'selling_price', 'is_active', 'remarks']);
            $structural = ['reel_brand_id', 'reel_type_id', 'reel_gsm_id', 'width', 'length'];
            $structureChanged = collect($structural)->contains(fn ($key) => (string) $before[$key] !== (string) $data[$key]);

            if ($structureChanged && ReelStock::withoutGlobalScope('not_voided')->where('reel_id', $locked->id)
                ->where(function ($q) {
                    $q->where('status', '!=', 'full')->orWhereHas('movements', fn ($m) => $m->where('transaction_type', '!=', 'opening'));
                })->exists()) {
                throw ValidationException::withMessages(['reel_brand_id' => 'Brand, type, GSM, width, and length cannot be changed because this reel has stock activity.']);
            }

            $data['code'] = $this->reelCode($data);
            if (Reel::where('code', $data['code'])->whereKeyNot($locked->id)->exists()) {
                throw ValidationException::withMessages(['reel_brand_id' => 'A reel with these identifying details already exists.']);
            }
            $reason = $data['reason'];
            unset($data['reason']);
            $locked->update($data);

            if ($structureChanged) {
                $stocks = ReelStock::withoutGlobalScope('not_voided')->where('reel_id', $locked->id)->where('status', 'full')->get();
                foreach ($stocks as $stock) {
                    $stock->update(['original_length' => $locked->length, 'balance_length' => $locked->length, 'purchase_price' => $locked->unit_price]);
                    $stock->movements()->where('transaction_type', 'opening')->update([
                        'length' => $locked->length, 'balance_after' => $locked->length,
                    ]);
                }
            }

            ReelDetailCorrection::create([
                'reel_id' => $locked->id, 'before_values' => $before,
                'after_values' => $locked->fresh()->only(array_keys($before)),
                'reason' => $reason, 'created_by' => auth()->id(),
            ]);
        });

        return response()->json(['message' => 'Reel details corrected successfully.']);
    }

    public function history(): JsonResponse
    {
        $query = ReelStockCorrection::with(['reel:id,code', 'provider:id,name', 'warehouse:id,name'])->latest();
        return DataTables::eloquent($query)
            ->addColumn('reel_code', fn ($row) => $row->reel?->code ?? '—')
            ->addColumn('provider_name', fn ($row) => $row->provider?->name ?? '—')
            ->addColumn('warehouse_name', fn ($row) => $row->warehouse?->name ?? '—')
            ->editColumn('quantity_change', fn ($row) => ($row->quantity_change > 0 ? '+' : '') . $row->quantity_change)
            ->editColumn('created_at', fn ($row) => $row->created_at?->format('d M Y h:i a'))->toJson();
    }

    public function reelHistory(): JsonResponse
    {
        $query = ReelDetailCorrection::with('reel:id,code')->latest();
        return DataTables::eloquent($query)
            ->addColumn('reel_code', fn ($row) => $row->reel?->code ?? '—')
            ->addColumn('changes', function ($row) {
                $before = $row->before_values ?? [];
                $after = $row->after_values ?? [];
                return collect($after)->filter(fn ($value, $key) => (string) ($before[$key] ?? '') !== (string) $value)
                    ->keys()->map(fn ($key) => ucwords(str_replace(['reel_', '_id', '_'], ['', '', ' '], $key)))->implode(', ') ?: 'No value changes';
            })
            ->editColumn('created_at', fn ($row) => $row->created_at?->format('d M Y h:i a'))->toJson();
    }

    private function eligibleStocks(string $batchUuid, int $reelId, int $providerId, int $warehouseId)
    {
        $stockIds = DB::table('reel_stock_movements')->where('batch_uuid', $batchUuid)
            ->where('transaction_type', 'opening')->select('reel_stock_id');
        return ReelStock::query()->whereIn('id', $stockIds)->where('reel_id', $reelId)
            ->where('reel_provider_id', $providerId)->where('reel_warehouse_id', $warehouseId)
            ->where('status', 'full')->where('is_active', true)
            ->whereDoesntHave('saleItems')->whereDoesntHave('usages')->whereDoesntHave('productionRuns')
            ->whereDoesntHave('movements', fn ($q) => $q->where('transaction_type', '!=', 'opening'));
    }

    private function nextStockCode(): string
    {
        $last = (int) ReelStock::withoutGlobalScope('not_voided')->whereRaw("stock_code REGEXP '^[0-9]+$'")
            ->lockForUpdate()->max(DB::raw('CAST(stock_code AS UNSIGNED)'));
        return str_pad((string) ($last + 1), 6, '0', STR_PAD_LEFT);
    }

    private function reelCode(array $data): string
    {
        $brand = ReelBrand::findOrFail($data['reel_brand_id']);
        $type = ReelType::findOrFail($data['reel_type_id']);
        $gsm = ReelGsm::findOrFail($data['reel_gsm_id']);
        $part = fn ($value) => strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $value));
        $number = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
        return implode('-', [$part($brand->short_name ?: $brand->name), $part($type->short_name ?: $type->name), $part($gsm->gsm) . 'GSM', $number($data['width']), $number($data['length'])]);
    }
}
