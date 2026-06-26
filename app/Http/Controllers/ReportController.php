<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Real;
use App\Models\PackingList;
use Illuminate\Http\Request;
use App\Models\ProductionList;
use App\Models\Machines\Machine;
use App\Models\Employees\Employee;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function machineIndex()
    {
        $machines = Machine::all();
        return view('reports.machine', compact('machines'));
    }

    public function getMachineData(Request $request)
    {
        $machineId = $request->machine_id;
        $type = $request->report_type; // day, month, year
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date)->endOfDay();

        $query = ProductionList::where('machine_id', $machineId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNull('deleted_at');

        if ($type === 'day') {
            $query->selectRaw('SUM(quantity) as total, DATE(created_at) as date')
                ->groupBy(DB::raw('DATE(created_at)'));
        } elseif ($type === 'month') {
            $query->selectRaw('SUM(quantity) as total, DATE_FORMAT(created_at, "%Y-%m") as date')
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));
        } elseif ($type === 'year') {
            $query->selectRaw('SUM(quantity) as total, YEAR(created_at) as date')
                ->groupBy(DB::raw('YEAR(created_at)'));
        }

        $data = $query->orderBy('date')->get();

        return response()->json($data);
    }
    
    public function producedByIndex(Request $request)
    {
        $employees = Employee::where('status', 'Active')->get();
        if ($request->ajax()) {
            $employees = Employee::where('status', 'Active');

            if ($request->employee_id) {
                $employees->where('id', $request->employee_id);
            }

            return datatables()->of($employees)
                ->addColumn('total_production',  function ($row) use ($request) {
                    $query = $row->productions();
                    if ($request->from_date && $request->to_date) {
                        $query->whereBetween('created_at', [$request->from_date . ' 00:00:00', $request->to_date . ' 23:59:59']);
                    }
                    return $query->sum('quantity');
                })
                ->addColumn('total_packings',  function ($row) use ($request) {
                    $query = $row->packings();
                    if ($request->from_date && $request->to_date) {
                        $query->whereBetween('created_at', [$request->from_date . ' 00:00:00', $request->to_date . ' 23:59:59']);
                    }
                    return $query->sum('quantity');
                })
                ->addColumn('actions',  function ($row) {
                    return '<button type="button" class="btn btn-sm btn-primary view-packing-btn" data-title="Packing Details" data-id="' . $row->id . '">
                                Package Details
                            </button>
                            
                            <button type="button" class="btn btn-sm btn-warning view-production-btn" 
                                data-title="Production Details" data-id="' . $row->id . '">
                                Production Details
                            </button>
                            ';
                })
                ->addIndexColumn()
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('reports.production', compact('employees'));
    }


    public function producedByIndexProduction(Request $request)
    {
        return view('reports.tables.production', [
            'employee_id' => $request->employee_id
        ]);
    }

    public function producedByIndexProductionList(Request $request)
    {
        $query = ProductionList::with('productionItemMaster', 'productionItemMaster.item', 'real', 'productionItemMaster.purchaseOrder')
            ->where('produced_by', $request->employee_id)->orderBy('created_at', 'DESC');

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('purchase_order', function ($row) {
                return $row->productionItemMaster->purchaseOrder->purchase_order_id ?? '-';
            })
            ->addColumn('item_name', function ($row) {
                return $row->productionItemMaster->item->name ?? 'N/A';
            })
            ->addColumn('real', function ($row) {
                return $row->real->real_no ?? 'N/A';
            })
            ->addColumn('date', function ($row) {
                return $row->created_at->format('d M Y');
            })
            ->rawColumns([])
            ->make(true);
    }

    public function producedByIndexPacking(Request $request)
    {
        return view('reports.tables.packing', [
            'employee_id' => $request->employee_id
        ]);
    }

    public function producedByIndexPackingList(Request $request)
    {
        $query = PackingList::with('purchaseOrderMaster', 'purchaseOrderMaster.item')
            ->where('packed_by', $request->employee_id)->orderBy('created_at', 'DESC');

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('purchase_order', function ($row) {
                return $row->purchaseOrderMaster->purchaseOrder->purchase_order_id ?? '-';
            })
            ->addColumn('item_name', function ($row) {
                return $row->purchaseOrderMaster->item->name ?? 'N/A';
            })
            ->addColumn('date', function ($row) {
                return $row->created_at->format('d M Y');
            })
            ->rawColumns([])
            ->make(true);
    }

    public function producedByData(Request $request)
    {
        $employeeId = $request->produced_by;
        $type = $request->report_type;
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date)->endOfDay();

        $query = ProductionList::where('produced_by', $employeeId)
            ->whereBetween('created_at', [$from, $to]);

        if ($type === 'day') {
            $query->selectRaw('SUM(quantity) as total, DATE(created_at) as date')
                ->groupBy(DB::raw('DATE(created_at)'));
        } elseif ($type === 'month') {
            $query->selectRaw('SUM(quantity) as total, DATE_FORMAT(created_at, "%Y-%m") as date')
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));
        } elseif ($type === 'year') {
            $query->selectRaw('SUM(quantity) as total, YEAR(created_at) as date')
                ->groupBy(DB::raw('YEAR(created_at)'));
        }

        return response()->json($query->orderBy('date')->get());
    }

    public function realNumberIndex()
    {
        $realNumbers = Real::get();
        return view('reports.real_number', compact('realNumbers'));
    }

    public function realNumberData(Request $request)
    {
        $realNumber = $request->real_number;
        $type = $request->report_type;
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date)->endOfDay();

        $query = ProductionList::where('real_id', $realNumber)
            ->whereBetween('created_at', [$from, $to]);

        if ($type === 'day') {
            $query->selectRaw('SUM(quantity) as total, DATE(created_at) as date')
                ->groupBy(DB::raw('DATE(created_at)'));
        } elseif ($type === 'month') {
            $query->selectRaw('SUM(quantity) as total, DATE_FORMAT(created_at, "%Y-%m") as date')
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));
        } elseif ($type === 'year') {
            $query->selectRaw('SUM(quantity) as total, YEAR(created_at) as date')
                ->groupBy(DB::raw('YEAR(created_at)'));
        }

        return response()->json($query->orderBy('date')->get());
    }

    public function packedByIndex()
    {
        $employees = Employee::all();
        return view('reports.packed', compact('employees'));
    }

    public function packedByData(Request $request)
    {
        $employeeId = $request->produced_by;
        $type = $request->report_type;
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date)->endOfDay();

        $query = PackingList::where('packed_by', $employeeId)
            ->whereBetween('created_at', [$from, $to]);

        if ($type === 'day') {
            $query->selectRaw('SUM(quantity) as total, DATE(created_at) as date')
                ->groupBy(DB::raw('DATE(created_at)'));
        } elseif ($type === 'month') {
            $query->selectRaw('SUM(quantity) as total, DATE_FORMAT(created_at, "%Y-%m") as date')
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));
        } elseif ($type === 'year') {
            $query->selectRaw('SUM(quantity) as total, YEAR(created_at) as date')
                ->groupBy(DB::raw('YEAR(created_at)'));
        }

        return response()->json($query->orderBy('date')->get());
    }
}
