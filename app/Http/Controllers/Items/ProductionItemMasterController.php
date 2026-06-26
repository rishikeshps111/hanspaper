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
        $productionItemMaster = ProductionItemMaster::with(['item', 'item.brand', 'requestedBy', 'approvedBy', 'purchaseOrder.party', 'assignedMachine', 'assignedProductionUser', 'assignedPackingUser'])->findOrFail($id);
        //$reals = Real::with('brandRelation', 'categoryRelation')->where('is_active', 1)->where('current_status','!=','full')->orWhereNull('current_status')->get();
        $reals = Real::with('brandRelation', 'categoryRelation')->where('is_active', 1)->get();
       // dd($reals);
        return view('production.edit', [
            'user' => $user,
            'productionItemMaster' => $productionItemMaster,
            'reals' => $reals,
        ]);

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $productionItemMaster = ProductionItemMaster::findOrFail($request->production_id);

            // Get fresh totals
            $totalProducedQty = $productionItemMaster->productionLists()->sum('quantity');
            $existingPackedQty = $productionItemMaster->packingLists()->sum('quantity');
            $newPackedQty = $existingPackedQty + $request->packed_qty;

            // Prevent overpacking
            if ($newPackedQty > $totalProducedQty) {
                return response()->json([
                    'status' => false,
                    'message' => 'Packing quantity exceeds total produced quantity.',
                ], 400);
            }

            // Save new packing record
            $record = PackingList::create([
                'production_item_master_id' => $productionItemMaster->id,
                'packed_by' => $request->packed_by,
                'quantity' => $request->packed_qty,
            ]);
            
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
    $query->whereIn('status', array('Partial', 'Progress'));
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
        $query = ProductionItemMaster::with('item', 'item.brand', 'item.category', 'requestedBy', 'approvedBy', 'purchaseOrder', 'purchaseOrder.party', 'productionLists', 'packingLists');


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
        $query = ProductionItemMaster::with('item', 'item.brand', 'item.category', 'requestedBy', 'approvedBy', 'purchaseOrder', 'purchaseOrder.party', 'productionLists', 'packingLists');

        $ccstatus = $request->cstatus;
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

                $query->whereIn('status', array($ccstatus1, $ccstatus2));

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
    $query->whereIn('status', array('Partial', 'Progress'));
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
