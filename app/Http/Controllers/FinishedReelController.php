<?php

namespace App\Http\Controllers;

use App\Models\Reels\Reel;
use App\Models\Reels\ReelBrand;
use App\Models\Reels\ReelGsm;
use App\Models\Reels\ReelProvider;
use App\Models\Reels\ReelStock;
use App\Models\Reels\ReelStockMovement;
use App\Models\Reels\ReelType;
use App\Models\Reels\ReelWarehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class FinishedReelController extends Controller
{
    public function index(): View
    {
        return view('reels.finished.index', [
            'reels' => Reel::orderBy('code')->get(['id', 'code']),
            'brands' => ReelBrand::orderBy('name')->get(['id', 'name']),
            'types' => ReelType::orderBy('name')->get(['id', 'name']),
            'gsms' => ReelGsm::orderBy('gsm')->get(['id', 'gsm']),
            'providers' => ReelProvider::orderBy('name')->get(['id', 'name']),
            'warehouses' => ReelWarehouse::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $finishedAt = fn () => ReelStockMovement::query()->select('created_at')
            ->whereColumn('reel_stock_movements.reel_stock_id', 'reel_stocks.id')
            ->where('stock_status', 'finished')->latest('created_at')->limit(1);

        $query = ReelStock::query()
            ->with(['reel.brand:id,name', 'reel.type:id,name', 'reel.gsm:id,gsm', 'provider:id,name', 'warehouse:id,name'])
            ->where('reel_stocks.status', 'finished')->select('reel_stocks.*')
            ->addSelect(['finished_at' => $finishedAt()])
            ->when($request->filled('reel_id'), fn ($q) => $q->where('reel_stocks.reel_id', $request->integer('reel_id')))
            ->when($request->filled('reel_provider_id'), fn ($q) => $q->where('reel_stocks.reel_provider_id', $request->integer('reel_provider_id')))
            ->when($request->filled('reel_warehouse_id'), fn ($q) => $q->where('reel_stocks.reel_warehouse_id', $request->integer('reel_warehouse_id')))
            ->when($request->filled('reel_brand_id'), fn ($q) => $q->whereHas('reel', fn ($r) => $r->where('reel_brand_id', $request->integer('reel_brand_id'))))
            ->when($request->filled('reel_type_id'), fn ($q) => $q->whereHas('reel', fn ($r) => $r->where('reel_type_id', $request->integer('reel_type_id'))))
            ->when($request->filled('reel_gsm_id'), fn ($q) => $q->whereHas('reel', fn ($r) => $r->where('reel_gsm_id', $request->integer('reel_gsm_id'))))
            ->when($request->filled('finished_from'), fn ($q) => $q->whereHas('movements', fn ($m) =>
                $m->where('stock_status', 'finished')->whereDate('created_at', '>=', $request->input('finished_from'))))
            ->when($request->filled('finished_to'), fn ($q) => $q->whereHas('movements', fn ($m) =>
                $m->where('stock_status', 'finished')->whereDate('created_at', '<=', $request->input('finished_to'))))
            ->orderByDesc('finished_at')->orderByDesc('reel_stocks.updated_at');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('reel_code', fn (ReelStock $stock) => $stock->reel?->code ?? '—')
            ->addColumn('brand_name', fn (ReelStock $stock) => $stock->reel?->brand?->name ?? '—')
            ->addColumn('type_name', fn (ReelStock $stock) => $stock->reel?->type?->name ?? '—')
            ->addColumn('gsm_value', fn (ReelStock $stock) => $stock->reel?->gsm?->gsm ?? '—')
            ->addColumn('width', fn (ReelStock $stock) => $this->measurement($stock->reel?->width))
            ->addColumn('provider_name', fn (ReelStock $stock) => $stock->provider?->name ?? '—')
            ->addColumn('warehouse_name', fn (ReelStock $stock) => $stock->warehouse?->name ?? '—')
            ->editColumn('actual_code', fn (ReelStock $stock) => $stock->actual_code ?: '—')
            ->editColumn('original_length', fn (ReelStock $stock) => $this->measurement($stock->original_length))
            ->editColumn('created_at', fn (ReelStock $stock) => $stock->created_at?->format('d M Y h:i a') ?? '—')
            ->editColumn('finished_at', fn (ReelStock $stock) => $stock->finished_at
                ? date('d M Y h:i a', strtotime($stock->finished_at))
                : ($stock->updated_at?->format('d M Y h:i a') ?? '—'))
            ->addColumn('action', fn (ReelStock $stock) => '<a href="' . route('reels.finished.usage', $stock) .
                '" class="btn btn-sm btn-outline-info" title="View Usage"><i class="bx bx-history"></i></a>')
            ->rawColumns(['action'])
            ->toJson();
    }

    private function measurement(mixed $value): string
    {
        return $value === null ? '—' : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
