<?php

namespace App\Http\Controllers\Items;


use Carbon\Carbon;
use App\Models\Tax;
use App\Models\Role;
use App\Models\Real;
use App\Models\Unit;
use App\Models\User;
use Spatie\Image\Image;
use App\Models\Items\Item;
use App\Models\PackingList;
use App\Models\Party\Party;
use App\Traits\FormatNumber;
use Illuminate\Http\Request;
use App\Services\ItemService;
use App\Models\ProductionList;
use App\Models\ProductionRun;
use App\Models\Core;
use App\Models\CoreStockMovement;
use App\Models\PackingMaterial;
use App\Models\PackingMaterialStockMovement;
use App\Services\CacheService;
use App\Models\Items\ItemSerial;
use App\Models\Dispatch\Dispatch;
use App\Traits\FormatsDateInputs;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Models\Items\ProductionItem;
use App\Models\Items\ItemBatchMaster;
use App\Models\Items\ItemTransaction;
use Illuminate\Support\Facades\Cache;
use App\Models\Items\ItemSerialMaster;
use App\Models\Items\ItemBatchQuantity;
use Illuminate\Support\Facades\Storage;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\Items\ItemSerialQuantity;
use App\Services\ItemTransactionService;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Items\ItemGeneralQuantity;
use Illuminate\Support\Facades\Validator;
use App\Models\Items\ItemBatchTransaction;
use App\Models\Items\ProductionItemMaster;
use App\Services\AccountTransactionService;
use App\Models\PurchaseOrders\PurchaseOrderItem;
use App\Models\PurchaseOrders\PurchaseOrderMaster;
use App\Models\Machines\Machine;
use App\Models\Employees\Employee;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\RealStock;
use App\Models\Items\ItemCategory;
use App\Models\Items\Brand;
use App\Models\Reels\ReelStock;
use App\Models\Reels\ReelStockMovement;
use App\Models\Reels\ReelStockUsage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProductionItemMasterController extends Controller
{
    public function index()
    {

        //  $productionLists = ProductionItemMaster::with('item','requestedBy','approvedBy','purchaseOrder')->get();
        $productionLists = ProductionItemMaster::with('item', 'item.brand', 'item.category', 'requestedBy', 'approvedBy', 'purchaseOrder.party', 'productionLists', 'packingLists')->get();

        //  dd($productionLists);
        return view('production.productionlist', compact('productionLists'));
    }

    public function create()
    {
        $user = auth()->user();
        return view('production.create', compact('user'));
    }



    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $rules = [
                'order_date' => 'required|date',
                'due_date' => 'required|date|after:order_date',
                'requested_by' => 'required|exists:users,id',
                'approved_by' => 'required|exists:users,id',
                'operation' => 'required|string',
                'representative_id' => 'nullable|exists:sales_representatives,id',
                'item_id' => 'required|array|min:1',
                'item_id.*' => 'required|exists:items,id',
                'quantity' => 'required|array',
                'quantity.*' => 'required|numeric|min:1',
                'remarks' => 'required|array',
                'remarks.*' => 'required|string',
                'pakingremarks' => 'required|array',
                'pakingremarks.*' => 'required|string',
                'dispatchremarks' => 'required|array',
                'dispatchremarks.*' => 'required|string',
            ];

            $messages = [
                'order_date.required' => 'Order date is required.',
                'order_date.date' => 'Order date must be a valid date.',
                'due_date.required' => 'Due date is required.',
                'due_date.date' => 'Due date must be a valid date.',
                'due_date.after' => 'Due date must be a date after the order date.',

                'requested_by.required' => 'Requested by is required.',
                'requested_by.exists' => 'Requested by user is invalid.',

                'approved_by.required' => 'Approved by is required.',
                'approved_by.exists' => 'Approved by user is invalid.',

                'item_id.required' => 'At least one item is required.',
                'item_id.*.required' => 'Item is required.',
                'item_id.*.exists' => 'One or more selected items are invalid.',


                'quantity.*.required' => 'Quantity is required for each item.',
                'quantity.*.numeric' => 'Quantity must be a number.',
                'quantity.*.min' => 'Quantity must be at least 1.',

                'remarks.*.required' => 'Production remark is required for each item.',
                'pakingremarks.*.required' => 'Packing remark is required for each item.',
                'dispatchremarks.*.required' => 'Dispatch remark is required for each item.',
            ];

            // Validate
            $request->validate($rules, $messages);

            $microtime = microtime(true);
            $milliseconds = sprintf("%03d", ($microtime - floor($microtime)) * 1000);
            $purchase_order_id = 'WO-' . date('dmY-His') . $milliseconds;

            $workOrder = PurchaseOrderMaster::create([
                'purchase_order_id' => $purchase_order_id,
                'representative_id' => $request->representative_id,
                'po_date' => $request->order_date,
                'due_date' => $request->due_date,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'purchase_order_status' => 'Production',
            ]);

            foreach ($request->item_id as $index => $itemId) {
                $productionItem = new ProductionItemMaster();
                $productionItem->item_id = $itemId;
                $productionItem->requested_qty = $request->quantity[$index];
                $productionItem->production_remarks = $request->remarks[$index];
                $productionItem->packing_remarks = $request->pakingremarks[$index];
                $productionItem->dispatch_remarks = $request->dispatchremarks[$index];
                $productionItem->requested_by = $request->requested_by;
                $productionItem->approved_by = $request->approved_by;
                $productionItem->production_type = 'Stock';
                $productionItem->status = 'Assigning Pending';
                $productionItem->production_status = 'Pending';
                $productionItem->packing_status = 'Pending';
                $productionItem->purchase_order_id = $workOrder->id;
                $productionItem->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Work Order and Production records created successfully!'),
                'redirect' => route('item.production.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function show(ProductionItemMaster $productionItemMaster)
    {
        return view('production_item_masters.show', compact('productionItemMaster'));
    }

    public function edit($id)
    {
        $user = auth()->user();
        $productionItemMaster = ProductionItemMaster::with(['item', 'item.brand', 'requestedBy', 'approvedBy', 'purchaseOrder.party', 'assignedMachine', 'assignedProductionUser', 'assignedPackingUser', 'activeRun.reelStock.reel', 'activeRun.reelStock.provider', 'activeRun.reelStock.warehouse', 'activeRun.machine', 'activeRun.productionUser', 'activeRun.core'])->findOrFail($id);
        return view('production.edit', [
            'user' => $user,
            'productionItemMaster' => $productionItemMaster,
            'activeRun' => $productionItemMaster->activeRun,
            'availableMachines' => Machine::where('status', 'Active')->whereDoesntHave('activeProductionRun')->orderBy('machine_name')->get(),
        ]);

    }

    public function reelStockSearch(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));
        $page = max(1, $request->integer('page', 1));
        $perPage = 20;

        $query = ReelStock::query()
            ->with(['reel:id,code,width', 'warehouse:id,name', 'provider:id,name'])
            ->where('is_active', true)
            ->whereIn('status', ['full', 'bit'])
            ->where('balance_length', '>', 0)
            ->whereDoesntHave('activeProductionRun')
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($query) use ($term) {
                    $query->where('stock_code', 'like', "%{$term}%")
                        ->orWhere('actual_code', 'like', "%{$term}%")
                        ->orWhereHas('reel', fn ($reel) => $reel->where('code', 'like', "%{$term}%"))
                        ->orWhereHas('provider', fn ($provider) => $provider->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('warehouse', fn ($warehouse) => $warehouse->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderBy('stock_code');

        $stocks = $query->skip(($page - 1) * $perPage)->take($perPage + 1)->get();
        $more = $stocks->count() > $perPage;

        return response()->json([
            'results' => $stocks->take($perPage)->map(function (ReelStock $stock) {
                $sourceWidth = (float) ($stock->reel?->width ?? 0);
                $cutWidth = (float) ($stock->cut_width ?? 0);
                $balance = (float) $stock->balance_length;
                $widthSplits = $cutWidth > 0 ? (int) floor($sourceWidth / $cutWidth) : 0;
                $actualLength = $stock->status === 'bit' && $widthSplits > 0
                    ? $balance / $widthSplits
                    : $balance;
                $lengthLabel = number_format($actualLength, 2) . ' m';

                return [
                    'id' => $stock->id,
                    'text' => implode(' | ', array_filter([
                        $stock->stock_code,
                        $stock->actual_code,
                        $stock->reel?->code,
                        $stock->provider?->name,
                        $stock->warehouse?->name,
                        $lengthLabel,
                    ])),
                    'status' => $stock->status,
                    'width' => $sourceWidth,
                    'balance' => $balance,
                    'cut_width' => $cutWidth,
                    'width_splits' => $widthSplits,
                    'actual_length' => round($actualLength, 3),
                ];
            })->values(),
            'pagination' => ['more' => $more],
        ]);
    }

    public function coreSearch(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));
        $cores = Core::query()->where('is_active', true)
            ->when($term !== '', fn ($query) => $query->where(fn ($q) => $q->where('code', 'like', "%{$term}%")->orWhere('size_mm', 'like', "%{$term}%")))
            ->withSum(['productionRuns as reserved_quantity' => fn ($query) => $query->where('status', 'in_progress')], 'core_quantity')
            ->orderBy('code')->limit(30)->get()
            ->map(fn (Core $core) => [
                'id' => $core->id,
                'text' => "{$core->code} | {$core->size_mm} mm | Available: " . max(0, $core->quantity - (int) $core->reserved_quantity),
                'code' => $core->code, 'size_mm' => (float) $core->size_mm,
                'available_quantity' => max(0, $core->quantity - (int) $core->reserved_quantity),
            ])->filter(fn ($core) => $core['available_quantity'] > 0)->values();
        return response()->json(['results' => $cores]);
    }

    public function update(Request $request, ProductionItemMaster $productionItemMaster)
    {
        $productionItemMaster->update($request->all());
        return redirect()->route('production_item_masters.index');
    }

    public function destroy(ProductionItemMaster $productionItemMaster)
    {
        $productionItemMaster->delete();
        return redirect()->route('production_item_masters.index');
    }

    public function storePacking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'production_id' => 'required|exists:production_item_masters,id',
            'packed_qty' => 'required|numeric|min:1',
            'packed_by' => 'required|exists:employees,id',
            'packing_box_id' => 'required|exists:packing_materials,id',
            'packing_box_quantity' => 'required|integer|min:1',
            'packing_cover_id' => 'required|exists:packing_materials,id',
            'packing_cover_quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $productionItemMaster = ProductionItemMaster::lockForUpdate()->findOrFail($request->production_id);
            $box = PackingMaterial::lockForUpdate()->findOrFail($request->packing_box_id);
            $cover = PackingMaterial::lockForUpdate()->findOrFail($request->packing_cover_id);
            if ($box->type !== 'box' || !$box->is_active || $box->quantity < $request->integer('packing_box_quantity')) {
                throw ValidationException::withMessages(['packing_box_id' => 'Selected packing box stock is unavailable or insufficient.']);
            }
            if ($cover->type !== 'cover' || !$cover->is_active || $cover->quantity < $request->integer('packing_cover_quantity')) {
                throw ValidationException::withMessages(['packing_cover_id' => 'Selected packing cover stock is unavailable or insufficient.']);
            }

            // Get fresh totals
            $totalProducedQty = $productionItemMaster->productionLists()->sum('quantity');
            $existingPackedQty = $productionItemMaster->packingLists()->sum('quantity');
            $newPackedQty = $existingPackedQty + $request->packed_qty;

            // Prevent overpacking
            if ($newPackedQty > $totalProducedQty) {
                throw ValidationException::withMessages(['packed_qty' => 'Packing quantity exceeds total produced quantity.']);
            }

            // Save new packing record
            $record = PackingList::create([
                'production_item_master_id' => $productionItemMaster->id,
                'packed_by' => $request->packed_by,
                'quantity' => $request->packed_qty,
                'packing_box_id' => $box->id,
                'packing_box_quantity' => $request->integer('packing_box_quantity'),
                'packing_cover_id' => $cover->id,
                'packing_cover_quantity' => $request->integer('packing_cover_quantity'),
            ]);
            foreach ([[$box,$request->integer('packing_box_quantity')],[$cover,$request->integer('packing_cover_quantity')]] as [$material,$used]) {
                $before=$material->quantity;$material->update(['quantity'=>$before-$used,'updated_by'=>auth()->id()]);
                PackingMaterialStockMovement::create(['packing_material_id'=>$material->id,'transaction_type'=>'packing_usage','quantity_change'=>-$used,'quantity_before'=>$before,'quantity_after'=>$before-$used,'reference_type'=>PackingList::class,'reference_id'=>$record->id,'remarks'=>"{$used} used for packing production #{$productionItemMaster->id}.",'created_by'=>auth()->id()]);
            }
            
            $productionItemMaster->update([
                'assigned_packing_user_id' => $request->packed_by
            ]);


            // Update packing_status - CORRECTED CONDITION
            if ($newPackedQty == $totalProducedQty) {
                $productionItemMaster->packing_status = 'Completed';
            } else {
                $productionItemMaster->packing_status = 'Partial';
            }

            // Check if production is complete
            $isProductionCompleted = $totalProducedQty == $productionItemMaster->requested_qty;
            $isPackingCompleted = $newPackedQty == $totalProducedQty;

            // Update overall status based on new logic
            if ($isProductionCompleted && !$isPackingCompleted) {
                $productionItemMaster->status = 'Packing Pending';
            } elseif ($isProductionCompleted && $isPackingCompleted) {
                $productionItemMaster->status = 'Completed';

                // Update stock when both production and packing are completed
                ItemTransaction::where('item_id', $productionItemMaster->item_id)
                    ->increment('quantity', $productionItemMaster->requested_qty);

                if ($productionItemMaster->production_type == 'Stock') {
                    ItemTransaction::where('item_id', $productionItemMaster->item_id)
                        ->increment('avaquantity', $productionItemMaster->requested_qty);

                    // Update item_general_quantities
                    ItemGeneralQuantity::updateOrCreate(
                        [
                            'item_id' => $productionItemMaster->item_id,
                            'warehouse_id' => 1,
                        ],
                        [
                            'quantity' => DB::raw("quantity + {$productionItemMaster->requested_qty}"),
                            'avaquantity' => DB::raw("avaquantity + {$productionItemMaster->requested_qty}"),

                        ]
                    );
                } else {

                    ItemGeneralQuantity::updateOrCreate(
                        [
                            'item_id' => $productionItemMaster->item_id,
                            'warehouse_id' => 1,
                        ],
                        [
                            'quantity' => DB::raw("quantity + {$productionItemMaster->requested_qty}"),
                        ]
                    );
                }




                ///new code update dispatch

                $purchaseOrderId = $productionItemMaster->purchase_order_id;
                // Update Purchase Order Item if production completed
                if ($purchaseOrderId) {
                    PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)
                        ->where('product_id', $productionItemMaster->item_id) // Correct column
                        ->update([
                            'status' => 'Ready to Dispatch',
                            'updated_by' => auth()->id(),
                        ]);

                    // Check if all items of the Purchase Order are now 'Ready to Dispatch'
                    $pendingItemsCount = PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)
                        //   ->where('status', '!=', 'Ready to Dispatch')
                        ->whereNotIn('status', ['Ready to Dispatch', 'Move to Dispatch'])
                        ->count();


                    if ($pendingItemsCount === 0) {
                        PurchaseOrderMaster::where('id', $purchaseOrderId)
                            ->update([
                                'purchase_order_status' => 'Dispatch Pending',
                                'updated_by' => auth()->id(),
                            ]);
                    }

                    if ($pendingItemsCount === 0) {
                        $details = PurchaseOrderMaster::find($purchaseOrderId); // <-- find(), not where()
                        if ($details) {
                            $microtime = microtime(true);
                            $milliseconds = sprintf("%03d", ($microtime - floor($microtime)) * 1000);
                            $dispatch_order = 'DIS-' . date('dmY-His') . $milliseconds;
                            $did = Dispatch::create([
                                'purchase_order_id' => $details->id,
                                'purchase_order_identifier' => $details->purchase_order_id,
                                'customer_id' => $details->customer_id,
                                'status' => 'Dispatch Pending',
                                'remarks' => 'Auto created from production completion.',
                                'mode_of_delivery' => 'Company Vehicle',
                                'dispatch_order' => $dispatch_order,
                            ])->id;


                            PurchaseOrderItem::where('purchase_order_id', $details->id)
                                ->where('product_id', $productionItemMaster->item_id)
                                ->update([
                                    'dispatches_id' => $did,
                                    'status' => 'Move to Dispatch',
                                    'updated_by' => auth()->id(),
                                ]);

                            ProductionItemMaster::where('purchase_order_id', $details->id)
                                ->where('item_id', $productionItemMaster->item_id)
                                ->update([
                                    'dispatches_id' => $did
                                ]);

                            //remaining products
                            $pendingremainItems = PurchaseOrderItem::where('purchase_order_id', $details->id)
                                ->where('status', '=', 'Ready to Dispatch')
                                ->get();

                            foreach ($pendingremainItems as $purchase_order_item) {

                                $product_id = $purchase_order_item['product_id'];

                                PurchaseOrderItem::where('purchase_order_id', $details->id)
                                    ->where('product_id', $product_id)
                                    ->update([
                                        'dispatches_id' => $did,
                                        'status' => 'Move to Dispatch',
                                        'updated_by' => auth()->id(),
                                    ]);

                                ProductionItemMaster::where('purchase_order_id', $details->id)
                                    ->where('item_id', $product_id)
                                    ->update([
                                        'dispatches_id' => $did
                                    ]);
                            }
                        }
                    }
                }
            } else {
                $productionItemMaster->status = 'Progress';
            }

            $productionItemMaster->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Packing record saved and statuses updated successfully.',
                'redirect' => route('item.production.edit', ['id' => $request->production_id])
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while saving packing.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeProduction(Request $request)
    {
        return $this->storeProductionWithReelStock($request);

        $validator = Validator::make($request->all(), [
            'production_id' => 'required|exists:production_item_masters,id',
            'production_qty' => 'required|numeric|min:1',
            'packed_by' => 'required|exists:employees,id',
            'machines' => 'required|exists:machines,id',
            'real_number' => 'required|exists:reals,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $productionItemMaster = ProductionItemMaster::find($request->production_id);
            $totalProducedQty = $productionItemMaster->productionLists()->sum('quantity');
            $newTotal = $totalProducedQty + $request->production_qty;

            if ($newTotal > $productionItemMaster->requested_qty) {
                return response()->json([
                    'status' => false,
                    'message' => 'Production quantity exceeds requested quantity.'
                ], 400);
            }

            // Save new production entry
            ProductionList::create([
                'production_item_master_id' => $productionItemMaster->id,
                'machine_id' => $request->machines,
                'produced_by' => $request->packed_by,
                'quantity' => $request->production_qty,
                'real_id' => $request->real_number,
            ]);
            $statusRealStock = ($request->is_finisher == 'yes') ? 'full' : 'bit';


      Real::where('id', $request->real_number)
                        ->update([
                          
                              'current_status' => $statusRealStock,
                             

                        ]);

         $stock_status = $request->stock_status;
    
                 $record = Real::query()
                  ->leftJoin('brands', 'brands.id', '=', 'reals.brand')
                ->leftJoin('item_categories', 'item_categories.id', '=', 'reals.category')
                ->select([
                    'reals.*','reals.id as realsid',
                    'brands.name as brand_name',
                    'item_categories.name as category_name'
                ])->findOrFail($request->real_number);  
                $category=$record['category_name']; 
                if($category==('Thermal Paper'||'THERMAL PAPER PRINTING'||'THERMAL PAPER ROLL'))
                {

                         $width=$record['width'];
                       $length=$record['length']; 
                         $rid =$record['realsid']; 

                 $productionItemMaster = ProductionItemMaster::with(['item', 'item.brand', 'requestedBy', 'approvedBy', 'purchaseOrder.party', 'assignedMachine', 'assignedProductionUser', 'assignedPackingUser'])->findOrFail($productionItemMaster->id);
                  $widthproduct=$productionItemMaster['item']['width'];
                 $lengthproduct=$productionItemMaster['item']['length'];
                 $totlength=0;
                 $orgbalance_length=0;
                 $balance_length=0;
                 $used_length=0;
                if(($widthproduct!='')&&($lengthproduct!=''))
                 {
                  $tot_sheet=$width/$widthproduct;
                  $tot_sheet=floor($tot_sheet);
                   $totlength=$length*$tot_sheet;
                   $totlength=floor($totlength);
                    $used_length=$lengthproduct*$request->production_qty;
                    $used_length=floor($used_length);
                    $balance_length= $totlength-$used_length;
                     $balance_length=floor($balance_length);
                  }
                    //echo $rid;
                    $details = RealStock::with('real')->where('real_id', $rid)->get();
                    //print_r($details);
                  //  $details = RealStock::where('real_id', $rid); // <-- find(), not where()
                  //  if(!empty($details))
                  if (count($details) != 0)
                   { 
                   //print_r($details);


                     $total_lengthupdate=$details[0]['total_length'];
                    $bal_lengthupdate=$details[0]['bal_length'];
                    if($total_lengthupdate>0)
                    {
                    $used_lengthupdate=$lengthproduct*$request->production_qty;
                    $orgbalance_length= $bal_lengthupdate-$used_lengthupdate;
                    }
                    else

                    {

                       $orgbalance_length= $totlength-$used_length;
                    }


                         if(($total_lengthupdate>=0)&&($orgbalance_length>=0))
                        {

                     RealStock::where('real_id', $rid)
                        ->update([
                           'type' => 'out',
                             'quantity' => 1,
                              'status' => $statusRealStock,
                              'total_length'=> $totlength,
                           'bal_length' =>$orgbalance_length,
                           'stock_status'=>$stock_status

                        ]);
                         
                        }
                        else
                        {
                     RealStock::where('real_id', $rid)
                        ->update([
                           'type' => 'out',
                             'quantity' => 1,
                              'status' => $statusRealStock,
                              'stock_status'=>$stock_status
                        ]);
                        }
                        //update
                   }

                   else
                   {
                    //insert
                      RealStock::create([
                      'real_id' => $request->real_number,
                     'type' => 'out',
                       'quantity' => 1,
                      'status' => $statusRealStock,
                        'total_length' =>0,
                             'bal_length' =>0,
                             'stock_status'=>$stock_status
                        ]);
                         if(($widthproduct!='')&&($lengthproduct!=''))
                         {
                             if(($totlength>=0)&&($balance_length>=0))
                             {

                              RealStock::where('real_id', $rid)
                              ->update([
                                 'total_length' =>$totlength,
                                 'bal_length' =>$balance_length
                                ]);

                            }
                        }
                    }
                   }
                else
                {
                        $rid =$record['realsid']; 

                        $details = RealStock::with('real')->where('real_id', $rid)->get();

                        if (count($details) != 0)
                        {      
                                     RealStock::where('real_id', $rid)
                            ->update([
                           'type' => 'out',
                             'quantity' => 1,
                              'status' => $statusRealStock,
                              'stock_status'=>$stock_status
                                ]);
                        } 
                        else
                        {
                            RealStock::create([
                          'real_id' => $request->real_number,
                          'type' => 'out',
                          'quantity' => 1,
                            'status' => $statusRealStock,
                            'total_length' =>0,
                             'bal_length' =>0,
                             'stock_status'=>$stock_status
                            ]);

                        }      

                }

 //print_r($record);
 //exit;
             $productionItemMaster->update([
                'assigned_production_user_id' => $request->packed_by,
                'assigned_machine_id' => $request->machines,
            ]);

            // Update production status
            $productionItemMaster->production_status = $newTotal == $productionItemMaster->requested_qty ? 'Completed' : 'Partial';

            // Get packing status
            $totalPackedQty = $productionItemMaster->packingLists()->sum('quantity');
            $isPackingCompleted = $totalPackedQty == $newTotal;

            // Update overall status based on new logic
            if ($newTotal == $productionItemMaster->requested_qty && !$isPackingCompleted) {
                $productionItemMaster->status = 'Packing Pending';
            } elseif ($newTotal == $productionItemMaster->requested_qty && $isPackingCompleted) {
                $productionItemMaster->status = 'Completed';

                // Update stock when both production and packing are completed
                ItemTransaction::where('item_id', $productionItemMaster->item_id)
                    ->increment('quantity', $productionItemMaster->requested_qty);


                ItemGeneralQuantity::updateOrCreate(
                    [
                        'item_id' => $productionItemMaster->item_id,
                        'warehouse_id' => 1,
                    ],
                    [
                        'quantity' => DB::raw("quantity + {$productionItemMaster->requested_qty}"),
                    ]
                );

                ///new code update dispatch

                $purchaseOrderId = $productionItemMaster->purchase_order_id;
                // Update Purchase Order Item if production completed
                if ($purchaseOrderId) {
                    PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)
                        ->where('product_id', $productionItemMaster->item_id) // Correct column
                        ->update([
                            'status' => 'Ready to Dispatch',
                            'updated_by' => auth()->id(),
                        ]);

                    // Check if all items of the Purchase Order are now 'Ready to Dispatch'
                    $pendingItemsCount = PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)
                     //   ->where('status', '!=', 'Ready to Dispatch')

                        ->whereNotIn('status', ['Ready to Dispatch', 'Move to Dispatch'])
                        ->count();


                    if ($pendingItemsCount === 0) {
                        PurchaseOrderMaster::where('id', $purchaseOrderId)
                            ->update([
                                'purchase_order_status' => 'Dispatch Pending',
                                'updated_by' => auth()->id(),
                            ]);
                    }

                    if ($pendingItemsCount === 0) {



                        $details = PurchaseOrderMaster::find($purchaseOrderId); // <-- find(), not where()
                        if ($details) {
                            $microtime = microtime(true);
                            $milliseconds = sprintf("%03d", ($microtime - floor($microtime)) * 1000);
                            $dispatch_order = 'DIS-' . date('dmY-His') . $milliseconds;
                             $did=Dispatch::create([
                                'purchase_order_id' => $details->id,
                                'purchase_order_identifier' => $details->purchase_order_id,
                                'customer_id' => $details->customer_id,
                                'status' => 'Dispatch Pending',
                                'remarks' => 'Auto created from production completion.',
                                'mode_of_delivery' => 'Company Vehicle',
                                'dispatch_order' => $dispatch_order,
                            ])->id;

                               PurchaseOrderItem::where('purchase_order_id', $details->id)
                         ->where('product_id', $productionItemMaster->item_id)
                            ->update([
                                'dispatches_id' => $did,
                                'status'=>'Move to Dispatch',
                                'updated_by' => auth()->id(),
                            ]);

                             ProductionItemMaster::where('purchase_order_id', $details->id)
                            ->where('item_id', $productionItemMaster->item_id)
                             ->update([
                                'dispatches_id' => $did
                                                            ]);


//remaining products
                            $pendingremainItems = PurchaseOrderItem::where('purchase_order_id', $details->id)
                     ->where('status', '=', 'Ready to Dispatch')
                     ->get();

                     foreach($pendingremainItems as $purchase_order_item)
                           {
                                
                        $product_id=$purchase_order_item['product_id'];

                           PurchaseOrderItem::where('purchase_order_id', $details->id)
                              ->where('product_id', $product_id)
                            ->update([
                                'dispatches_id' => $did,
                                'status'=>'Move to Dispatch',
                                'updated_by' => auth()->id(),
                            ]);

                             ProductionItemMaster::where('purchase_order_id', $details->id)
                            ->where('item_id', $product_id)
                             ->update([
                                'dispatches_id' => $did
                                                            ]);


                        }


                        }
                    }
                }






            } elseif ($newTotal < $productionItemMaster->requested_qty) {
                $productionItemMaster->status = 'Partial';
            }

            $productionItemMaster->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Production saved and status updated.',
                'redirect' => route('item.production.edit', ['id' => $request->production_id])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while saving production.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function startProduction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_id' => ['required', 'exists:production_item_masters,id'],
            'packed_by' => ['required', 'exists:employees,id'],
            'machines' => ['required', 'exists:machines,id'],
            'reel_stock_id' => ['required', 'exists:reel_stocks,id'],
            'core_id' => ['required', 'exists:cores,id'],
            'roll_length' => ['required', 'numeric', 'gt:0'],
            'output_roll_width' => ['required', 'numeric', 'gt:0'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $production = ProductionItemMaster::lockForUpdate()->findOrFail($validated['production_id']);
                if (!in_array($production->status, ['Pending', 'Partial'], true)) {
                    throw ValidationException::withMessages(['production_id' => 'Only Pending or Partial production can be started.']);
                }
                if ((float) $production->productionLists()->sum('quantity') >= (float) $production->requested_qty) {
                    throw ValidationException::withMessages(['production_id' => 'The requested production quantity is already completed.']);
                }

                $stock = ReelStock::with('reel')->lockForUpdate()->findOrFail($validated['reel_stock_id']);
                $machine = Machine::lockForUpdate()->findOrFail($validated['machines']);
                $core = Core::lockForUpdate()->findOrFail($validated['core_id']);
                if (!$stock->is_active || !in_array($stock->status, ['full', 'bit'], true) || (float) $stock->balance_length <= 0) {
                    throw ValidationException::withMessages(['reel_stock_id' => 'Only an available Full or Bit reel with balance can be started.']);
                }
                if ($machine->status !== 'Active') {
                    throw ValidationException::withMessages(['machines' => 'Only an active machine can be used.']);
                }
                if (ProductionRun::where('status', 'in_progress')->where(function ($query) use ($production, $stock, $machine) {
                    $query->where('production_id', $production->id)->orWhere('reel_stock_id', $stock->id)->orWhere('machine_id', $machine->id);
                })->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages(['reel_stock_id' => 'The production, reel, or machine is already in use. Refresh and choose an available resource.']);
                }

                $outputWidth = round((float) $validated['output_roll_width'], 3);
                $sourceWidth = round((float) $stock->reel->width, 3);
                if ($outputWidth > $sourceWidth || floor($sourceWidth / $outputWidth) < 1) {
                    throw ValidationException::withMessages(['output_roll_width' => "Output roll width must fit within the source width of {$sourceWidth} mm."]);
                }
                if ($stock->cut_width !== null && abs((float) $stock->cut_width - $outputWidth) > 0.0001) {
                    throw ValidationException::withMessages(['output_roll_width' => "This Bit reel was already slit to {$stock->cut_width} mm."]);
                }

                if (!$core->is_active || $core->quantity < 1) {
                    throw ValidationException::withMessages(['core_id' => 'The selected core has no available quantity.']);
                }
                $rollLength = round((float) $validated['roll_length'], 3);

                ProductionRun::create([
                    'production_id' => $production->id, 'reel_stock_id' => $stock->id,
                    'machine_id' => $machine->id, 'production_user_id' => $validated['packed_by'],
                    'core_id' => $core->id, 'core_quantity' => null,
                    'source_reel_status' => $stock->status, 'output_roll_width' => $outputWidth,
                    'roll_length' => $rollLength, 'production_quantity' => null,
                    'status' => 'in_progress', 'active_key' => 1, 'started_at' => now(), 'started_by' => auth()->id(),
                ]);
                $production->update([
                    'status' => 'In Progress', 'production_status' => 'In Progress',
                    'assigned_machine_id' => $machine->id, 'assigned_production_user_id' => $validated['packed_by'],
                ]);
            });

            return response()->json(['status' => true, 'message' => 'Production started successfully.', 'redirect' => route('item.production.edit', $validated['production_id'])]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['status' => false, 'message' => 'Unable to start production. Please refresh and try again.'], 500);
        }
    }

    private function storeProductionWithReelStock(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'production_id' => ['required', 'exists:production_item_masters,id'],
            'production_run_id' => ['required', 'exists:production_runs,id'],
            'production_qty' => ['required', 'integer', 'min:1'],
            'reel_status_after_usage' => ['required', 'in:bit,finished'],
            'reel_status_selection_type' => ['required', 'in:automatic,manual'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::transaction(function () use ($request) {
                $production = ProductionItemMaster::lockForUpdate()->findOrFail($request->integer('production_id'));
                $run = ProductionRun::lockForUpdate()->findOrFail($request->integer('production_run_id'));
                if ($run->production_id !== $production->id || $run->status !== 'in_progress' || $run->active_key !== 1) {
                    throw ValidationException::withMessages(['production_run_id' => 'This production run is no longer active. Refresh the page.']);
                }
                $stock = ReelStock::with('reel')->lockForUpdate()->findOrFail($run->reel_stock_id);
                $core = $run->core_id ? Core::lockForUpdate()->findOrFail($run->core_id) : null;

                if (!$stock->is_active || !in_array($stock->status, ['full', 'bit'], true)) {
                    throw ValidationException::withMessages(['reel_stock_id' => 'Only active Full or Bit Reel stock can be used.']);
                }

                $rollLength = round((float) $run->roll_length, 3);
                $outputWidth = round((float) $run->output_roll_width, 3);
                $sourceWidth = round((float) $stock->reel->width, 3);
                if ($outputWidth > $sourceWidth) {
                    throw ValidationException::withMessages([
                        'output_roll_width' => "Output roll width cannot exceed the source width of {$sourceWidth} mm.",
                    ]);
                }

                $rollCount = (int) floor($sourceWidth / $outputWidth);
                if ($rollCount < 1) {
                    throw ValidationException::withMessages(['output_roll_width' => 'The selected width does not produce any rolls.']);
                }

                $totalProducedQty = (float) $production->productionLists()->sum('quantity');
                $productionQuantity = (int) $request->input('production_qty');
                $coreQuantity = $productionQuantity;
                if ($core && $core->quantity < $coreQuantity) {
                    throw ValidationException::withMessages(['core_id' => 'Core stock is no longer sufficient to finish this production.']);
                }
                $orderRemaining = max(0, (float) $production->requested_qty - $totalProducedQty);
                $orderQuantity = min($productionQuantity, $orderRemaining);
                $excessStockQuantity = max(0, $productionQuantity - $orderQuantity);
                $newTotal = $totalProducedQty + $orderQuantity;

                if ($stock->cut_width !== null && abs((float) $stock->cut_width - $outputWidth) > 0.0001) {
                    throw ValidationException::withMessages([
                        'output_roll_width' => "This Bit reel was already slit to {$stock->cut_width} mm. Continue using the same width.",
                    ]);
                }

                $balanceBefore = $stock->cut_width === null
                    ? round((float) $stock->balance_length * $rollCount, 3)
                    : round((float) $stock->balance_length, 3);
                $consumedLength = round($productionQuantity * $rollLength, 3);
                if ($consumedLength > $balanceBefore) {
                    $possibleQuantity = (int) floor($balanceBefore / $rollLength);
                    throw ValidationException::withMessages([
                        'production_qty' => "Only {$possibleQuantity} roll(s) can be produced from the available {$balanceBefore} m.",
                    ]);
                }

                $balanceAfter = round($balanceBefore - $consumedLength, 3);
                $calculatedStatus = $balanceAfter <= 0 ? 'finished' : 'bit';
                $resultingStatus = $request->input('reel_status_after_usage');
                if ($resultingStatus === 'bit' && $balanceAfter <= 0) {
                    throw ValidationException::withMessages([
                        'reel_status_after_usage' => 'Bit cannot be selected because no usable balance remains.',
                    ]);
                }
                $statusSelectionType = $request->input('reel_status_selection_type') === 'manual' ||
                    $resultingStatus !== $calculatedStatus ? 'manual' : 'automatic';
                $sourceStatus = $stock->status;
                $totalOutputLength = $balanceBefore;
                $widthWaste = round($sourceWidth - ($outputWidth * $rollCount), 3);
                $physicalRemainingLength = round($balanceAfter / $rollCount, 3);
                $wastageOutputLength = $resultingStatus === 'finished' ? $balanceAfter : 0;
                $physicalWastageLength = $resultingStatus === 'finished' ? $physicalRemainingLength : 0;
                $stockBalanceAfter = $resultingStatus === 'finished' ? 0 : $balanceAfter;

                $productionList = ProductionList::create([
                    'production_item_master_id' => $production->id,
                    'production_run_id' => $run->id,
                    'machine_id' => $run->machine_id,
                    'produced_by' => $run->production_user_id,
                    'quantity' => $orderQuantity,
                    'actual_quantity' => $productionQuantity,
                    'excess_stock_quantity' => $excessStockQuantity,
                    'real_id' => null,
                    'reel_stock_id' => $stock->id,
                    'core_id' => $core?->id,
                    'core_quantity' => $core ? $coreQuantity : null,
                ]);

                if ($core) {
                    $coreBefore = $core->quantity;
                    $core->update(['quantity' => $coreBefore - $coreQuantity, 'updated_by' => auth()->id()]);
                    CoreStockMovement::create([
                        'core_id' => $core->id, 'transaction_type' => 'production_usage',
                        'quantity_change' => -$coreQuantity, 'quantity_before' => $coreBefore,
                        'quantity_after' => $coreBefore - $coreQuantity,
                        'reference_type' => ProductionItemMaster::class, 'reference_id' => $production->id,
                        'remarks' => "{$coreQuantity} core(s) used for production #{$production->id}.", 'created_by' => auth()->id(),
                    ]);
                }

                ReelStockUsage::create([
                    'production_id' => $production->id,
                    'production_run_id' => $run->id,
                    'production_list_id' => $productionList->id,
                    'reel_stock_id' => $stock->id,
                    'source_status' => $sourceStatus,
                    'calculated_status' => $calculatedStatus,
                    'resulting_status' => $resultingStatus,
                    'status_selection_type' => $statusSelectionType,
                    'source_width' => $sourceWidth,
                    'output_roll_width' => $outputWidth,
                    'roll_length' => $rollLength,
                    'production_quantity' => $productionQuantity,
                    'consumed_length' => $consumedLength,
                    'output_roll_count' => $rollCount,
                    'total_output_length' => $totalOutputLength,
                    'width_waste' => $widthWaste,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $stockBalanceAfter,
                    'remaining_output_length' => $balanceAfter,
                    'physical_remaining_length' => $physicalRemainingLength,
                    'wastage_output_length' => $wastageOutputLength,
                    'physical_wastage_length' => $physicalWastageLength,
                    'machine_id' => $run->machine_id,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                $stock->update([
                    'balance_length' => $stockBalanceAfter,
                    'cut_width' => $outputWidth,
                    'status' => $resultingStatus,
                ]);
                ReelStockMovement::create([
                    'batch_uuid' => (string) Str::uuid(),
                    'reel_stock_id' => $stock->id,
                    'transaction_type' => 'production_usage',
                    'stock_status' => $sourceStatus,
                    'length' => $consumedLength,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $stockBalanceAfter,
                    'reference_type' => ProductionItemMaster::class,
                    'reference_id' => $production->id,
                    'reel_warehouse_id' => $stock->reel_warehouse_id,
                    'remarks' => "{$productionQuantity} roll(s) × {$rollLength} m used at {$outputWidth} mm width. Status set to " . ucfirst($resultingStatus) . '.',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);
                if ($wastageOutputLength > 0) {
                    ReelStockMovement::create([
                        'batch_uuid' => (string) Str::uuid(), 'reel_stock_id' => $stock->id,
                        'transaction_type' => 'production_wastage', 'stock_status' => 'finished',
                        'length' => $wastageOutputLength, 'balance_before' => $balanceAfter, 'balance_after' => 0,
                        'reference_type' => ProductionItemMaster::class, 'reference_id' => $production->id,
                        'reel_warehouse_id' => $stock->reel_warehouse_id,
                        'remarks' => "Finished reel: {$wastageOutputLength} m output length ({$physicalWastageLength} m actual physical length) recorded as wastage.",
                        'created_by' => auth()->id(), 'created_at' => now(),
                    ]);
                }

                $run->update([
                    'production_quantity' => $productionQuantity, 'core_quantity' => $coreQuantity,
                    'status' => 'finished', 'active_key' => null,
                    'finished_at' => now(), 'finished_by' => auth()->id(),
                ]);
                $production->assigned_production_user_id = $run->production_user_id;
                $production->assigned_machine_id = $run->machine_id;
                $production->production_status = $newTotal == (float) $production->requested_qty ? 'Completed' : 'Partial';
                $totalPackedQty = (float) $production->packingLists()->sum('quantity');
                if ($newTotal == (float) $production->requested_qty) {
                    $production->status = $totalPackedQty == $newTotal ? 'Completed' : 'Packing Pending';
                } else {
                    $production->status = 'Partial';
                }
                $production->save();

                if ($excessStockQuantity > 0) {
                    ItemTransaction::where('item_id', $production->item_id)->increment('quantity', $excessStockQuantity);
                    ItemTransaction::where('item_id', $production->item_id)->increment('avaquantity', $excessStockQuantity);
                    $generalQuantity = ItemGeneralQuantity::firstOrCreate(
                        ['item_id' => $production->item_id, 'warehouse_id' => 1],
                        ['quantity' => 0, 'avaquantity' => 0]
                    );
                    $generalQuantity->increment('quantity', $excessStockQuantity);
                    $generalQuantity->increment('avaquantity', $excessStockQuantity);
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Production updated successfully. Reel, core, wastage, and excess stock were recorded.',
                'redirect' => route('item.production.edit', ['id' => $request->production_id]),
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while saving production.',
            ], 500);
        }
    }

    public function assign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'production_id' => 'required|exists:production_item_masters,id',
            'assigned_machine' => 'required|exists:machines,id',
            'assigned_production_user' => 'required|exists:employees,id',
            'assigned_packing_user' => 'required|exists:employees,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $productionItemMaster = ProductionItemMaster::find($request->production_id);
            $productionItemMaster->assigned_machine_id = $request->assigned_machine;
            $productionItemMaster->assigned_production_user_id = $request->assigned_production_user;
            $productionItemMaster->assigned_packing_user_id = $request->assigned_packing_user;
            $productionItemMaster->status = 'Pending'; // Change status to Pending
            $productionItemMaster->save();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Machine and Employees Assigned Successfully.',
                'redirect' => route('item.production.edit', ['id' => $request->production_id])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while saving production.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //   public function store(Request $request)
    // {
    //     try {
    //         DB::beginTransaction();

    //         if ($request->operation == 'save') {
    //             $request->validate([
    //                 'item_id' => 'required|exists:items,id',
    //                 'status' => 'required|string',
    //                 'machines' => 'required|integer',
    //                 'requested_qty' => 'required|integer|min:1',
    //                 'entered_qty' => 'nullable|integer|min:0',
    //                 'remarks' => 'nullable|string',
    //                 'requested_by' => 'required|exists:users,id',
    //                 'approved_by' => 'required|exists:users,id',
    //                 'operation' => 'required',
    //                 'due_date' => 'required'
    //             ]);

    //             $productionItemMaster = ProductionItemMaster::create([
    //                 'remarks' => $request->remarks,
    //                 'production_type' => 'Stock',
    //                 'status' => $request->status,
    //                 'requested_by' => $request->requested_by,
    //                 'entered_qty' => $request->requested_qty,
    //                 'approved_by' => $request->approved_by,
    //                 'item_id' => $request->item_id,
    //                 'machine_id' => $request->machines,
    //                 'requested_qty' => $request->requested_qty,
    //                 'due_date' => $request->due_date,

    //             ]);

    //             // Update item_transactions
    //             ItemTransaction::where('item_id', $request->item_id)
    //                 ->increment('quantity', $request->requested_qty);

    //             // Update item_general_quantities
    //             ItemGeneralQuantity::updateOrCreate(
    //                 [
    //                     'item_id' => $request->item_id,
    //                     'warehouse_id' => 1,
    //                 ],
    //                 [
    //                     'quantity' => DB::raw("quantity + {$request->requested_qty}"),
    //                 ]
    //             );

    //         } else {
    //             $productionItemMaster = ProductionItemMaster::findOrFail($request->production_id);
    //             $purchaseOrderId = $productionItemMaster->purchase_order_id;
    //             $enteredQty = $request->entered_qty;
    //             $remainingQty = $request->remaining_qty;
    //             $approved_qty = $request->requested_qty - $remainingQty;
    //             $total_qty_entered = $approved_qty + $enteredQty;
    //             $balanceQty = $request->requested_qty - $total_qty_entered;

    //             if ($total_qty_entered >= $request->requested_qty) {
    //                 $status = 'Completed';
    //             } elseif ($enteredQty == 0) {
    //                 $status = 'Pending';
    //             } else {
    //                 $status = 'Partial';
    //             }

    //             $productionItemMaster->update([
    //                 'status' => $status,
    //                 'remarks' => $request->remarks,
    //                 'requested_by' => $request->requested_by,
    //                 'entered_qty' => $total_qty_entered,
    //                 'approved_by' => $request->approved_by,
    //                 'item_id' => $request->item_id,
    //                 'real_number' => $request->real_number,
    //                 'packed_by' => $request->packed_by,
    //                 'machine_id' => $request->machines,
    //                 'remaining_qty' => $balanceQty,
    //             ]);

    //             // Update item transactions and general quantities
    //             if ($enteredQty > 0 && $request->requested_qty < $enteredQty) {
    //                 $extraQty = $enteredQty - $request->requested_qty;
    //                 ItemTransaction::updateOrCreate(
    //                     ['item_id' => $request->item_id],
    //                     ['quantity' => DB::raw("quantity + $extraQty")]
    //                 );

    //                 ItemGeneralQuantity::updateOrCreate(
    //                     [
    //                         'item_id' => $request->item_id,
    //                         'warehouse_id' => 1,
    //                     ],
    //                     [
    //                         'quantity' => DB::raw("quantity + $extraQty"),
    //                     ]
    //                 );
    //             }

    //             // Update Purchase Order Item if production completed
    //             if ($purchaseOrderId && $status == 'Completed') {
    //                 PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)
    //                     ->where('product_id', $request->item_id) // Correct column
    //                     ->update([
    //                         'status' => 'Ready to Dispatch',
    //                         'updated_by' => auth()->id(),
    //                     ]);

    //                 // Check if all items of the Purchase Order are now 'Ready to Dispatch'
    //                 $pendingItemsCount = PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)
    //                     ->where('status', '!=', 'Ready to Dispatch')
    //                     ->count();

    //                 if ($pendingItemsCount === 0) {
    //                     PurchaseOrderMaster::where('id', $purchaseOrderId)
    //                         ->update([
    //                             'purchase_order_status' => 'Dispatch Pending',
    //                             'updated_by' => auth()->id(),
    //                         ]);
    //                 }

    //                 $details = PurchaseOrderMaster::find($purchaseOrderId); // <-- find(), not where()
    //                 if ($details) {
    //                     $microtime = microtime(true);
    //                     $milliseconds = sprintf("%03d", ($microtime - floor($microtime)) * 1000);
    //                     $dispatch_order = 'DIS-' . date('dmY-His') . $milliseconds;
    //                     Dispatch::create([
    //                         'purchase_order_id' => $details->id,
    //                         'purchase_order_identifier' => $details->purchase_order_id,
    //                         'customer_id' => $details->customer_id,
    //                         'status' => 'Dispatch Pending',
    //                         'remarks' => 'Auto created from production completion.',
    //                         'mode_of_delivery' => 'Company Vehicle',
    //                         'dispatch_order' => $dispatch_order,
    //                     ]);
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => __('Production record created successfully!'),
    //             'redirect' => route('item.production.index')
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => __('Error creating production record: ') . $e->getMessage()
    //         ], 500);
    //     }
    // }


    // Get unique customers for filter
    public function uniqueCustomers()
    {

        $customers = PurchaseOrderMaster::with('party')
            ->select('customer_id')

            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });

        return response()->json($customers);

    }
 public function uniqueCustomerswithstatus($status)
    {


if ($status == "pending") {
               $ccstatus1 = 'Pending';

                    $customers = PurchaseOrderMaster::with('party','productionmaster')
            ->select('customer_id')
            ->whereHas('productionmaster', function ($query) {
    $query->where('status', 'Pending');
})

            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });
               

            }

            if ($status == "packingpending") {
               $ccstatus1 = 'Packing Pending';
                 $customers = PurchaseOrderMaster::with('party','productionmaster')
            ->select('customer_id')
   ->whereHas('productionmaster', function ($query) {
    $query->where('status', 'Packing Pending');
})
            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });

            }
            if ($status == "assignpending") {
              $ccstatus1 = 'Assigning Pending';
                 $customers = PurchaseOrderMaster::with('party','productionmaster')
            ->select('customer_id')
   ->whereHas('productionmaster', function ($query) {
    $query->where('status', 'Assigning Pending');
})
            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });

            }
            if ($status == "partial") {
                $ccstatus1 = 'Partial';
                $ccstatus2 = 'Progress';


                $customers = PurchaseOrderMaster::with('party','productionmaster')
            ->select('customer_id')
 ->whereHas('productionmaster', function ($query) {
    $query->whereIn('status', ['Partial', 'Progress']);
})
            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });

            }
            if ($status == "inprogress") {
                $customers = PurchaseOrderMaster::with('party', 'productionmaster')
                    ->select('customer_id')
                    ->whereHas('productionmaster', fn ($query) => $query->where('status', 'In Progress'))
                    ->distinct()->get()->map(fn ($item) => [
                        'id' => $item->customer_id,
                        'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown',
                    ]);
            }
            if ($status == "completed") {
                $ccstatus1 = 'Completed';

                 $customers = PurchaseOrderMaster::with('party','productionmaster')
            ->select('customer_id')
 ->whereHas('productionmaster', function ($query) {
    $query->where('status', 'Completed');
})
            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });

            }



        return response()->json($customers);

    }












    /**
     * Datatable
     * */
    public function datatableList(Request $request)
    {
        // Load the 'party' relationship eagerly by default
        // $query = PurchaseOrderMaster::with('party');

        //  $productionLists = ProductionItemMaster::with('item','requestedBy','approvedBy','purchaseOrder')->get();
        $query = ProductionItemMaster::with(
            'item',
            'item.brand',
            'item.category',
            'requestedBy',
            'approvedBy',
            'purchaseOrder',
            'purchaseOrder.party',
            'productionLists',
            'packingLists'
        );


        if ($request->filled('customer_id')) {

            $query->whereHas('purchaseOrder.party', function ($q) use ($request) {
                $q->where('id', $request->customer_id);
            });
        }
          if ($request->filled('product_name')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('id', $request->product_name);
                                //$q->where('name', 'like', "%{$request->product_name}%");

            });
        }

        if ($request->filled('brand_name')) {
            $query->whereHas('item.brand', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->brand_name}%");
            });
        }


        if ($request->filled('category_name')) {
            $query->whereHas('item.category', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->category_name}%");
            });
        }
       if ($request->filled('duedate')) {
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $duedateInput = $request->duedate;

                // Check if it's a range (expects "DD-MM-YYYY - DD-MM-YYYY")
                if (strpos($duedateInput, '-') !== false) {
                    [$start, $end] = explode(' - ', $duedateInput);

                    // Convert to Y-m-d format
                    $startDate = Carbon::createFromFormat('d-m-Y', trim($start))->startOfDay();
                    $endDate   = Carbon::createFromFormat('d-m-Y', trim($end))->endOfDay();

                    $q->whereBetween('due_date', [$startDate, $endDate]);
                } else {
                    // Single date fallback
                    $date = Carbon::createFromFormat('d-m-Y', $duedateInput);
                    $q->whereDate('due_date', $date->format('Y-m-d'));
                }
            });
        }
        if ($request->filled('gapdate')) {
            //echo "1";
            //echo $request->podate;
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $gapdate = $request->gapdate;
                $days = (int) preg_replace('/[^0-9]/', '', $gapdate);
                $date = today()->subDays($days);
                $q->whereDate('po_date', $date->format('Y-m-d'));



            });


        }
        if ($request->filled('product_order_status')) {
            $query->where('status', $request->product_order_status);
        }





        $query->orderBy('id', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('customer', function ($row) {
                if ($row->production_type === 'Purchaseorder' && $row->purchaseOrder && $row->purchaseOrder->party) {

                    $data = $row->purchaseOrder->party->first_name;

                    $editUrl = route('item.production.edit', ['id' => $row->id]);

                    return '<a href="' . $editUrl . '" class="text-dark">' . $data . '</a>';

                } else {
                    $data = $row->production_type;
                    $editUrl = route('item.production.edit', ['id' => $row->id]);


                    return '<span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-primary text-white"><a href="' . $editUrl . '" class="text-dark">' . $data . '</a></span>';

                }
            })


            ->editColumn('work_order', function ($row) {
                if ($row->purchaseOrder) {
                    $data = $row->purchaseOrder->purchase_order_id;
                    $editUrl = route('item.production.edit', ['id' => $row->id]);

                    return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';
                } else {

                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }
            })
            ->editColumn('product_name', function ($row) {
                 $data = $row->item->name ?? 'Nill';
                return  $data;
            })
             ->addColumn('assigned_user', function ($row) {

                return $row->assignedProductionUser->full_name ?? '-';
            })
            ->addColumn('assigned_machine', function ($row) {

                return $row->assignedMachine->machine_name ?? '-';
            })

            ->editColumn('brand', function ($row) {
                if ($row->purchaseOrder) {
                    $data = $row->item->brand->name ?? '';
                    $editUrl = route('item.production.edit', ['id' => $row->id]);

                    return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';
                } else {

                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }
            })

            ->editColumn('category', function ($row) {
                if ($row->purchaseOrder) {
                    $data = $row->item->category->name ?? '';

                    return $data;
                } else {

                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }
            })
            ->editColumn('requested_qty', function ($row) {

                $data = $row->requested_qty;
                $editUrl = route('item.production.edit', ['id' => $row->id]);

                return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';

            })
            ->editColumn('production_remaining_qty', function ($row) {

                $data = $row->requested_qty - $row->productionLists()->sum('quantity');
                $editUrl = route('item.production.edit', ['id' => $row->id]);

                return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';

            })
            ->editColumn('packing_remaining_qty', function ($row) {

                $data = $row->requested_qty - $row->packingLists()->sum('quantity');
                $editUrl = route('item.production.edit', ['id' => $row->id]);

                return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';

            })



            ->editColumn('due_date', function ($row) {

                if ($row->purchaseOrder && $row->purchaseOrder->due_date) {
                    $data = ($row->purchaseOrder->due_date)?->format('d-m-Y');
                    ;
                    $editUrl = route('item.production.edit', ['id' => $row->id]);

                    return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';
                } else {

                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }


            })
            ->editColumn('ageing', function ($row) {
                $createdAt = $row->purchaseOrder->po_date ?? '';

                if ($createdAt) {
                    $gap = today()->diffInDays($createdAt) . ' days';
                    return '<span class="text-success">' . $gap . '</span>';

                } else {
                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }

            })
            ->editColumn('status', function ($row) {
                $statusClass = match ($row->status) {
                    'Pending' => 'bg-warning text-dark',
                    'Assigning Pending' => 'bg-warning text-dark',
                    'Packing Pending' => 'bg-warning text-dark',
                    'Completed' => 'bg-success text-white',
                    'Partial' => 'bg-info text-dark',
                    'Progress' => 'bg-primary text-white',
                    'In Progress' => 'bg-primary text-white',
                    'Cancelled' => 'bg-danger text-white',
                    default => 'bg-secondary text-white',
                };
                return '<span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm ' . $statusClass . '">' . $row->status . '</span>';


            })
           ->editColumn('action', function ($row) {
                $editUrl = route('item.production.edit', ['id' => $row->id]);
                $editProductUrl = route('item.editProduct', ['id' => $row->id]);
                $buttons = '';
                $buttons = '<a class="btn btn-success btn-sm" href="' . $editUrl . '">Track</a>';
                if (auth()->user()->can('production.edit')) {
                    $buttons .= '<a class="btn btn-primary btn-sm ms-2" href="' . $editProductUrl . '">Edit</a>';
                }
                return $buttons;
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('production_item_masters.status', 'like', "%{$keyword}%");
            })

            // Add this filter for due_date
            ->filterColumn('due_date', function ($query, $keyword) {
                try {
                    // Try to parse the search term in d-m-Y format
                    $date = Carbon::createFromFormat('d-m-Y', $keyword);
                    $query->whereDate('due_date', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    // If parsing fails, do a simple like search
                    $query->where('due_date', 'like', "%{$keyword}%");
                }
            })

            // Add custom filter for CreatedGap only when specifically searching this column
            ->filterColumn('ageing', function ($query, $keyword) {
                if (strpos($keyword, 'days') !== false) {
                    $days = (int) preg_replace('/[^0-9]/', '', $keyword);
                    $date = today()->subDays($days);
                    $query->whereDate('po_date', $date->format('Y-m-d'));
                }

            })


            ->rawColumns(['brand', 'category', 'requested_qty', 'production_remaining_qty', 'packing_remaining_qty', 'due_date', 'ageing', 'customer', 'work_order', 'action', 'ageing', 'status'])
            ->make(true);
    }

    public function FilterList($status): View
    {



        return view('production.filterlist', compact('status'));

    }

    /* filter data table*/
    public function datatableFilterList(Request $request)
    {
        // Load the 'party' relationship eagerly by default
        // $query = PurchaseOrderMaster::with('party');

        //  $productionLists = ProductionItemMaster::with('item','requestedBy','approvedBy','purchaseOrder')->get();
        $query = ProductionItemMaster::with(
            'item',
            'item.brand',
            'item.category',
            'requestedBy',
            'approvedBy',
            'purchaseOrder',
            'purchaseOrder.party',
            'productionLists',
            'packingLists',
            'activeRun.machine'
        );

        $ccstatus = trim((string) $request->cstatus);
        if ($request->filled('cstatus')) {

            if ($ccstatus == "pending") {
               $ccstatus1 = 'Pending';
                /* $ccstatus2 = 'Packing Pending';

                $query->whereIn('status', array($ccstatus1, $ccstatus2));*/

                $query->where('status', $ccstatus1);

            }

            if ($ccstatus == "packingpending") {
               $ccstatus1 = 'Packing Pending';
                $query->where('status', $ccstatus1);

            }
            if ($ccstatus == "assignpending") {
               $ccstatus1 = 'Assigning Pending';
                $query->where('status', $ccstatus1);

            }
            if ($ccstatus == "partial") {
                $ccstatus1 = 'Partial';
                $ccstatus2 = 'Progress';

                $query->whereIn('status', [$ccstatus1, $ccstatus2]);

            }
            if ($ccstatus == "inprogress") {
                $query->where('status', 'In Progress');
            }
            if ($ccstatus == "completed") {
                $ccstatus1 = 'Completed';

                $query->where('status', $ccstatus1);

            }

        }




        if ($request->filled('customer_id')) {

            $query->whereHas('purchaseOrder.party', function ($q) use ($request) {
                $q->where('id', $request->customer_id);
            });
        }
 if ($request->filled('product_name')) {
            $query->whereHas('item', function ($q) use ($request) {
                //$q->where('name', 'like', "%{$request->product_name}%");
                    $q->where('id', $request->product_name);
                                //$q->where('name', 'like', "%{$request->product_name}%");
            });
        }

        if ($request->filled('brand_name')) {
            $query->whereHas('item.brand', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->brand_name}%");
            });
        }


        if ($request->filled('category_name')) {
            $query->whereHas('item.category', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->category_name}%");
            });
        }
       if ($request->filled('duedate')) {
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $duedateInput = $request->duedate;

                // Check if it's a range (expects "DD-MM-YYYY - DD-MM-YYYY")
                if (strpos($duedateInput, '-') !== false) {
                    [$start, $end] = explode(' - ', $duedateInput);

                    // Convert to Y-m-d format
                    $startDate = Carbon::createFromFormat('d-m-Y', trim($start))->startOfDay();
                    $endDate   = Carbon::createFromFormat('d-m-Y', trim($end))->endOfDay();

                    $q->whereBetween('due_date', [$startDate, $endDate]);
                } else {
                    // Single date fallback
                    $date = Carbon::createFromFormat('d-m-Y', $duedateInput);
                    $q->whereDate('due_date', $date->format('Y-m-d'));
                }
            });
        }
        if ($request->filled('gapdate')) {
            //echo "1";
            //echo $request->podate;
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $gapdate = $request->gapdate;
                $days = (int) preg_replace('/[^0-9]/', '', $gapdate);
                $date = today()->subDays($days);
                $q->whereDate('po_date', $date->format('Y-m-d'));



            });


        }
        if ($request->filled('product_order_status')) {
            $query->where('status', $request->product_order_status);
        }





        $query->orderBy('id', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('customer', function ($row) {
                if ($row->production_type === 'Purchaseorder' && $row->purchaseOrder && $row->purchaseOrder->party) {

                    $data = $row->purchaseOrder->party->first_name;

                    $editUrl = route('item.production.edit', ['id' => $row->id]);

                    return '<a href="' . $editUrl . '" class="text-dark">' . $data . '</a>';

                } else {
                    $data = $row->production_type;
                    $editUrl = route('item.production.edit', ['id' => $row->id]);


                    return '<span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-primary text-white"><a href="' . $editUrl . '" class="text-dark">' . $data . '</a></span>';

                }
            })


            ->editColumn('work_order', function ($row) {
                if ($row->purchaseOrder) {
                    $data = $row->purchaseOrder->purchase_order_id;
                    $editUrl = route('item.production.edit', ['id' => $row->id]);

                    return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';
                } else {

                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }
            })
            ->editColumn('product_name', function ($row) {
                return $row->item->name ?? 'N/A';
            })
             ->addColumn('assigned_user', function ($row) {

                return $row->assignedProductionUser->full_name ?? '-';
            })
            ->addColumn('assigned_machine', function ($row) {

                return $row->assignedMachine->machine_name ?? '-';
            })

            ->editColumn('brand', function ($row) {
                if ($row->purchaseOrder) {
                    $data = $row->item->brand->name ?? '';
                    $editUrl = route('item.production.edit', ['id' => $row->id]);

                    return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';
                } else {

                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }
            })

            ->editColumn('category', function ($row) {
                if ($row->purchaseOrder) {
                    $data = $row->item->category->name ?? '';

                    return $data;
                } else {

                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }
            })
            ->editColumn('requested_qty', function ($row) {

                $data = $row->requested_qty;
                $editUrl = route('item.production.edit', ['id' => $row->id]);

                return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';

            })
            ->editColumn('production_remaining_qty', function ($row) {

                $data = $row->requested_qty - $row->productionLists()->sum('quantity');
                $editUrl = route('item.production.edit', ['id' => $row->id]);

                return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';

            })
            ->editColumn('packing_remaining_qty', function ($row) {

                $data = $row->requested_qty - $row->packingLists()->sum('quantity');
                $editUrl = route('item.production.edit', ['id' => $row->id]);

                return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';

            })



            ->editColumn('due_date', function ($row) {

                if ($row->purchaseOrder && $row->purchaseOrder->due_date) {
                    $data = ($row->purchaseOrder->due_date)?->format('d-m-Y');
                    ;
                    $editUrl = route('item.production.edit', ['id' => $row->id]);

                    return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';
                } else {

                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }


            })
            ->editColumn('ageing', function ($row) {
                $createdAt = $row->purchaseOrder->po_date ?? '';

                if ($createdAt) {
                    $gap = today()->diffInDays($createdAt) . ' days';
                    return '<span class="text-success">' . $gap . '</span>';

                } else {
                    $data = "Not Available";

                    return ' <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm bg-info text-dark">' . $data . '</span>';
                }

            })
            ->editColumn('status', function ($row) {
                $statusClass = match ($row->status) {
                    'Pending' => 'bg-warning text-dark',
                    'Packing Pending' => 'bg-warning text-dark',
                    'Completed' => 'bg-success text-white',
                    'Partial' => 'bg-info text-dark',
                    'Progress' => 'bg-primary text-white',
                    'In Progress' => 'bg-primary text-white',
                    'Cancelled' => 'bg-danger text-white',
                    default => 'bg-secondary text-white',
                };
                return '<span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm ' . $statusClass . '">' . $row->status . '</span>';


            })
            ->addColumn('current_machine', function ($row) {
                $machineName = $row->activeRun?->machine?->machine_name;

                return $machineName
                    ? '<span class="badge bg-primary text-white">' . e($machineName) . '</span>'
                    : '<span class="text-muted">Not Available</span>';
            })
             ->editColumn('action', function ($row) {
                 $editUrl = route('item.production.edit', ['id' => $row->id]);
                $editProductUrl = route('item.editProduct', ['id' => $row->id]);
                $buttons = '';
                $buttons = '<a class="btn btn-success btn-sm" href="' . $editUrl . '">Track</a>';
                if (auth()->user()->can('production.edit')) {
                    $buttons .= '<a class="btn btn-primary btn-sm ms-2" href="' . $editProductUrl . '">Edit</a>';
                }
                return $buttons;
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('production_item_masters.status', 'like', "%{$keyword}%");
            })

            // Add this filter for due_date
            ->filterColumn('due_date', function ($query, $keyword) {
                try {
                    // Try to parse the search term in d-m-Y format
                    $date = Carbon::createFromFormat('d-m-Y', $keyword);
                    $query->whereDate('due_date', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    // If parsing fails, do a simple like search
                    $query->where('due_date', 'like', "%{$keyword}%");
                }
            })

            // Add custom filter for CreatedGap only when specifically searching this column
            ->filterColumn('ageing', function ($query, $keyword) {
                if (strpos($keyword, 'days') !== false) {
                    $days = (int) preg_replace('/[^0-9]/', '', $keyword);
                    $date = today()->subDays($days);
                    $query->whereDate('po_date', $date->format('Y-m-d'));
                }

            })


            ->rawColumns(['brand', 'category', 'requested_qty', 'production_remaining_qty', 'packing_remaining_qty', 'due_date', 'ageing', 'customer', 'work_order', 'action', 'status', 'current_machine'])
            ->make(true);
    }


    public function printView($id)
    {
        $productionItemMaster = ProductionItemMaster::with([
            'item',
            'item.brand',
            'purchaseOrder.party',
            'assignedMachine',
            'assignedProductionUser',
            'assignedPackingUser',
            'productionLists',
            'packingLists'
        ])->findOrFail($id);

        return view('production.print-content', compact('productionItemMaster'))->render(); 
    }
    // Get unique PRODUCTS for filter
    public function uniqueProducts()
    {
        $customers = Item::with('itemTransaction')
            ->select('*')
            ->distinct('id')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name
                ];
            });

        return response()->json($customers);

    }
    
      public function uniqueProductsWithStatus($status)
    {
      


if ($status == "pending") {
               $ccstatus1 = 'Pending';

                    $customers =Item::with('itemTransaction','productionmaster')
            ->select('*')
            ->whereHas('productionmaster', function ($query) {
    $query->where('status', 'Pending');
})

            ->distinct('id')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name
                ];
            });
               

            }

            if ($status == "packingpending") {
               $ccstatus1 = 'Packing Pending';
                    $customers =Item::with('itemTransaction','productionmaster')
            ->select('*')
   ->whereHas('productionmaster', function ($query) {
    $query->where('status', 'Packing Pending');
})
            ->distinct('id')
            ->get()
            ->map(function ($row) {
                return [
                   'id' => $row->id,
                    'name' => $row->name
                ];
            });

            }
            if ($status == "assignpending") {
              $ccstatus1 = 'Assigning Pending';
                    $customers =Item::with('itemTransaction','productionmaster')
            ->select('*')
   ->whereHas('productionmaster', function ($query) {
    $query->where('status', 'Assigning Pending');
})
            ->distinct('id')
            ->get()
            ->map(function ($row) {
                return [
                  'id' => $row->id,
                    'name' => $row->name
                ];
            });

            }
            if ($status == "partial") {
                $ccstatus1 = 'Partial';
                $ccstatus2 = 'Progress';


                    $customers =Item::with('itemTransaction','productionmaster')
            ->select('*')
   ->whereHas('productionmaster', function ($query) {
    $query->whereIn('status', ['Partial', 'Progress']);
})
            ->distinct('id')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name
                ];
            });

            }
            if ($status == "inprogress") {
                $customers = Item::with('itemTransaction', 'productionmaster')
                    ->select('*')
                    ->whereHas('productionmaster', fn ($query) => $query->where('status', 'In Progress'))
                    ->distinct('id')->get()->map(fn ($row) => ['id' => $row->id, 'name' => $row->name]);
            }
            if ($status == "completed") {
                $ccstatus1 = 'Completed';

                    $customers =Item::with('itemTransaction','productionmaster')
            ->select('*')
 ->whereHas('productionmaster', function ($query) {
    $query->where('status', 'Completed');
})
            ->distinct('id')
            ->get()
            ->map(function ($row) {
                return [
                     'id' => $row->id,
                    'name' => $row->name
                ];
            });

            }


        return response()->json($customers);


    }
     public function editProduct($id)
    {
        $user = auth()->user();
        $product = ProductionItemMaster::with(['item', 'item.brand', 'requestedBy', 'approvedBy', 'purchaseOrder.party', 'assignedMachine', 'assignedProductionUser', 'assignedPackingUser'])->findOrFail($id);

        $items = Item::with('category', 'brand')->get();
        $machines = Machine::select('id', 'machine_name')->get();
        $employees = Employee::select(
            'id',
            'full_name',
        )->get();
        // dd($productionItemMaster);
        return view('production.edit-product', [
            'user' => $user,
            'product' => $product,
            'items' => $items,
            'machines' => $machines,
            'employees' => $employees,
        ]);

    }

    public function updateProduct(ProductUpdateRequest $request, $id)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {

            $product = ProductionItemMaster::findOrFail($id);
            $existingItemId = $product->item_id;

            $product->item_id = $validated['product'];
            $product->requested_qty = $validated['requested_quantity'];
            $product->assigned_machine_id = $validated['assigned_machine'] ?? null;
            $product->assigned_production_user_id = $validated['assigned_production_user'] ?? null;
            $product->assigned_packing_user_id = $validated['assigned_packing_user'] ?? null;
            $product->production_remarks = $validated['production_remark'] ?? null;
            $product->packing_remarks = $validated['packing_remark'] ?? null;
            $product->dispatch_remarks = $validated['dispatch_remark'] ?? null;
            $product->save();

            if ($product->purchaseOrder) {
                $purchaseOrderItems = $product->purchaseOrder->items()
                    ->where('product_id', $existingItemId)
                    ->get();

                foreach ($purchaseOrderItems as $poItem) {
                    $poItem->update([
                        'product_remarks' => $validated['production_remark'] ?? null,
                        'paking_remarks' => $validated['packing_remark'] ?? null,
                        'dispatch_remarks' => $validated['dispatch_remark'] ?? null,
                        'product_id' => $validated['product'],
                        'quantity' => $validated['requested_quantity'],
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Product order updated successfully.',
                    'redirect_url' => route('item.production.index'),
                ]);
            }
            return redirect()->route('item.production.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating Product', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error updating Product: ' . $e->getMessage());
        }
    }
    
    public function uniqueBrands()
    {
        return Brand::select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function uniqueCategories()
    {
        return ItemCategory::select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    function getAjaxReal(){
        $real_id = request('real_id');
                $realrecord = Real::with(['brandRelation', 'categoryRelation','stocksRelation'])->findOrFail($real_id);

    
        return json_encode($realrecord);
    }

}
