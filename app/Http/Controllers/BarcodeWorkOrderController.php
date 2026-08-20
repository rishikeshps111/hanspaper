<?php

namespace App\Http\Controllers;

use App\Http\Requests\BarcodeWorkOrderRequest;
use App\Models\BarcodeWorkOrders\BarcodeWorkOrder;
use App\Models\Party\Party;
use App\Models\SalesRepresentatives\SalesRepresentative;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BarcodeWorkOrderController extends Controller
{
    public const STATUSES = ['pending', 'partial_pending', 'completed', 'dispatched', 'delivered', 'cancelled'];
    public const STATUS_TRANSITIONS = [
        'pending' => ['partial_pending', 'completed'],
        'partial_pending' => ['completed'],
        'completed' => ['dispatched'],
        'dispatched' => ['delivered'],
    ];

    public function index(): View
    {
        return view('barcode-work-orders.index', [
            'customers' => $this->customers(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = BarcodeWorkOrder::query()->with('customer:id,first_name,last_name')
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('due_date'), fn ($query) => $query->whereDate('due_date', $request->input('due_date')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderByDesc('created_at');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('customer_name', fn (BarcodeWorkOrder $order) => trim(($order->customer?->first_name ?? '') . ' ' . ($order->customer?->last_name ?? '')) ?: '—')
            ->editColumn('work_order_date', fn (BarcodeWorkOrder $order) => $order->work_order_date->format('d M Y'))
            ->editColumn('due_date', fn (BarcodeWorkOrder $order) => $order->due_date->format('d M Y'))
            ->editColumn('status', fn (BarcodeWorkOrder $order) => $this->statusBadge($order->status))
            ->addColumn('action', function (BarcodeWorkOrder $order) {
                $buttons = '<div class="btn-group">';
                if ($order->status === 'pending') {
                    $buttons .= '<a href="' . route('barcode-work-orders.edit', $order) . '" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-outline-danger cancel-work-order" data-url="' . route('barcode-work-orders.cancel', $order) . '" data-code="' . e($order->code) . '" title="Cancel"><i class="bx bx-x-circle"></i></button>';
                }
                if ($allowedStatuses = self::STATUS_TRANSITIONS[$order->status] ?? []) {
                    $buttons .= '<button type="button" class="btn btn-sm btn-outline-success change-work-order-status" data-url="' . route('barcode-work-orders.status', $order) . '" data-code="' . e($order->code) . '" data-statuses="' . e(json_encode($allowedStatuses)) . '" title="Change Status"><i class="bx bx-transfer-alt"></i></button>';
                }
                return $buttons . '<a href="' . route('barcode-work-orders.show', $order) . '" class="btn btn-sm btn-outline-dark" title="View"><i class="bx bx-show"></i></a></div>';
            })
            ->rawColumns(['status', 'action'])->toJson();
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(BarcodeWorkOrderRequest $request): RedirectResponse
    {
        $order = DB::transaction(function () use ($request) {
            $order = BarcodeWorkOrder::create([
                ...$request->safe()->only(['customer_id', 'representative_id', 'work_order_date', 'due_date']),
                'status' => 'pending', 'created_by' => auth()->id(), 'updated_by' => auth()->id(),
            ]);
            $order->update(['code' => 'BWO-' . now()->format('dmY') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)]);
            $order->items()->createMany($this->normalizeItems($request->validated('items')));
            return $order;
        });

        return redirect()->route('barcode-work-orders.index')->with('success', 'Barcode work order created successfully.');
    }

    public function show(BarcodeWorkOrder $barcodeWorkOrder): View
    {
        $barcodeWorkOrder->load(['customer', 'representative', 'items']);
        return view('barcode-work-orders.show', compact('barcodeWorkOrder'));
    }

    public function edit(BarcodeWorkOrder $barcodeWorkOrder): View
    {
        abort_unless($barcodeWorkOrder->status === 'pending', 403, 'Only pending work orders can be edited.');
        $barcodeWorkOrder->load('items');
        return $this->formView($barcodeWorkOrder);
    }

    public function update(BarcodeWorkOrderRequest $request, BarcodeWorkOrder $barcodeWorkOrder): RedirectResponse
    {
        abort_unless($barcodeWorkOrder->status === 'pending', 403, 'Only pending work orders can be edited.');
        DB::transaction(function () use ($request, $barcodeWorkOrder) {
            $barcodeWorkOrder->update([
                ...$request->safe()->only(['customer_id', 'representative_id', 'work_order_date', 'due_date']),
                'updated_by' => auth()->id(),
            ]);
            $barcodeWorkOrder->items()->delete();
            $barcodeWorkOrder->items()->createMany($this->normalizeItems($request->validated('items')));
        });
        return redirect()->route('barcode-work-orders.index')->with('success', 'Barcode work order updated successfully.');
    }

    public function cancel(BarcodeWorkOrder $barcodeWorkOrder): JsonResponse
    {
        $updated = BarcodeWorkOrder::whereKey($barcodeWorkOrder->id)->where('status', 'pending')->update([
            'status' => 'cancelled', 'updated_by' => auth()->id(), 'updated_at' => now(),
        ]);
        if (!$updated) {
            return response()->json(['message' => 'Only pending work orders can be cancelled.'], 422);
        }
        return response()->json(['message' => "{$barcodeWorkOrder->code} cancelled successfully."]);
    }

    public function changeStatus(Request $request, BarcodeWorkOrder $barcodeWorkOrder): JsonResponse
    {
        $allowedStatuses = self::STATUS_TRANSITIONS[$barcodeWorkOrder->status] ?? [];
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', self::STATUSES)],
            'status_date' => ['nullable', 'required_if:status,completed,dispatched,delivered', 'date'],
        ], ['status_date.required_if' => 'The status date is required.']);
        if (!in_array($validated['status'], $allowedStatuses, true)) {
            return response()->json(['message' => 'This status transition is not allowed. Refresh the listing and try again.'], 422);
        }

        $minimumDate = match ($validated['status']) {
            'completed' => $barcodeWorkOrder->work_order_date,
            'dispatched' => $barcodeWorkOrder->completed_date,
            'delivered' => $barcodeWorkOrder->dispatched_date,
            default => null,
        };
        if ($minimumDate && Carbon::parse($validated['status_date'])->startOfDay()->lt($minimumDate->copy()->startOfDay())) {
            throw ValidationException::withMessages([
                'status_date' => 'The status date must be on or after ' . $minimumDate->format('d M Y') . '.',
            ]);
        }

        $values = [
            'status' => $validated['status'], 'updated_by' => auth()->id(), 'updated_at' => now(),
        ];
        $dateColumn = match ($validated['status']) {
            'completed' => 'completed_date',
            'dispatched' => 'dispatched_date',
            'delivered' => 'delivered_date',
            default => null,
        };
        if ($dateColumn) {
            $values[$dateColumn] = $validated['status_date'];
        }

        $updated = BarcodeWorkOrder::whereKey($barcodeWorkOrder->id)->where('status', $barcodeWorkOrder->status)->update($values);
        if (!$updated) {
            return response()->json(['message' => 'The work order status has already changed. Refresh and try again.'], 422);
        }

        return response()->json(['message' => "{$barcodeWorkOrder->code} moved to " . ucwords(str_replace('_', ' ', $validated['status'])) . '.']);
    }

    private function formView(?BarcodeWorkOrder $barcodeWorkOrder = null): View
    {
        return view('barcode-work-orders.create', [
            'workOrder' => $barcodeWorkOrder,
            'customers' => $this->customers(),
            'representatives' => SalesRepresentative::where('status', 'Active')->orderBy('full_name')->get(['id', 'full_name', 'mobile']),
        ]);
    }

    private function customers()
    {
        return Party::whereIn('party_type', ['customer', 'both'])->where('status', 1)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'mobile']);
    }

    private function normalizeItems(array $items): array
    {
        return array_map(function (array $item) {
            $item['gap_mm'] = $item['gap'] === 'with_gap' ? $item['gap_mm'] : null;
            $item['is_printing'] = $item['is_printing'] === 'yes';
            $item['printing_colors'] = $item['is_printing'] ? $item['printing_colors'] : null;
            return $item;
        }, $items);
    }

    private function statusBadge(string $status): string
    {
        $classes = ['pending' => 'bg-warning text-dark', 'partial_pending' => 'bg-info text-dark', 'completed' => 'bg-success', 'dispatched' => 'bg-primary', 'delivered' => 'bg-dark', 'cancelled' => 'bg-danger'];
        return '<span class="badge ' . $classes[$status] . '">' . e(ucwords(str_replace('_', ' ', $status))) . '</span>';
    }
}
