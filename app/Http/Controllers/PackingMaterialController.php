<?php

namespace App\Http\Controllers;

use App\Models\PackingMaterial;
use App\Models\PackingMaterialStockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PackingMaterialController extends Controller
{
    private function validType(string $type): string
    {
        abort_unless(in_array($type, ['box', 'cover'], true), 404);

        return $type;
    }

    public function index(string $type): View
    {
        $type = $this->validType($type);

        return view('packing-materials.index', compact('type'));
    }

    public function data(string $type): JsonResponse
    {
        $type = $this->validType($type);

        return DataTables::eloquent(PackingMaterial::where('type', $type)->latest())
            ->editColumn('is_active', fn ($m) => (int) $m->is_active)
            ->addColumn('action', fn ($m) => '<button class="btn btn-sm btn-outline-primary edit-material" data-id="'.$m->id.'" data-code="'.e($m->code).'" data-name="'.e($m->name).'" data-active="'.(int) $m->is_active.'" title="Edit"><i class="bx bx-edit"></i></button> <button class="btn btn-sm btn-outline-success adjust-material" data-id="'.$m->id.'" title="Adjust Quantity"><i class="bx bx-plus-circle"></i></button> <button class="btn btn-sm btn-outline-dark material-history" data-id="'.$m->id.'" data-code="'.e($m->code).'" title="History"><i class="bx bx-history"></i></button>')
            ->rawColumns(['action'])->toJson();
    }

    public function store(Request $request, string $type): JsonResponse
    {
        $type = $this->validType($type);
        $data = $this->validated($request, $type);
        DB::transaction(function () use ($data, $type) {
            $m = PackingMaterial::create($data + ['type' => $type, 'code' => 'PENDING-'.Str::uuid(), 'created_by' => auth()->id(), 'updated_by' => auth()->id()]);
            $m->update(['code' => ($type === 'box' ? 'BOX' : 'COVER').str_pad((string) $m->id, 4, '0', STR_PAD_LEFT)]);
            if ($m->quantity) {
                PackingMaterialStockMovement::create(['packing_material_id' => $m->id, 'transaction_type' => 'opening_stock', 'quantity_change' => $m->quantity, 'quantity_before' => 0, 'quantity_after' => $m->quantity, 'remarks' => 'Opening stock', 'created_by' => auth()->id()]);
            }
        });

        return response()->json(['message' => ucfirst($type).' stock created successfully.']);
    }

    public function update(Request $request, string $type, PackingMaterial $material): JsonResponse
    {
        $type = $this->validType($type);
        abort_unless($material->type === $type, 404);
        $material->update($this->validated($request, $type, true) + ['updated_by' => auth()->id()]);

        return response()->json(['message' => ucfirst($type).' updated successfully.']);
    }

    public function adjust(Request $request, string $type, PackingMaterial $material): JsonResponse
    {
        $this->validType($type);
        abort_unless($material->type === $type, 404);
        $d = $request->validate(['adjustment_type' => ['required', Rule::in(['add', 'remove'])], 'quantity' => ['required', 'integer', 'min:1'], 'remarks' => ['required', 'string', 'max:500']]);
        DB::transaction(function () use ($material, $d) {
            $m = PackingMaterial::lockForUpdate()->findOrFail($material->id);
            $before = $m->quantity;
            $change = $d['adjustment_type'] === 'add' ? $d['quantity'] : -$d['quantity'];
            if ($before + $change < 0) {
                abort(422, 'Insufficient quantity.');
            }$m->update(['quantity' => $before + $change, 'updated_by' => auth()->id()]);
            PackingMaterialStockMovement::create(['packing_material_id' => $m->id, 'transaction_type' => 'manual_adjustment', 'quantity_change' => $change, 'quantity_before' => $before, 'quantity_after' => $before + $change, 'remarks' => $d['remarks'], 'created_by' => auth()->id()]);
        });

        return response()->json(['message' => 'Quantity adjusted successfully.']);
    }

    public function history(string $type, PackingMaterial $material): JsonResponse
    {
        $this->validType($type);
        abort_unless($material->type === $type, 404);

        return DataTables::eloquent(PackingMaterialStockMovement::where('packing_material_id', $material->id)->latest())->editColumn('transaction_type', fn ($m) => ucwords(str_replace('_', ' ', $m->transaction_type)))->editColumn('quantity_change', fn ($m) => ($m->quantity_change > 0 ? '+' : '').$m->quantity_change)->editColumn('created_at', fn ($m) => $m->created_at?->format('d M Y h:i a'))->addColumn('reference', fn ($m) => $m->reference_id ? class_basename($m->reference_type).' #'.$m->reference_id : '—')->toJson();
    }

    public function search(Request $request, string $type): JsonResponse
    {
        $type = $this->validType($type);
        $q = trim((string) $request->q);
        $items = PackingMaterial::where('type', $type)->where('is_active', 1)->where('quantity', '>', 0)->when($q, fn ($x) => $x->where(fn ($y) => $y->where('code', 'like', "%$q%")->orWhere('name', 'like', "%$q%")))->limit(30)->get()->map(fn ($m) => ['id' => $m->id, 'text' => $m->code.' | '.$m->name.' | Available: '.$m->quantity, 'available_quantity' => $m->quantity]);

        return response()->json(['results' => $items]);
    }

    private function validated(Request $r, string $type, bool $update = false): array
    {
        $rules = ['name' => ['required', 'string', 'max:255'], 'is_active' => ['required', 'boolean']];
        if (! $update) {
            $rules['quantity'] = ['required', 'integer', 'min:0'];
        }

return $r->validate($rules);
    }
}
