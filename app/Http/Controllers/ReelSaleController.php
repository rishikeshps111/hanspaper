<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReelSaleRequest;
use App\Models\Party\Party;
use App\Models\Reels\ReelSale;
use App\Models\Reels\ReelSaleItem;
use App\Models\Reels\ReelStock;
use App\Models\Reels\ReelStockMovement;
use App\Models\Reels\Reel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Yajra\DataTables\Facades\DataTables;

class ReelSaleController extends Controller
{
    public function index(): View
    {
        return view('reels.sales.index', [
            'customers' => Party::whereIn('party_type', ['customer', 'both'])->where('status', 1)
                ->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'mobile']),
            'reels' => Reel::orderBy('code')->get(['id', 'code']),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = ReelSale::query()
            ->with(['customer', 'items.stock.reel:id,code'])
            ->withCount('items')
            ->when($request->filled('customer_id'), fn($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('sale_date'), fn($query) => $query->whereDate('sale_date', $request->input('sale_date')))
            ->when($request->filled('reel_id'), fn($query) => $query->whereHas('items.stock', fn($stock) =>
            $stock->where('reel_id', $request->integer('reel_id'))))
            ->orderByDesc('created_at');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('customer_name', fn($sale) => trim(($sale->customer?->first_name ?? '') . ' ' . ($sale->customer?->last_name ?? '')))
            ->addColumn('reels_badges', fn($sale) => $sale->items->pluck('stock.reel.code')->filter()->unique()
                ->map(fn($code) => '<span class="badge bg-primary me-1 mb-1">' . e($code) . '</span>')->implode(''))
            ->addColumn('unit_selling_prices', fn($sale) => $sale->items->pluck('unit_price')->unique()
                ->map(fn($price) => number_format((float) $price, 2))->implode(', '))
            ->editColumn('sale_date', fn($sale) => $sale->sale_date?->format('d M Y'))
            ->addColumn(
                'action',
                fn($sale) =>
                '<div class="btn-group">' .
                    '<a class="btn btn-sm btn-outline-info" title="Sale details" href="' . route('reels.sales.show', $sale) . '"><i class="bx bx-show"></i></a>' .
                    // '<a class="btn btn-sm btn-outline-secondary" title="Physical reel stocks" href="' . route('reels.sales.stocks', $sale) . '"><i class="bx bx-list-ul"></i></a>' .
                    // '<a class="btn btn-sm btn-outline-danger" title="Download invoice" href="' . route('reels.sales.invoice', $sale) . '"><i class="bx bx-download"></i></a>' .
                    '</div>'
            )
            ->rawColumns(['reels_badges', 'action'])->toJson();
    }

    public function create(): View
    {
        $customers = Party::whereIn('party_type', ['customer', 'both'])->where('status', 1)->orderBy('first_name')->get();
        $reels = Reel::where('is_active', true)
            ->whereHas('stocks', fn($query) => $query->where('is_active', true)->where('status', 'full'))
            ->orderBy('code')->get(['id', 'code', 'selling_price']);

        $prefillReelId = request()->integer('reel_id') ?: null;
        $prefillWarehouseId = request()->integer('warehouse_id') ?: null;

        return view('reels.sales.create', compact('customers', 'reels', 'prefillReelId', 'prefillWarehouseId'));
    }

    public function availability(Reel $reel): JsonResponse
    {
        abort_unless($reel->is_active, 404);
        $availability = ReelStock::query()
            ->join('reel_warehouses', 'reel_warehouses.id', '=', 'reel_stocks.reel_warehouse_id')
            ->where('reel_stocks.reel_id', $reel->id)
            ->where('reel_stocks.is_active', true)
            ->where('reel_stocks.status', 'full')
            ->where('reel_warehouses.is_active', true)
            ->groupBy('reel_stocks.reel_warehouse_id', 'reel_warehouses.name')
            ->selectRaw('reel_stocks.reel_warehouse_id as warehouse_id, reel_warehouses.name as warehouse_name, COUNT(*) as available')
            ->orderBy('reel_warehouses.name')->get();

        return response()->json([
            'selling_price' => (float) $reel->selling_price,
            'availability' => $availability,
        ]);
    }

    public function store(ReelSaleRequest $request): RedirectResponse
    {
        $sale = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $preparedLines = collect();

            foreach ($validated['items'] as $itemIndex => $item) {
                $reel = Reel::where('is_active', true)->findOrFail($item['reel_id']);
                $stocksForLine = collect();
                $warehouseQuantities = collect($item['warehouse_quantities'])
                    ->groupBy('warehouse_id')
                    ->map(fn($lines) => $lines->sum(fn($line) => (int) $line['quantity']));

                foreach ($warehouseQuantities as $warehouseId => $quantity) {
                    if ($quantity < 1) continue;
                    $stocks = ReelStock::where('reel_id', $reel->id)
                        ->where('reel_warehouse_id', $warehouseId)
                        ->where('is_active', true)->where('status', 'full')
                        ->orderBy('created_at')->orderBy('id')
                        ->lockForUpdate()->limit($quantity)->get();
                    if ($stocks->count() !== $quantity) {
                        throw ValidationException::withMessages([
                            "items.{$itemIndex}.warehouse_quantities" =>
                            "Only {$stocks->count()} Full {$reel->code} reel(s) are currently available in the selected warehouse.",
                        ]);
                    }
                    $stocksForLine = $stocksForLine->concat($stocks);
                }

                if ($stocksForLine->isEmpty()) {
                    throw ValidationException::withMessages([
                        "items.{$itemIndex}.warehouse_quantities" => "Enter a quantity for {$reel->code}.",
                    ]);
                }

                $unitPrice = (float) $reel->selling_price;
                $gross = round($stocksForLine->count() * $unitPrice, 2);
                $discount = round((float) ($item['discount'] ?? 0), 2);
                if ($discount > $gross) {
                    throw ValidationException::withMessages([
                        "items.{$itemIndex}.discount" => "Discount for {$reel->code} cannot exceed its amount.",
                    ]);
                }

                $preparedLines->push(compact('reel', 'stocksForLine', 'unitPrice', 'gross', 'discount'));
            }

            $subtotal = round($preparedLines->sum('gross'), 2);
            $discount = round($preparedLines->sum('discount'), 2);
            $taxableAmount = round($subtotal - $discount, 2);
            $gstApplicable = (bool) ($validated['is_gst_applicable'] ?? false);
            $sgstPercentage = $gstApplicable ? (float) ($validated['sgst_percentage'] ?? 0) : 0;
            $cgstPercentage = $gstApplicable ? (float) ($validated['cgst_percentage'] ?? 0) : 0;
            $sgstAmount = round($taxableAmount * $sgstPercentage / 100, 2);
            $cgstAmount = round($taxableAmount * $cgstPercentage / 100, 2);

            $sale = ReelSale::create([
                'customer_id' => $validated['customer_id'],
                'sale_date' => $validated['sale_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'is_gst_applicable' => $gstApplicable,
                'sgst_percentage' => $sgstPercentage,
                'sgst_amount' => $sgstAmount,
                'cgst_percentage' => $cgstPercentage,
                'cgst_amount' => $cgstAmount,
                'total' => round($taxableAmount + $sgstAmount + $cgstAmount, 2),
                'remarks' => $validated['remarks'] ?? null,
            ]);
            $sale->update([
                'sale_code' => 'RSALE' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
                'invoice_number' => 'RINV' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
            ]);
            $batchUuid = (string) Str::uuid();

            foreach ($preparedLines as $line) {
                $count = $line['stocksForLine']->count();
                $allocatedDiscount = 0;
                foreach ($line['stocksForLine']->values() as $stockIndex => $stock) {
                    $stockDiscount = $stockIndex === $count - 1
                        ? round($line['discount'] - $allocatedDiscount, 2)
                        : round($line['discount'] / $count, 2);
                    $allocatedDiscount += $stockDiscount;
                    $before = (float) $stock->balance_length;

                    ReelSaleItem::create([
                        'reel_sale_id' => $sale->id,
                        'reel_stock_id' => $stock->id,
                        'length' => $before,
                        'unit_price' => $line['unitPrice'],
                        'discount' => $stockDiscount,
                        'total' => round($line['unitPrice'] - $stockDiscount, 2),
                        'balance_before' => $before,
                        'balance_after' => 0,
                    ]);
                    $stock->update(['balance_length' => 0, 'status' => 'sold']);
                    ReelStockMovement::create([
                        'batch_uuid' => $batchUuid,
                        'reel_stock_id' => $stock->id,
                        'transaction_type' => 'sale',
                        'stock_status' => 'full',
                        'length' => $before,
                        'balance_before' => $before,
                        'balance_after' => 0,
                        'reference_type' => ReelSale::class,
                        'reference_id' => $sale->id,
                        'customer_id' => $sale->customer_id,
                        'reel_warehouse_id' => $stock->reel_warehouse_id,
                        'remarks' => $validated['remarks'] ?? null,
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }
            }
            return $sale;
        });

        return redirect()->route('reels.sales.show', $sale)->with('success', 'Reel sale completed successfully.');
    }

    public function show(ReelSale $sale): View
    {
        $sale->load(['customer', 'items.stock.reel', 'items.stock.provider', 'items.stock.warehouse']);
        $products = $this->groupedProducts($sale);
        return view('reels.sales.show', compact('sale', 'products'));
    }

    public function stocks(ReelSale $sale): View
    {
        $sale->load(['items.stock.reel', 'items.stock.warehouse']);
        return view('reels.sales.stocks', compact('sale'));
    }

    public function invoice(ReelSale $sale)
    {
        $sale->load(['customer', 'items.stock.reel']);
        $products = $this->groupedProducts($sale);
        $html = view('reels.sales.invoice', [
            'sale' => $sale,
            'products' => $products,
            'company' => app('company'),
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'default_font' => 'dejavusans',
        ]);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . ($sale->invoice_number ?: $sale->sale_code) . '.pdf"',
        ]);
    }

    private function groupedProducts(ReelSale $sale)
    {
        return $sale->items->groupBy(fn($item) => $item->stock->reel_id)->map(function ($items) {
            $first = $items->first();
            return [
                'reel' => $first->stock->reel,
                'quantity' => $items->count(),
                'unit_price' => (float) $first->unit_price,
                'discount' => round($items->sum(fn($item) => (float) $item->discount), 2),
                'amount' => round($items->sum(fn($item) => (float) $item->total), 2),
                'warehouses' => $items->groupBy(fn($item) => $item->stock->reel_warehouse_id)
                    ->map(fn($warehouseItems) => [
                        'name' => $warehouseItems->first()->stock->warehouse?->name ?? 'Unknown warehouse',
                        'quantity' => $warehouseItems->count(),
                    ])->values(),
            ];
        })->values();
    }
}
