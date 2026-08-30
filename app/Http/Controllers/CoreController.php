<?php

namespace App\Http\Controllers;

use App\Models\Core;
use App\Models\CoreStockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CoreController extends Controller
{
    public function index(): View
    {
        return view('cores.index');
    }

    public function data(): JsonResponse
    {
        $query = Core::query()
            ->withSum(['productionRuns as reserved_quantity' => fn ($query) => $query->where('status', 'in_progress')], 'core_quantity')
            ->orderByDesc('created_at');

        return DataTables::eloquent($query)
            ->editColumn('size_mm', fn (Core $core) => (float) $core->size_mm)
            ->addColumn('available_quantity', fn (Core $core) => max(0, $core->quantity - (int) $core->reserved_quantity))
            ->editColumn('is_active', fn (Core $core) => $core->is_active ? 1 : 0)
            ->addColumn('action', fn (Core $core) =>
                '<button class="btn btn-sm btn-outline-primary edit-core" data-id="' . $core->id . '" data-code="' . e($core->code) . '" data-size="' . e($core->size_mm) . '" data-active="' . (int) $core->is_active . '" title="Edit"><i class="bx bx-edit"></i></button> ' .
                '<button class="btn btn-sm btn-outline-success adjust-core" data-id="' . $core->id . '" title="Adjust Quantity"><i class="bx bx-plus-circle"></i></button> ' .
                '<button class="btn btn-sm btn-outline-dark core-history" data-id="' . $core->id . '" data-code="' . e($core->code) . '" title="Transaction History"><i class="bx bx-history"></i></button>')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function history(Core $core): JsonResponse
    {
        $query = CoreStockMovement::query()
            ->where('core_id', $core->id)
            ->select('core_stock_movements.*')
            ->orderByDesc('created_at');

        return DataTables::eloquent($query)
            ->editColumn('transaction_type', fn (CoreStockMovement $movement) => ucwords(str_replace('_', ' ', $movement->transaction_type)))
            ->editColumn('quantity_change', fn (CoreStockMovement $movement) => ($movement->quantity_change > 0 ? '+' : '') . $movement->quantity_change)
            ->editColumn('created_at', fn (CoreStockMovement $movement) => $movement->created_at?->format('d M Y h:i a'))
            ->addColumn('reference', fn (CoreStockMovement $movement) => $movement->reference_id
                ? class_basename($movement->reference_type) . ' #' . $movement->reference_id
                : '—')
            ->toJson();
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'size_mm' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
        DB::transaction(function () use ($data) {
            $core = Core::create($data + ['code' => 'PENDING-' . Str::uuid(), 'created_by' => auth()->id(), 'updated_by' => auth()->id()]);
            $core->update(['code' => 'CORE' . str_pad((string) $core->id, 4, '0', STR_PAD_LEFT)]);
            if ($core->quantity > 0) CoreStockMovement::create([
                'core_id' => $core->id, 'transaction_type' => 'opening_stock', 'quantity_change' => $core->quantity,
                'quantity_before' => 0, 'quantity_after' => $core->quantity, 'remarks' => 'Opening stock', 'created_by' => auth()->id(),
            ]);
        });
        return response()->json(['message' => 'Core created successfully.'], 201);
    }

    public function update(Request $request, Core $core): JsonResponse
    {
        $data = $request->validate([
            'size_mm' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'is_active' => ['required', 'boolean'],
        ]);
        $core->update($data + ['updated_by' => auth()->id()]);
        return response()->json(['message' => 'Core updated successfully.']);
    }

    public function adjust(Request $request, Core $core): JsonResponse
    {
        $data = $request->validate([
            'adjustment_type' => ['required', Rule::in(['add', 'remove'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'remarks' => ['required', 'string', 'max:500'],
        ]);
        DB::transaction(function () use ($core, $data) {
            $locked = Core::lockForUpdate()->findOrFail($core->id);
            $before = $locked->quantity;
            $change = $data['adjustment_type'] === 'add' ? $data['quantity'] : -$data['quantity'];
            $reserved = (int) $locked->productionRuns()->where('status', 'in_progress')->sum('core_quantity');
            if ($before + $change < $reserved) abort(422, 'Quantity cannot be reduced below the quantity reserved for active production.');
            $locked->update(['quantity' => $before + $change, 'updated_by' => auth()->id()]);
            CoreStockMovement::create([
                'core_id' => $locked->id, 'transaction_type' => 'manual_adjustment', 'quantity_change' => $change,
                'quantity_before' => $before, 'quantity_after' => $before + $change,
                'remarks' => $data['remarks'], 'created_by' => auth()->id(),
            ]);
        });
        return response()->json(['message' => 'Core quantity adjusted successfully.']);
    }
}
