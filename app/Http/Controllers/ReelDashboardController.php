<?php

namespace App\Http\Controllers;

use App\Models\Party\Party;
use App\Models\Reels\Reel;
use App\Models\Reels\ReelBrand;
use App\Models\Reels\ReelGsm;
use App\Models\Reels\ReelProvider;
use App\Models\Reels\ReelStock;
use App\Models\Reels\ReelSale;
use App\Models\Reels\ReelSaleItem;
use App\Models\Reels\ReelStockMovement;
use App\Models\Reels\ReelType;
use App\Models\Reels\ReelWarehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReelDashboardController extends Controller
{
    public function index(): View
    {
        return view('reels.dashboard', [
            'warehouses' => ReelWarehouse::where('is_active', true)->orderBy('name')->get(),
            'brands' => ReelBrand::where('is_active', true)->orderBy('name')->get(),
            'types' => ReelType::where('is_active', true)->orderBy('name')->get(),
            'gsms' => ReelGsm::where('is_active', true)->orderBy('gsm')->get(),
            'providers' => ReelProvider::where('is_active', true)->orderBy('name')->get(),
            'customers' => Party::whereIn('party_type', ['customer', 'both'])->where('status', 1)
                ->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'mobile']),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $warehouses = ReelWarehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $stocks = ReelStock::query()
            ->with(['reel.brand:id,name', 'reel.type:id,name', 'reel.gsm:id,gsm'])
            ->where('is_active', true)
            ->whereIn('status', ['full', 'bit'])
            ->when($request->filled('reel_brand_id'), fn ($query) =>
                $query->whereHas('reel', fn ($reel) => $reel->where('reel_brand_id', $request->integer('reel_brand_id'))))
            ->when($request->filled('reel_type_id'), fn ($query) =>
                $query->whereHas('reel', fn ($reel) => $reel->where('reel_type_id', $request->integer('reel_type_id'))))
            ->when($request->filled('reel_gsm_id'), fn ($query) =>
                $query->whereHas('reel', fn ($reel) => $reel->where('reel_gsm_id', $request->integer('reel_gsm_id'))))
            ->when($request->filled('width'), fn ($query) =>
                $query->whereHas('reel', fn ($reel) => $reel->where('width', round((float) $request->input('width'), 2))))
            ->when($request->filled('length'), fn ($query) =>
                $query->whereHas('reel', fn ($reel) => $reel->where('length', round((float) $request->input('length'), 2))))
            ->get();

        $rows = $stocks->groupBy('reel_id')
            ->map(function (Collection $group) use ($warehouses) {
                /** @var ReelStock $first */
                $first = $group->first();
                $reel = $first->reel;
                $row = [
                    'reel_id' => $reel->id,
                    'reel_code' => $reel->code,
                    'brand_name' => $reel->brand?->name ?? '—',
                    'type_name' => $reel->type?->name ?? '—',
                    'gsm_value' => $reel->gsm?->gsm ?? '—',
                    'width' => $this->compactNumber($reel->width),
                    'length' => $this->compactNumber($reel->length),
                ];

                foreach ($warehouses as $warehouse) {
                    $warehouseStocks = $group->where('reel_warehouse_id', $warehouse->id);
                    foreach (['full', 'bit'] as $status) {
                        $row["warehouse_{$warehouse->id}_{$status}"] = $this->quantityButton(
                            $reel, $warehouseStocks->where('status', $status)->count(), $warehouse->id, $status
                        );
                    }
                }

                $row['full_total'] = $this->quantityButton($reel, $group->where('status', 'full')->count(), null, 'full');
                $row['bit_total'] = $this->quantityButton($reel, $group->where('status', 'bit')->count(), null, 'bit');
                return $row;
            })
            ->sortBy(fn ($row) => implode('|', [
                $row['brand_name'], $row['type_name'],
                str_pad((string) $row['gsm_value'], 6, '0', STR_PAD_LEFT),
                str_pad((string) $row['width'], 14, '0', STR_PAD_LEFT),
                str_pad((string) $row['length'], 14, '0', STR_PAD_LEFT),
            ]), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $rawColumns = collect($warehouses)->map(fn ($warehouse) => "warehouse_{$warehouse->id}_full")
            ->merge(collect($warehouses)->map(fn ($warehouse) => "warehouse_{$warehouse->id}_bit"))
            ->push('full_total')->push('bit_total')->all();

        return DataTables::of($rows)->addIndexColumn()->rawColumns($rawColumns)->toJson();
    }

    public function stocks(Request $request, Reel $reel): JsonResponse
    {
        $query = ReelStock::query()
            ->with(['provider:id,name', 'warehouse:id,name'])
            ->where('reel_id', $reel->id)
            ->where('is_active', true)
            ->whereIn('status', ['full', 'bit'])
            ->when($request->filled('reel_warehouse_id'), fn ($query) =>
                $query->where('reel_warehouse_id', $request->integer('reel_warehouse_id')))
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->input('status')))
            ->select('reel_stocks.*')
            ->orderByDesc('created_at');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn (ReelStock $stock) => '<input type="checkbox" class="form-check-input dashboard-stock-checkbox" data-id="' .
                $stock->id . '" data-status="' . e($stock->status) . '" data-price="' . e($reel->selling_price) .
                '" data-code="' . e($stock->stock_code) . '" data-actual-code="' . e($stock->actual_code ?? '') .
                '" data-reel-code="' . e($reel->code) . '" data-provider="' . e($stock->provider?->name ?? '—') .
                '" data-added-date="' . e($stock->created_at?->format('d M Y h:i a') ?? '—') . '">')
            ->editColumn('actual_code', fn (ReelStock $stock) => $stock->actual_code ?: '—')
            ->addColumn('provider_name', fn (ReelStock $stock) => $stock->provider?->name ?? '—')
            ->addColumn('warehouse_name', fn (ReelStock $stock) => $stock->warehouse?->name ?? '—')
            ->editColumn('created_at', fn (ReelStock $stock) => $stock->created_at?->format('d M Y h:i a') ?? '—')
            ->addColumn('action', fn (ReelStock $stock) =>
                '<div class="btn-group"><button type="button" class="btn btn-sm btn-outline-primary dashboard-stock-action edit-dashboard-actual" data-id="' .
                $stock->id . '" data-code="' . e($stock->actual_code ?? '') . '" title="Edit Actual Code"><i class="bx bx-edit"></i></button>' .
                '<button type="button" class="btn btn-sm btn-outline-dark dashboard-stock-action print-dashboard-stock" data-code="' . e($stock->stock_code) .
                '" data-reel-code="' . e($reel->code) . '" data-provider="' . e($stock->provider?->name ?? '—') .
                '" data-added-date="' . e($stock->created_at?->format('d M Y h:i a') ?? '—') .
                '" title="Print Barcode"><i class="bx bx-barcode"></i></button></div>')
            ->rawColumns(['select', 'action'])->toJson();
    }

    public function transfer(Request $request, Reel $reel): JsonResponse
    {
        $validated = $request->validate([
            'stock_ids' => ['required', 'array', 'min:1'],
            'stock_ids.*' => ['integer', 'distinct', 'exists:reel_stocks,id'],
            'source_warehouse_id' => ['required', 'integer', 'exists:reel_warehouses,id'],
            'destination_warehouse_id' => ['required', 'integer', 'different:source_warehouse_id', 'exists:reel_warehouses,id'],
        ]);

        $count = DB::transaction(function () use ($validated, $reel) {
            $stocks = ReelStock::whereIn('id', $validated['stock_ids'])->lockForUpdate()->get();
            if ($stocks->count() !== count($validated['stock_ids']) || $stocks->contains(fn ($stock) =>
                (int) $stock->reel_id !== (int) $reel->id || !$stock->is_active ||
                !in_array($stock->status, ['full', 'bit'], true) ||
                (int) $stock->reel_warehouse_id !== (int) $validated['source_warehouse_id'])) {
                throw ValidationException::withMessages(['stock_ids' => 'One or more selected stocks are no longer available in the source warehouse.']);
            }

            $batchUuid = (string) Str::uuid();
            foreach ($stocks as $stock) {
                $balance = $stock->balance_length;
                foreach ([
                    ['transfer_out', (int) $validated['source_warehouse_id']],
                    ['transfer_in', (int) $validated['destination_warehouse_id']],
                ] as [$type, $warehouseId]) {
                    ReelStockMovement::create([
                        'batch_uuid' => $batchUuid, 'reel_stock_id' => $stock->id,
                        'transaction_type' => $type, 'stock_status' => $stock->status,
                        'length' => $balance, 'balance_before' => $balance, 'balance_after' => $balance,
                        'reel_warehouse_id' => $warehouseId, 'remarks' => 'Warehouse transfer from Reel Dashboard',
                        'created_by' => auth()->id(), 'created_at' => now(),
                    ]);
                    if ($type === 'transfer_out') {
                        $stock->update(['reel_warehouse_id' => $validated['destination_warehouse_id']]);
                    }
                }
            }
            return $stocks->count();
        });

        return response()->json(['message' => "{$count} reel stock(s) transferred successfully."]);
    }

    public function sale(Request $request, Reel $reel): JsonResponse
    {
        $validated = $request->validate([
            'stock_ids' => ['required', 'array', 'min:1'],
            'stock_ids.*' => ['integer', 'distinct', 'exists:reel_stocks,id'],
            'customer_id' => ['required', 'integer', 'exists:parties,id'],
            'sale_date' => ['required', 'date'],
        ]);

        $sale = DB::transaction(function () use ($validated, $reel) {
            $stocks = ReelStock::whereIn('id', $validated['stock_ids'])->lockForUpdate()->get();
            if ($stocks->count() !== count($validated['stock_ids']) || $stocks->contains(fn ($stock) =>
                (int) $stock->reel_id !== (int) $reel->id || !$stock->is_active || $stock->status !== 'full')) {
                throw ValidationException::withMessages(['stock_ids' => 'Only currently available Full reel stocks can be sold.']);
            }

            $unitPrice = (float) $reel->selling_price;
            $total = round($stocks->count() * $unitPrice, 2);
            $sale = ReelSale::create([
                'customer_id' => $validated['customer_id'], 'sale_date' => $validated['sale_date'],
                'subtotal' => $total, 'discount' => 0, 'is_gst_applicable' => false,
                'sgst_percentage' => 0, 'sgst_amount' => 0, 'cgst_percentage' => 0, 'cgst_amount' => 0,
                'total' => $total, 'remarks' => null,
            ]);
            $sale->update([
                'sale_code' => 'RSALE' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
                'invoice_number' => 'RINV' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
            ]);
            $batchUuid = (string) Str::uuid();
            foreach ($stocks as $stock) {
                $before = (float) $stock->balance_length;
                ReelSaleItem::create([
                    'reel_sale_id' => $sale->id, 'reel_stock_id' => $stock->id,
                    'length' => $before, 'unit_price' => $unitPrice, 'discount' => 0,
                    'total' => $unitPrice, 'balance_before' => $before, 'balance_after' => 0,
                ]);
                $stock->update(['balance_length' => 0, 'status' => 'sold']);
                ReelStockMovement::create([
                    'batch_uuid' => $batchUuid, 'reel_stock_id' => $stock->id,
                    'transaction_type' => 'sale', 'stock_status' => 'full',
                    'length' => $before, 'balance_before' => $before, 'balance_after' => 0,
                    'reference_type' => ReelSale::class, 'reference_id' => $sale->id,
                    'customer_id' => $sale->customer_id, 'reel_warehouse_id' => $stock->reel_warehouse_id,
                    'remarks' => null, 'created_by' => auth()->id(), 'created_at' => now(),
                ]);
            }
            return $sale;
        });

        return response()->json([
            'message' => 'Reel sale completed successfully.',
            'invoice_number' => $sale->invoice_number,
            'total' => (float) $sale->total,
        ]);
    }

    private function quantityButton(Reel $reel, int $quantity, ?int $warehouseId = null, ?string $status = null): string
    {
        if ($quantity === 0) return '<span class="text-muted">0</span>';

        return '<button type="button" class="btn btn-sm btn-link fw-bold p-0 dashboard-stock-details" data-reel-id="' .
            $reel->id . '" data-reel-code="' . e($reel->code) . '" data-warehouse-id="' .
            ($warehouseId ?: '') . '" data-status="' . ($status ?: '') . '">' . $quantity . '</button>';
    }

    private function compactNumber(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
