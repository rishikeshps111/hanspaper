<?php

namespace App\Http\Controllers;

use App\Models\Real;
use App\Models\RealStock;
use App\Models\Items\Brand;
use Illuminate\Http\Request;
use App\Models\Items\ItemCategory;
use App\Http\Requests\StoreRealRequest;
use App\Http\Requests\UpdateRealRequest;
use App\Models\ProductionList;
use App\Models\PurchaseOrders\PurchaseOrderMaster;




class RealController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $reals = Real::query()
                ->leftJoin('brands', 'brands.id', '=', 'reals.brand')
                 // ->leftJoin('real_stocks', 'real_stocks.real_id', '=', 'reals.id')
                ->leftJoin('item_categories', 'item_categories.id', '=', 'reals.category')
                ->select([
                    'reals.*',
                    'brands.name as brand_name',
                    'item_categories.name as category_name'
                ]);

            return datatables()->of($reals)

                ->addColumn('real_id', function ($row) {
                    return 'REAL' . str_pad($row->id, 3, '0', STR_PAD_LEFT);
                })

                ->filterColumn('real_id', function ($query, $keyword) {
                    $keyword = preg_replace('/real/i', '', $keyword);
                    $keyword = ltrim($keyword, '0');
                    if ($keyword === '') {
                        $keyword = 0;
                    }
                    $query->where('reals.id', 'like', "%{$keyword}%");
                })

                ->addColumn('brand_relation.name', function ($row) {
                    return $row->brand_name;
                })

                ->addColumn('category_relation.name', function ($row) {
                    return $row->category_name;
                })

                ->addColumn('actions', function ($row) {
                    return view('reals.action', compact('row'))->render();
                })

                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->filterColumn('brand_relation.name', function ($query, $keyword) {
                    $query->where('brands.name', 'like', "%{$keyword}%");
                })

                ->filterColumn('category_relation.name', function ($query, $keyword) {
                    $query->where('item_categories.name', 'like', "%{$keyword}%");
                })

                ->addIndexColumn()
                ->rawColumns(['actions', 'is_active'])
                ->make(true);
        }

        return view('reals.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::get();;
        $categories = ItemCategory::get();
        return view('reals.create', compact('brands', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRealRequest $request)
    {
        $data = $request->validated();
        Real::create($data);

        return redirect()->route('reals.index')->with('success', __('Real created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {


        $record = Real::with(['brandRelation', 'categoryRelation','stocksRelation'])->findOrFail($id);
        $workorder = PurchaseOrderMaster::get();

//print_r($record);
//exit;
        $html = view('reals.partials.details', compact('record', 'id', 'workorder'))->render();

        return response()->json([
            'html' => $html,
            'id'   => $id,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Real $real)
    {
        $brands = Brand::all();
        $categories = ItemCategory::all();

        return view('reals.edit', compact('real', 'brands', 'categories'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRealRequest $request, Real $real)
    {
        $data = $request->validated();
        $real->update($data);
        return redirect()->route('reals.index')->with('success', __('Real updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Real $real)
    {
        $real->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Real deleted successfully!'
        ]);
    }

    public function details($id)
    {
        $record = Real::find($id);
        return view('reals.details', compact('record'));
    }

    public function getPage($id)
    {
        $perPage = request()->get('length', 10); // same as DataTables pageLength

        // Count how many rows come before this one in DESC order
        $position = Real::where('id', '>', $id)->count() + 1;

        // Convert position to page (0-based index)
        $page = floor(($position - 1) / $perPage);

        return response()->json(['page' => $page]);
    }

    public function productionList(Request $request, $id)
    {
        $query = ProductionList::with([
            'productionItemMaster.purchaseOrder.party',
            'productionItemMaster.item'
        ])->where('real_id', $id);

        if ($request->filled('work_order')) {
            $query->whereHas('productionItemMaster.purchaseOrder', function ($q) use ($request) {
                $q->where('purchase_order_id', 'like', '%' . $request->work_order . '%');
            });
        }
        return datatables()->of($query)
            ->addColumn('work_order', function ($row) {
                return $row->productionItemMaster->purchaseOrder->purchase_order_id;
            })
            ->addColumn('customer', function ($row) {
                if(isset($row->productionItemMaster->purchaseOrder->party->first_name))
                {
                return $row->productionItemMaster->purchaseOrder->party->first_name;
               }
            })
            ->addColumn('item', function ($row) {
                return $row->productionItemMaster->item->name;
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('d M Y') : '';
            })
            ->rawColumns(['item', 'customer', 'requested_qty', 'due_date',  'status'])
            ->make(true);
    }
    
    public function realStock($id)
    {
        $real = Real::with('stocks')->findOrFail($id);


        $html = view('reals.partials.stock-modal', compact('real'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'real_no' => $real->real_no
        ]);
    }

    public function realStockStore(Request $request)
    {
        $data =  $request->validate([
            'real_id' => 'required|exists:reals,id',
            'quantity' => 'required|integer|min:1',
        ]);
        /*$data['type'] = 'in';
        RealStock::create($data);*/
  



        //new code for real

          $details = RealStock::with('real')->where('real_id', $request->real_id)->get();
              $reqquantity=$request->quantity;
                     if (count($details) != 0)
                   { //update

                     $quantity=$details[0]['quantity'];

                       RealStock::where('real_id',  $request->real_id)
                        ->update([
                           'type' => 'in',
                             'quantity' =>$quantity+$reqquantity,
                        ]);

                   }
                   else
                   {
                        //insert

                    $data['type'] = 'in';
                       $data['quantity'] = $reqquantity;
        RealStock::create($data);

                   }







        return response()->json([
            'success' => true,
            'message' => 'Stock added successfully'
        ]);
    }
    
    
     public function modalCreate()
    {
        $brands = Brand::get();
        $categories = ItemCategory::get();

        return view('reals.partials.modal-create', compact('brands', 'categories'));
    }

    public function modalStore(StoreRealRequest $request)
    {
        $real = Real::with(['brandRelation', 'categoryRelation'])
            ->create($request->validated());

        $real->load(['brandRelation', 'categoryRelation']);

        return response()->json([
            'success' => true,
            'real' => [
                'id' => $real->id,
                'text' => $real->formatted_id . ' - ' . $real->real_no,
                'title' =>
                $real->formatted_id .
                    ' | Brand: ' . ($real->brandRelation->name ?? '-') .
                    ' | Category: ' . ($real->categoryRelation->name ?? '-') .
                    ' | GSM: ' . ($real->gsm ?? '-') .
                    ' | Width: ' . ($real->width ?? '-') .
                    ' | Length: ' . ($real->length ?? '-') .
                    ' | Weight: ' . ($real->weight ?? '-') .
                    ' | Subcode: ' . ($real->subcode ?? '-')
            ]
        ]);
    }
  

    public function report($id)
    {
       $real = Real::with(['brandRelation', 'categoryRelation','stocksRelation'])->findOrFail($id);

         $order = PurchaseOrderMaster::with([
             'party',
            'items.product',
             'items.brand',
            'items.category'
         ])->findOrFail($id);

        //   dd($order);
         return view('reals.report', compact('real', 'id', 'order'));

      //  return redirect()->route('reals.partials.details', ['id' => $id]);

    }
public function finished(Request $request)
    {
        if ($request->ajax()) {


            $reals = Real::query()
                ->leftJoin('brands', 'brands.id', '=', 'reals.brand')
                  ->leftJoin('real_stocks', 'real_stocks.real_id', '=', 'reals.id')
                ->leftJoin('item_categories', 'item_categories.id', '=', 'reals.category')
                ->select([
                    'reals.*',
                    'brands.name as brand_name',
                    'item_categories.name as category_name'
                ]);

                $reals->where('real_stocks.status', 'full');

            return datatables()->of($reals)

                ->addColumn('real_id', function ($row) {
                    return 'REAL' . str_pad($row->id, 3, '0', STR_PAD_LEFT);
                })

                ->filterColumn('real_id', function ($query, $keyword) {
                    $keyword = preg_replace('/real/i', '', $keyword);
                    $keyword = ltrim($keyword, '0');
                    if ($keyword === '') {
                        $keyword = 0;
                    }
                    $query->where('reals.id', 'like', "%{$keyword}%");
                })

                ->addColumn('brand_relation.name', function ($row) {
                    return $row->brand_name;
                })

                ->addColumn('category_relation.name', function ($row) {
                    return $row->category_name;
                })

                ->addColumn('actions', function ($row) {
                    return view('reals.action', compact('row'))->render();
                })

                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->filterColumn('brand_relation.name', function ($query, $keyword) {
                    $query->where('brands.name', 'like', "%{$keyword}%");
                })

                ->filterColumn('category_relation.name', function ($query, $keyword) {
                    $query->where('item_categories.name', 'like', "%{$keyword}%");
                })

                ->addIndexColumn()
                ->rawColumns(['actions', 'is_active'])
                ->make(true);
        }

        return view('reals.finished');
    }

    public function alllist(Request $request)
    {
      if ($request->ajax()) {

            $reals = Real::query()
                ->leftJoin('brands', 'brands.id', '=', 'reals.brand')
                 // ->leftJoin('real_stocks', 'real_stocks.real_id', '=', 'reals.id')
                ->leftJoin('item_categories', 'item_categories.id', '=', 'reals.category')
                ->select([
                    'reals.*',
                    'brands.name as brand_name',
                    'item_categories.name as category_name'
                ]);
                //$reals->groupBy('brand_name');
                // $reals->groupBy('category_name');

            return datatables()->of($reals)

                ->addColumn('real_id', function ($row) {
                    return 'REAL' . str_pad($row->id, 3, '0', STR_PAD_LEFT);
                })

                ->filterColumn('real_id', function ($query, $keyword) {
                    $keyword = preg_replace('/real/i', '', $keyword);
                    $keyword = ltrim($keyword, '0');
                    if ($keyword === '') {
                        $keyword = 0;
                    }
                    $query->where('reals.id', 'like', "%{$keyword}%");
                })

                ->addColumn('brand_relation.name', function ($row) {
                    return $row->brand_name;
                })

                ->addColumn('category_relation.name', function ($row) {
                    return $row->category_name;
                })

                ->addColumn('actions', function ($row) {
                    return view('reals.action', compact('row'))->render();
                })

                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->filterColumn('brand_relation.name', function ($query, $keyword) {
                    $query->where('brands.name', 'like', "%{$keyword}%");
                })

                ->filterColumn('category_relation.name', function ($query, $keyword) {
                    $query->where('item_categories.name', 'like', "%{$keyword}%");
                })

                ->addIndexColumn()
                ->rawColumns(['actions', 'is_active'])
                ->make(true);
        }


        return view('reals.alllist');
    }
  
  

    
}
