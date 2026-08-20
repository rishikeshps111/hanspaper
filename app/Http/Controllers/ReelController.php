<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReelRequest;
use App\Models\Reels\Reel;
use App\Models\Reels\ReelBrand;
use App\Models\Reels\ReelGsm;
use App\Models\Reels\ReelProvider;
use App\Models\Reels\ReelType;
use App\Models\Reels\ReelWarehouse;
use App\Models\Reels\ReelStockMovement;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ReelController extends Controller
{
    public function index(): View
    {
        return view('reels.manage.index', array_merge($this->settings(false), [
            'warehouses' => ReelWarehouse::where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));
        $page = max(1, $request->integer('page', 1));
        $perPage = 20;
        $query = Reel::query()
            ->when($term !== '', fn ($query) => $query->where('code', 'like', "%{$term}%"))
            ->orderBy('code');
        $reels = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get(['id', 'code', 'is_active']);

        return response()->json([
            'results' => $reels->take($perPage)->map(fn (Reel $reel) => [
                'id' => $reel->id,
                'text' => $reel->code . ($reel->is_active ? '' : ' (Inactive)'),
            ])->values(),
            'pagination' => ['more' => $reels->count() > $perPage],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = Reel::query()
            ->with(['brand:id,name', 'type:id,name', 'gsm:id,gsm'])
            ->select('reels.*')
            ->withCount([
                'stocks',
                'stocks as full_stock_count' => fn ($query) => $query->where('status', 'full'),
                'stocks as bit_stock_count' => fn ($query) => $query->where('status', 'bit'),
                'stocks as finished_stock_count' => fn ($query) => $query->where('status', 'finished'),
                'stocks as sold_stock_count' => fn ($query) => $query->where('status', 'sold'),
            ])
            ->orderByDesc('reels.created_at');

        if ($request->filled('reel_brand_id')) {
            $query->where('reel_brand_id', $request->integer('reel_brand_id'));
        }
        if ($request->filled('reel_type_id')) {
            $query->where('reel_type_id', $request->integer('reel_type_id'));
        }
        if ($request->filled('reel_gsm_id')) {
            $query->where('reel_gsm_id', $request->integer('reel_gsm_id'));
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('brand_name', fn(Reel $reel) => $reel->brand?->name ?? '—')
            ->addColumn('type_name', fn(Reel $reel) => $reel->type?->name ?? '—')
            ->addColumn('gsm_value', fn(Reel $reel) => $reel->gsm?->gsm ?? '—')
            ->editColumn('is_active', fn(Reel $reel) => $reel->is_active ? 1 : 0)
            ->editColumn('created_at', fn(Reel $reel) => $reel->created_at?->format('Y-m-d H:i:s'))
            ->filterColumn('brand_name', fn($query, $keyword) => $query->whereHas('brand', fn($q) => $q->where('name', 'like', "%{$keyword}%")))
            ->filterColumn('type_name', fn($query, $keyword) => $query->whereHas('type', fn($q) => $q->where('name', 'like', "%{$keyword}%")))
            ->filterColumn('gsm_value', fn($query, $keyword) => $query->whereHas('gsm', fn($q) => $q->where('gsm', 'like', "%{$keyword}%")))
            ->addColumn('action', function (Reel $reel) {
                $editUrl = route('reels.manage.edit', $reel);
                $detailsUrl = route('reels.manage.details', $reel);
                $stockUrl = route('reels.manage.stock', $reel);
                return '<button type="button" class="btn btn-sm btn-outline-success add-reel-stock" data-reel-id="' . $reel->id . '" data-reel-code="' . e($reel->code) . '" title="Add Stock"><i class="bx bx-plus"></i></button>
                    <a href="' . e($stockUrl) . '" class="btn btn-sm btn-outline-info" title="Stock Details"><i class="bx bx-package"></i> ' . $reel->stocks_count . '</a>
                    <a href="' . e($detailsUrl) . '" class="btn btn-sm btn-outline-dark" title="Reel Details"><i class="bx bx-show"></i></a>
                    <a href="' . e($editUrl) . '" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-reel" data-id="' . $reel->id . '" title="Delete"><i class="bx bx-trash"></i></button>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function details(Reel $reel): View
    {
        $reel->load(['brand', 'type', 'gsm']);
        $stockSummary = $reel->stocks()
            ->selectRaw('status, COUNT(*) as total, SUM(original_length) as original_length, SUM(balance_length) as balance_length')
            ->groupBy('status')->get()->keyBy('status');

        return view('reels.manage.details', compact('reel', 'stockSummary'));
    }

    public function transactionData(Reel $reel): JsonResponse
    {
        $movements = ReelStockMovement::query()
            ->whereHas('stock', fn ($stock) => $stock->where('reel_id', $reel->id))
            ->with(['stock:id,stock_code,actual_code,original_length', 'provider:id,name', 'warehouse:id,name', 'customer:id,first_name,last_name', 'reference'])
            ->orderByDesc('created_at')
            ->get();
        $warehouseNames = ReelWarehouse::whereIn('id', $movements->pluck('reel_warehouse_id')->filter()->unique())
            ->pluck('name', 'id');

        $activities = $movements
            ->groupBy(function (ReelStockMovement $movement) {
                if ($movement->batch_uuid) return $movement->batch_uuid;
                $legacyType = in_array($movement->transaction_type, ['transfer_out', 'transfer_in'], true)
                    ? 'transfer'
                    : $movement->transaction_type;
                return implode('|', [
                    'legacy', $legacyType, $movement->reference_type,
                    $movement->reference_id,
                    $movement->created_at?->format('Y-m-d H:i:s'),
                ]);
            })
            ->map(function ($group) use ($warehouseNames) {
                $first = $group->first();
                $type = $first->transaction_type;
                $outMovements = $group->where('transaction_type', 'transfer_out');
                $inMovements = $group->where('transaction_type', 'transfer_in');
                $countSource = $outMovements->isNotEmpty() ? $outMovements : $group;
                $quantity = $countSource->count();
                $statusCounts = $countSource
                    ->map(function ($item) {
                        if ($item->stock_status) return $item->stock_status;
                        if ($item->transaction_type === 'opening') return 'full';
                        return (float) $item->balance_before < (float) ($item->stock?->original_length ?? 0)
                            ? 'bit'
                            : 'full';
                    })
                    ->groupBy(fn ($status) => $status)->map->count();
                $statusText = collect(['bit', 'full', 'finished', 'sold'])
                    ->filter(fn ($status) => ($statusCounts[$status] ?? 0) > 0)
                    ->map(fn ($status) => ($statusCounts[$status] ?? 0) . ' ' . ucfirst($status))
                    ->implode(' and ');
                $warehouseName = fn ($movement) => $warehouseNames[$movement->reel_warehouse_id]
                    ?? 'Warehouse #' . $movement->reel_warehouse_id;
                $warehouse = $warehouseName($first);
                $providers = $group->pluck('provider.name')->filter()->unique()->implode(', ');
                $customer = trim(($first->customer?->first_name ?? '') . ' ' . ($first->customer?->last_name ?? ''));
                $reference = $first->reference?->sale_code
                    ?? $first->reference?->code
                    ?? ($first->reference_id ? '#' . $first->reference_id : '—');

                if ($outMovements->isNotEmpty() || $inMovements->isNotEmpty()) {
                    $source = $outMovements->isNotEmpty() ? $warehouseName($outMovements->first()) : $warehouse;
                    $destination = $inMovements->isNotEmpty() ? $warehouseName($inMovements->first()) : $warehouse;
                    $description = ($statusText ? $statusText . ' stock' : $quantity . ' stock') . " moved from {$source} to {$destination}";
                    $type = 'transfer';
                    $warehouse = "{$source} → {$destination}";
                } elseif ($type === 'opening') {
                    $description = ($statusText ?: $quantity) . ' item' . ($quantity === 1 ? '' : 's') . " added at {$warehouse}";
                } elseif ($type === 'sale') {
                    $description = ($statusText ?: $quantity) . ' reel item' . ($quantity === 1 ? '' : 's') . ' sold' .
                        ($customer ? " to {$customer}" : '');
                } else {
                    $description = ($statusText ?: $quantity) . ' reel item' . ($quantity === 1 ? '' : 's') . ' ' .
                        str_replace('_', ' ', $type) . " at {$warehouse}";
                }

                return [
                    'created_at' => $group->max('created_at')?->format('d M Y h:i a'),
                    'activity' => ucfirst($description),
                    'transaction_type' => ucfirst(str_replace('_', ' ', $type)),
                    'quantity' => $quantity,
                    'warehouse' => $warehouse,
                    'provider' => $providers ?: '—',
                    'customer' => $customer ?: '—',
                    'reference' => $reference,
                    'remarks' => $first->remarks ?: '—',
                ];
            })->sortByDesc('created_at')->values();

        return DataTables::of($activities)
            ->addIndexColumn()
            ->toJson();
    }

    public function create(): View
    {
        return view('reels.manage.create', $this->settings());
    }

    public function store(ReelRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['code'] = $this->generateCode($data);
        $this->ensureCodeIsUnique($data['code']);
        $reel = Reel::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Reel created successfully.',
                'reel' => ['id' => $reel->id, 'text' => $reel->code, 'code' => $reel->code],
            ], 201);
        }

        return redirect()->route('reels.manage.index')->with('success', 'Reel created successfully.');
    }

    public function edit(Reel $reel): View
    {
        return view('reels.manage.edit', array_merge($this->settings(false), compact('reel')));
    }

    public function update(ReelRequest $request, Reel $reel): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = $this->generateCode($data);
        $this->ensureCodeIsUnique($data['code'], $reel->id);
        $reel->update($data);

        return redirect()->route('reels.manage.index')->with('success', 'Reel updated successfully.');
    }

    public function destroy(Reel $reel): JsonResponse
    {
        try {
            $reel->delete();
        } catch (QueryException) {
            return response()->json(['message' => 'This reel cannot be deleted because it is in use.'], 422);
        }

        return response()->json(['message' => 'Reel deleted successfully.']);
    }

    private function settings(bool $onlyActive = true): array
    {
        $brands = ReelBrand::query()->when($onlyActive, fn($q) => $q->where('is_active', true))->orderBy('name')->get();
        $providers = ReelProvider::query()->when($onlyActive, fn($q) => $q->where('is_active', true))->orderBy('name')->get();
        $types = ReelType::query()->when($onlyActive, fn($q) => $q->where('is_active', true))->orderBy('name')->get();
        $gsms = ReelGsm::query()->when($onlyActive, fn($q) => $q->where('is_active', true))->orderBy('gsm')->get();

        return compact('brands', 'providers', 'types', 'gsms');
    }

    private function generateCode(array $data): string
    {
        $brand = ReelBrand::findOrFail($data['reel_brand_id']);
        $type = ReelType::findOrFail($data['reel_type_id']);
        $gsm = ReelGsm::findOrFail($data['reel_gsm_id']);
        $part = fn ($value) => strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $value));

        return implode('-', [
            $part($brand->short_name ?: $brand->name),
            $part($type->short_name ?: $type->name),
            $part($gsm->gsm) . 'GSM',
            $this->numberPart($data['width']),
            $this->numberPart($data['length']),
        ]);
    }

    private function numberPart(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    private function ensureCodeIsUnique(string $code, ?int $ignoreId = null): void
    {
        $exists = Reel::where('code', $code)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists();
        if ($exists) {
            throw ValidationException::withMessages([
                'code' => 'A reel with the same brand, type, GSM, width, and length already exists.',
            ]);
        }
    }
}
