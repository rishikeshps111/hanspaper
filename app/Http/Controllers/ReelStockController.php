<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReelStockRequest;
use App\Models\Reels\Reel;
use App\Models\Reels\ReelStock;
use App\Models\Reels\ReelStockMovement;
use App\Models\Reels\ReelWarehouse;
use App\Models\Reels\ReelBrand;
use App\Models\Reels\ReelProvider;
use App\Models\Reels\ReelType;
use App\Models\Reels\ReelGsm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ReelStockController extends Controller
{
    public function index(): View
    {
        return view('reels.stock.index', [
            'reels' => Reel::with(['brand', 'type', 'gsm'])->orderBy('code')->get(),
            'warehouses' => ReelWarehouse::orderBy('name')->get(),
            'brands' => ReelBrand::orderBy('name')->get(),
            'providers' => ReelProvider::orderBy('name')->get(),
            'types' => ReelType::orderBy('name')->get(),
            'gsms' => ReelGsm::orderBy('gsm')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = ReelStock::with(['reel.brand', 'reel.type', 'reel.gsm', 'provider', 'warehouse'])
            ->select('reel_stocks.*')->orderByDesc('reel_stocks.created_at');
        foreach (['reel_id', 'reel_warehouse_id', 'status'] as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }
        if ($request->filled('reel_provider_id')) {
            $query->where('reel_provider_id', $request->integer('reel_provider_id'));
        }
        foreach (['reel_brand_id', 'reel_type_id', 'reel_gsm_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->whereHas('reel', fn ($q) => $q->where($filter, $request->input($filter)));
            }
        }

        return DataTables::eloquent($query)->addIndexColumn()
            ->addColumn('reel_code', fn ($row) => $row->reel?->code)
            ->addColumn('provider_name', fn ($row) => $row->provider?->name ?? '—')
            ->editColumn('created_at', fn (ReelStock $stock) => $stock->created_at?->format('d M Y h:i a') ?? '—')
            ->editColumn('actual_code', fn ($row) => $row->actual_code ?: '—')
            ->addColumn('warehouse_name', fn ($row) => $row->warehouse?->name)
            ->addColumn('specification', fn ($row) => implode(' / ', array_filter([
                $row->reel?->brand?->short_name ?: $row->reel?->brand?->name,
                $row->reel?->type?->short_name ?: $row->reel?->type?->name,
                $row->reel?->gsm?->gsm,
            ])))
            ->addColumn('action', fn ($row) =>
                '<a class="btn btn-sm btn-outline-info" href="' . route('reels.stock.show', $row) . '"><i class="bx bx-show"></i></a>
                 <a class="btn btn-sm btn-outline-primary" href="' . route('reels.stock.edit', $row) . '"><i class="bx bx-edit"></i></a>')
            ->rawColumns(['action'])->toJson();
    }

    public function create(): View
    {
        return view('reels.stock.create', $this->formData());
    }

    public function store(ReelStockRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['stock_code'] = $this->nextStockCode();
            $data['balance_length'] = $data['original_length'];
            $data['status'] = 'full';
            $stock = ReelStock::create($data);
            ReelStockMovement::create([
                'batch_uuid' => (string) Str::uuid(),
                'reel_stock_id' => $stock->id, 'transaction_type' => 'opening',
                'stock_status' => 'full',
                'length' => $stock->original_length, 'balance_before' => 0,
                'balance_after' => $stock->balance_length, 'reel_warehouse_id' => $stock->reel_warehouse_id,
                'remarks' => 'Opening stock', 'created_by' => auth()->id(), 'created_at' => now(),
            ]);
        });
        return redirect()->route('reels.stock.index')->with('success', 'Reel stock created successfully.');
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reel_id' => ['required', 'exists:reels,id'],
            'reel_provider_id' => ['required', 'exists:reel_providers,id'],
            'reel_warehouse_id' => ['required', 'exists:reel_warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $createdCodes = DB::transaction(function () use ($validated) {
            $reel = Reel::with(['brand', 'type', 'gsm'])->lockForUpdate()->findOrFail($validated['reel_id']);
            $codes = [];
            $batchUuid = (string) Str::uuid();

            for ($number = 1; $number <= $validated['quantity']; $number++) {
                $code = $this->nextStockCode();
                $stock = ReelStock::create([
                    'stock_code' => $code,
                    'reel_id' => $reel->id,
                    'reel_provider_id' => $validated['reel_provider_id'],
                    'reel_warehouse_id' => $validated['reel_warehouse_id'],
                    'original_length' => $reel->length,
                    'balance_length' => $reel->length,
                    'purchase_price' => $reel->unit_price,
                    'status' => 'full',
                    'is_active' => true,
                ]);
                ReelStockMovement::create([
                    'batch_uuid' => $batchUuid,
                    'reel_stock_id' => $stock->id,
                    'transaction_type' => 'opening',
                    'stock_status' => 'full',
                    'length' => $stock->original_length,
                    'balance_before' => 0,
                    'balance_after' => $stock->balance_length,
                    'reel_warehouse_id' => $stock->reel_warehouse_id,
                    'remarks' => 'Opening stock added in bulk',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);
                $codes[] = $code;
            }

            return $codes;
        });

        return response()->json([
            'message' => count($createdCodes) . ' reel stock record(s) added successfully.',
            'codes' => $createdCodes,
        ]);
    }

    public function reelDetails(Reel $reel): View
    {
        $reel->load(['brand', 'type', 'gsm']);
        $statusCounts = ReelStock::where('reel_id', $reel->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $totalReels = $statusCounts->sum();
        $warehouses = ReelWarehouse::where('is_active', true)->orderBy('name')->get();

        return view('reels.stock.reel-details', compact('reel', 'statusCounts', 'totalReels', 'warehouses'));
    }

    public function reelStockData(Request $request, Reel $reel): JsonResponse
    {
        $query = ReelStock::with(['warehouse', 'provider', 'reel:id,code'])->withCount('usages')
            ->where('reel_id', $reel->id)
            ->select('reel_stocks.*')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('reel_warehouse_id')) {
            $query->where('reel_warehouse_id', $request->integer('reel_warehouse_id'));
        }
        if ($request->filled('actual_code')) {
            $query->where('actual_code', 'like', '%' . $request->input('actual_code') . '%');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn (ReelStock $stock) =>
                '<input type="checkbox" class="form-check-input stock-checkbox" data-id="' . $stock->id .
                '" data-code="' . e($stock->stock_code) .
                '" data-reel-code="' . e($stock->reel?->code ?? '') .
                '" data-provider="' . e($stock->provider?->name ?? '—') .
                '" data-added-date="' . e($stock->created_at?->format('d M Y h:i a') ?? '—') . '">')
            ->addColumn('warehouse_name', fn (ReelStock $stock) => $stock->warehouse?->name ?? '—')
            ->addColumn('provider_name', fn (ReelStock $stock) => $stock->provider?->name ?? '—')
            ->editColumn('created_at', fn (ReelStock $stock) => $stock->created_at?->format('d M Y h:i a') ?? '—')
            ->editColumn('actual_code', fn (ReelStock $stock) => $stock->actual_code ?: '—')
            ->addColumn('action', fn (ReelStock $stock) =>
                (in_array($stock->status, ['bit', 'finished'], true)
                    ? '<a class="btn btn-sm btn-outline-info" href="' . route('reels.stock.usage', $stock) .
                        '" title="Production Usage"><i class="bx bx-history"></i> ' . $stock->usages_count . '</a> '
                    : '') .
                '<button type="button" class="btn btn-sm btn-outline-primary edit-actual-code" data-id="' . $stock->id .
                '" data-code="' . e($stock->actual_code ?? '') . '" data-stock-code="' . e($stock->stock_code) .
                '" title="Add/Edit Actual Code"><i class="bx bx-edit"></i></button>
                 <button type="button" class="btn btn-sm btn-outline-dark print-stock-barcode" data-code="' .
                e($stock->stock_code) . '" data-reel-code="' . e($stock->reel?->code ?? '') .
                '" data-provider="' . e($stock->provider?->name ?? '—') .
                '" data-added-date="' . e($stock->created_at?->format('d M Y h:i a') ?? '—') .
                '" title="Print Barcode"><i class="bx bx-barcode"></i></button>')
            ->rawColumns(['select', 'action'])
            ->toJson();
    }

    public function reelStockStats(Request $request, Reel $reel): JsonResponse
    {
        $query = ReelStock::where('reel_id', $reel->id);
        if ($request->filled('reel_warehouse_id')) {
            $query->where('reel_warehouse_id', $request->integer('reel_warehouse_id'));
        }
        if ($request->boolean('transferable')) {
            $query->where('is_active', true);
        }
        $counts = $query->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return response()->json([
            'total' => $counts->sum(),
            'full' => $counts['full'] ?? 0,
            'bit' => $counts['bit'] ?? 0,
            'finished' => $counts['finished'] ?? 0,
            'sold' => $counts['sold'] ?? 0,
        ]);
    }

    public function updateActualCode(Request $request, ReelStock $stock): JsonResponse
    {
        $validated = $request->validate([
            'actual_code' => ['nullable', 'string', 'max:100'],
        ]);
        $stock->update(['actual_code' => $validated['actual_code'] ?: null]);

        return response()->json(['message' => 'Actual code updated successfully.']);
    }

    public function transferWarehouse(Request $request, Reel $reel): JsonResponse
    {
        $validated = $request->validate([
            'source_warehouse_id' => ['required', 'integer', 'exists:reel_warehouses,id'],
            'destination_warehouse_id' => ['required', 'integer', 'exists:reel_warehouses,id'],
            'reel_provider_id' => ['nullable', 'integer', 'exists:reel_providers,id'],
            'full_quantity' => ['required', 'integer', 'min:0'],
            'bit_quantity' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);
        if ((int) $validated['full_quantity'] + (int) $validated['bit_quantity'] < 1) {
            throw ValidationException::withMessages(['full_quantity' => 'Enter at least one Full or Bit reel to transfer.']);
        }
        if ((int) $validated['source_warehouse_id'] === (int) $validated['destination_warehouse_id']) {
            throw ValidationException::withMessages(['destination_warehouse_id' => 'Destination warehouse must differ from source warehouse.']);
        }

        $source = ReelWarehouse::whereKey($validated['source_warehouse_id'])->where('is_active', true)->firstOrFail();
        $destination = ReelWarehouse::whereKey($validated['destination_warehouse_id'])
            ->where('is_active', true)->firstOrFail();

        $transferred = DB::transaction(function () use ($validated, $source, $destination, $reel) {
            $stocks = collect();
            $batchUuid = (string) Str::uuid();
            foreach (['full' => (int) $validated['full_quantity'], 'bit' => (int) $validated['bit_quantity']] as $status => $quantity) {
                if ($quantity === 0) continue;
                $selected = ReelStock::where('reel_id', $reel->id)
                    ->when($validated['reel_provider_id'] ?? null, fn ($query, $providerId) => $query->where('reel_provider_id', $providerId))
                    ->where('reel_warehouse_id', $source->id)
                    ->where('status', $status)
                    ->where('is_active', true)
                    ->orderBy('created_at')->orderBy('id')
                    ->lockForUpdate()->limit($quantity)->get();
                if ($selected->count() !== $quantity) {
                    throw ValidationException::withMessages([
                        "{$status}_quantity" => "Only {$selected->count()} {$status} reel(s) are currently available in {$source->name}.",
                    ]);
                }
                $stocks = $stocks->concat($selected);
            }

            foreach ($stocks as $stock) {
                $sourceWarehouseId = $stock->reel_warehouse_id;
                $balance = $stock->balance_length;
                ReelStockMovement::create([
                    'batch_uuid' => $batchUuid,
                    'reel_stock_id' => $stock->id, 'transaction_type' => 'transfer_out',
                    'stock_status' => $stock->status,
                    'length' => $balance, 'balance_before' => $balance, 'balance_after' => $balance,
                    'reel_warehouse_id' => $sourceWarehouseId, 'remarks' => $validated['remarks'] ?? 'Warehouse transfer',
                    'created_by' => auth()->id(), 'created_at' => now(),
                ]);
                $stock->update(['reel_warehouse_id' => $destination->id]);
                ReelStockMovement::create([
                    'batch_uuid' => $batchUuid,
                    'reel_stock_id' => $stock->id, 'transaction_type' => 'transfer_in',
                    'stock_status' => $stock->status,
                    'length' => $balance, 'balance_before' => $balance, 'balance_after' => $balance,
                    'reel_warehouse_id' => $destination->id, 'remarks' => $validated['remarks'] ?? 'Warehouse transfer',
                    'created_by' => auth()->id(), 'created_at' => now(),
                ]);
            }

            return $stocks->count();
        });

        return response()->json([
            'message' => "{$transferred} physical reel(s) transferred from {$source->name} to {$destination->name}.",
        ]);
    }

    public function edit(ReelStock $stock): View
    {
        abort_if($stock->movements()->where('transaction_type', '!=', 'opening')->exists(), 422, 'Stock with transactions cannot be edited.');
        return view('reels.stock.edit', array_merge($this->formData(false), compact('stock')));
    }

    public function update(ReelStockRequest $request, ReelStock $stock): RedirectResponse
    {
        abort_if($stock->movements()->where('transaction_type', '!=', 'opening')->exists(), 422, 'Stock with transactions cannot be edited.');
        DB::transaction(function () use ($request, $stock) {
            $data = $request->validated();
            $data['balance_length'] = $data['original_length'];
            $stock->update($data);
            $stock->movements()->where('transaction_type', 'opening')->update([
                'length' => $data['original_length'], 'balance_after' => $data['original_length'],
                'reel_warehouse_id' => $data['reel_warehouse_id'],
                'reel_provider_id' => $data['reel_provider_id'],
            ]);
        });
        return redirect()->route('reels.stock.index')->with('success', 'Reel stock updated successfully.');
    }

    public function show(ReelStock $stock): View
    {
        $stock->load(['reel.brand', 'reel.type', 'reel.gsm', 'provider', 'warehouse', 'movements.provider']);
        return view('reels.stock.show', compact('stock'));
    }

    public function usage(ReelStock $stock): View
    {
        abort_unless(in_array($stock->status, ['bit', 'finished'], true), 404);
        $stock->load([
            'reel', 'warehouse',
            'usages' => fn ($query) => $query->with(['production.item', 'machine'])->orderByDesc('created_at'),
        ]);
        return view('reels.stock.usage', compact('stock'));
    }

    private function formData(bool $active = true): array
    {
        return [
            'reels' => Reel::with(['brand', 'type', 'gsm'])->when($active, fn ($q) => $q->where('is_active', true))->orderBy('code')->get(),
            'providers' => ReelProvider::when($active, fn ($q) => $q->where('is_active', true))->orderBy('name')->get(),
            'warehouses' => ReelWarehouse::when($active, fn ($q) => $q->where('is_active', true))->orderBy('name')->get(),
        ];
    }

    private function nextStockCode(): string
    {
        $last = (int) ReelStock::query()
            ->whereRaw("stock_code REGEXP '^[0-9]+$'")
            ->lockForUpdate()
            ->max(DB::raw('CAST(stock_code AS UNSIGNED)'));
        return str_pad((string) ($last + 1), 6, '0', STR_PAD_LEFT);
    }
}
