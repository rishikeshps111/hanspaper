<?php

namespace App\Http\Controllers;

use App\Models\Reels\ReelBrand;
use App\Models\Reels\ReelGsm;
use App\Models\Reels\ReelProvider;
use App\Models\Reels\ReelType;
use App\Models\Reels\ReelWarehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReelSettingsController extends Controller
{
    private const MODELS = [
        'brands' => ReelBrand::class,
        'gsm' => ReelGsm::class,
        'providers' => ReelProvider::class,
        'types' => ReelType::class,
        'warehouses' => ReelWarehouse::class,
    ];

    public function index(): View
    {
        return view('reels.settings');
    }

    public function data(string $type): JsonResponse
    {
        $model = $this->model($type);
        $query = $model::query()
            ->with(['creator:id,username', 'updater:id,username'])
            ->select((new $model())->getTable() . '.*')
            ->orderByDesc('created_at');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('created_by_name', fn ($record) => $record->creator?->username ?? '—')
            ->addColumn('updated_by_name', fn ($record) => $record->updater?->username ?? '—')
            ->editColumn('is_active', fn ($record) => $record->is_active ? 1 : 0)
            ->editColumn('created_at', fn ($record) => $record->created_at?->format('Y-m-d H:i:s'))
            ->toJson();
    }

    public function store(Request $request, string $type): JsonResponse
    {
        $model = $this->model($type);
        $data = $this->validated($request, $type, $model);
        $record = $model::create($data)->load(['creator', 'updater']);

        return response()->json(['message' => 'Reel setting created successfully.', 'record' => $record], 201);
    }

    public function update(Request $request, string $type, int $id): JsonResponse
    {
        $model = $this->model($type);
        $record = $model::findOrFail($id);
        $record->update($this->validated($request, $type, $model, $id));

        return response()->json([
            'message' => 'Reel setting updated successfully.',
            'record' => $record->fresh()->load(['creator', 'updater']),
        ]);
    }

    public function destroy(string $type, int $id): JsonResponse
    {
        $model = $this->model($type);
        $model::findOrFail($id)->delete();

        return response()->json(['message' => 'Reel setting deleted successfully.']);
    }

    private function model(string $type): string
    {
        abort_unless(isset(self::MODELS[$type]), 404);
        return self::MODELS[$type];
    }

    private function validated(Request $request, string $type, string $model, ?int $id = null): array
    {
        $table = (new $model())->getTable();
        $active = ['is_active' => ['required', 'boolean']];

        if ($type === 'gsm') {
            return $request->validate([
                'gsm' => ['required', 'integer', 'min:1', 'max:65535', Rule::unique($table, 'gsm')->ignore($id)],
                ...$active,
            ]);
        }

        if ($type === 'warehouses') {
            return $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique($table, 'name')->ignore($id)],
                'short_name' => ['nullable', 'string', 'max:100'],
                'warehouse_type' => ['required', Rule::in(['factory', 'godown'])],
                ...$active,
            ]);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique($table, 'name')->ignore($id)],
            'short_name' => ['nullable', 'string', 'max:100'],
            ...$active,
        ]);
    }
}
