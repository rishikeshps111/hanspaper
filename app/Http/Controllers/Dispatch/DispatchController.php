<?php

namespace App\Http\Controllers\Dispatch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Dispatch\Dispatch;
use App\Models\dispatchmodify;
use App\Models\PurchaseOrders\PurchaseOrderMaster;
use App\Models\PurchaseOrders\PurchaseOrderItem;
use App\Models\Items\ItemTransaction;
use App\Models\Items\ItemGeneralQuantity;
use App\Models\Items\ProductionItemMaster;
use App\Models\ItemReturn;
use Illuminate\Database\QueryException;

use Carbon\Carbon;

class DispatchController extends Controller
{
    public function index(): View
    {
        return view('dispatches.list');
    }
     // Get unique customers for filter
    public function uniqueCustomers()
    {
        $customers = PurchaseOrderMaster::with('party')
            ->select('customer_id')
            
            ->distinct()
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });
        
        return response()->json($customers);
    }
    public function uniqueCustomerswithstatus($status)
    {
if($status=="pending")
    {
        $ccstatus1='Pending';
        $ccstatus2='Dispatch Pending';


              $customers = PurchaseOrderMaster::with('party','dispatchd')
            ->select('customer_id')
              ->whereHas('dispatchd', function ($query) {
          $query->whereIn('status',  array('Pending' ,'Dispatch Pending'));
})
            ->distinct()
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });

    }
    if($status=="partial")
    {
       $ccstatus1='Dispatched';

              $customers = PurchaseOrderMaster::with('party','dispatchd')
            ->select('customer_id')
              ->whereHas('dispatchd', function ($query) {
          $query->where('status',  'Dispatched' );
})
            ->distinct()
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });

    }
     if($status=="completed")
    {
        $ccstatus1='Completed';

       $customers = PurchaseOrderMaster::with('party','dispatchd')
            ->select('customer_id')
              ->whereHas('dispatchd', function ($query) {
          $query->where('status',  'Completed' );
})
            ->distinct()
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->customer_id,
                    'name' => $item->party ? $item->party->first_name . ' ' . $item->party->last_name : 'Unknown'
                ];
            });
    }




      
        
        return response()->json($customers);
    }

// public function datatableList(Request $request)
// {
//     $query = Dispatch::with(['purchaseOrder.items.product', 'customer','ProductionItemMaster']);


//     if ($request->filled('customer_id')) {

//          $query->whereHas('customer', function ($q) use ($request) {
//             $q->where('id', $request->customer_id);
//         });
//     }


//  if ($request->filled('mode_name')) {
//         $query->where('mode_of_delivery','like', "%{$request->mode_name}%"  );
//     }

//       if ($request->filled('duedate')) {
//         //echo "1";
//         // $request->duedate;

      


//           $query->whereHas('purchaseOrder', function ($q) use ($request) {
//                      $duedate1= date("Y-m-d", strtotime($request->duedate));

//                 $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$duedate1}%"]);
//             });
//     }
    

//     if ($request->filled('gapdate')) {
//         //echo "1";
//         //echo $request->podate;
//         $query->whereHas('purchaseOrder', function ($q) use ($request) {
//                     $gapdate= $request->gapdate;
//          $days = (int) preg_replace('/[^0-9]/', '', $gapdate);
//                 $date = today()->subDays($days);
//              $q->whereDate('created_at', $date->format('Y-m-d'));



//         });


//     }

// if ($request->filled('dispatch_order_status')) {
//         $query->where('status', $request->dispatch_order_status);
//     }
    


//     // Conditional Filters
//     if ($request->filled('party_id')) {
//         $query->whereHas('customer', function ($q) use ($request) {
//             $q->where('first_name', 'like', '%' . $request->party_id . '%')
//               ->orWhere('last_name', 'like', '%' . $request->party_id . '%');
//         });
//     }

//     if ($request->filled('user_id')) {
//         $query->where('user_id', $request->user_id);
//     }

//     if ($request->filled('from_date') && $request->filled('to_date')) {
//         $query->whereBetween('created_at', [
//             Carbon::parse($request->from_date)->startOfDay(),
//             Carbon::parse($request->to_date)->endOfDay()
//         ]);
//     }

//     // Order by latest
//     $query->orderByDesc('id');

//     return DataTables::of($query)
//         ->addIndexColumn()

//         // Searchable Columns
//         ->filterColumn('purchase_order_identifier', function ($query, $keyword) {
//             $query->where(function($q) use ($keyword) {
//                 $q->where('purchase_order_identifier', 'like', "%{$keyword}%")
//                   ->orWhere('purchase_order_id', 'like', "%{$keyword}%");
//             });
//         })

//         ->filterColumn('mode_of_delivery', function ($query, $keyword) {
//             $query->where('mode_of_delivery', 'like', "%{$keyword}%");
//         })

//         ->filterColumn('customer_id', function ($query, $keyword) {
//             $query->whereHas('customer', function ($q) use ($keyword) {
//                 $q->where('first_name', 'like', "%{$keyword}%")
//                   ->orWhere('last_name', 'like', "%{$keyword}%");
//             });
//         })

//         ->filterColumn('created_at', function ($query, $keyword) {
//             $query->whereHas('purchaseOrder', function ($q) use ($keyword) {
//                 $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$keyword}%"]);
//             });
//         })

//         ->filterColumn('CreatedGap', function ($query, $keyword) {
//             if (preg_match('/\d+/', $keyword, $matches)) {
//                 $days = (int) $matches[0];
//                 $targetDate = now()->subDays($days)->toDateString();

//                 $query->whereHas('purchaseOrder', function ($q) use ($targetDate) {
//                     $q->whereDate('created_at', $targetDate);
//                 });
//             }
//         })

//         // Display Columns
//         ->editColumn('purchase_order_identifier', fn($row) => $row->purchase_order_identifier)
//         ->editColumn('mode_of_delivery', fn($row) => $row->mode_of_delivery)
//         ->editColumn('remarks', fn($row) => $row->remarks)

//         ->editColumn('customer_id', function ($row) {
//             $customer = $row->customer;
//             return $customer ? "{$customer->first_name} {$customer->last_name}" : 'No customer';
//         })
//         ->editColumn('product_info', function ($row) {

//       $html="<table id='pinfo'><thead><th>Product Name</th><th>Total Quantity</th><th>From Production</th><th>From Stock</th></thead>";

//   $reqqunatity=0;


// $purchase_order_item_all = PurchaseOrderItem::where('dispatches_id', $row->id)->get();

//             foreach($purchase_order_item_all as $orderItem)
//             {

//   //$purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id',$orderItem->product->id)->select('requested_qty')->get();
// //echo $row->id;
//                 //$purchase_order_item = PurchaseOrderMaster::where('dispatches_id', $row->id)->WhereNotNull('dispatches_id')->where('item_id',$orderItem->product->id)->select('requested_qty')->get();
// //$purchase_order_item = PurchaseOrderItem::where('dispatches_id', $row->id)->where('product_id',$orderItem->product->id)->select('quantity')->get();

// $purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id',$orderItem->product->id)->select('requested_qty')->get();

//                   $c=sizeof($purchase_order_item);
//                     if($c<=1)                
                
//                 {        foreach($purchase_order_item as $purchase_order_item)
//                           {

//                           $reqqunatity= $purchase_order_item['requested_qty'];


//                           }
//                           $stock_qty=$orderItem->quantity-$reqqunatity;

         
//                       if(!empty($purchase_order_item['requested_qty']))
//                       {
//                     $html.="<tr><td>".$orderItem->product->name."</td><td>".$orderItem->quantity."</td><td>".$reqqunatity."</td><td>".$stock_qty."</td></tr>";
//                       }
//                       else
//                       {
//                         $reqqunatity=0;
//                          $stock_qty=$orderItem->quantity-$reqqunatity;
//                           $html.="<tr><td>".$orderItem->product->name."</td><td>".$orderItem->quantity."</td><td>".$reqqunatity."</td><td>".$stock_qty."</td></tr>";
//                       }
                

//                 }
//                 else
//                 {

//              $purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id',$orderItem->product->id)->select('requested_qty')->get();              

//                 foreach($purchase_order_item as $purchase_order_item)
//                           {

//                           $reqqunatity= $purchase_order_item['requested_qty'];
//                             $stock_qty=$orderItem->quantity-$reqqunatity;

                           
//                           }

//                  if(empty($purchase_order_item))
//                  {

//                              $html.="<tr><td>".$orderItem->product->name."</td><td>".$orderItem->quantity."</td><td></td><td></td></tr>";
//                          }

//                 }


//             }


                                                   

//             return $html;
//         })
       
 
//         ->editColumn('created_at', function ($row) {
//             return optional($row->purchaseOrder)?->created_at?->format('d-m-Y') ?? 'N/A';
//         })

//         ->editColumn('CreatedGap', function ($row) {
//             $createdAt = optional($row->purchaseOrder)->created_at;
//             if ($createdAt) {
//                 $gap = now()->diffInDays($createdAt) . ' days';
//                 $class = in_array($row->status, ['Completed', 'Dispatched']) ? 'text-success' : 'text-danger';
//                 if($row->status=="Completed")
//                 {
//             $createdAt = optional($row->purchaseOrder)->created_at;
//             $updated_at = optional($row->purchaseOrder)->updated_at;

//                                     $gap = $updated_at->diffInDays($createdAt) . ' days';

//                   return "<span class=''>{$gap}</span>";

//                 }
//                 else
//                 {


//                     return "<span class='{$class}'>{$gap}</span>";

//                 }
//             }
//             return 'N/A';
//         })

//         ->editColumn('action', function ($row) {
//             $editUrl = route('dispatch.edit', ['id' => $row->id]);
//             return '<a class="dropdown-item1" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> ' . __('app.edit') . '</a>';
//         })

//         ->rawColumns(['action', 'CreatedGap','product_info'])
//         ->make(true);
// }

 public function datatableList(Request $request)
    {
        $query = PurchaseOrderItem::with(['product', 'purchaseOrder.party', 'dispatch', 'returnDetail'])->whereNotNull('dispatches_id')->orderByDesc('id');

        if ($request->filled('customer_id')) {
            $query->whereHas('purchaseOrder.party', function ($q) use ($request) {
                $q->where('id', $request->customer_id);
            });
        }

        if ($request->filled('duedate')) {
            $duedate1 = date("Y-m-d", strtotime($request->duedate));
            $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$duedate1}%"]);
        }

        if ($request->filled('gapdate')) {
            $gapdate = $request->gapdate;
            $days = (int) preg_replace('/[^0-9]/', '', $gapdate);
            $targetDate = now()->subDays($days)->toDateString();
            $query->whereDate('created_at', $targetDate);
        }

        if ($request->filled('dispatch_order_status')) {
            $query->whereHas('dispatch',  function ($q) use ($request) {
                $q->where('status', $request->dispatch_order_status);
            });
        }

        if ($request->filled('return_status')) {
            if ($request->return_status === 'Returned') {
                $query->whereHas('returnDetail');
            } else {
                $query->whereDoesntHave('returnDetail');
            }
        }


        return DataTables::of($query)
            ->addIndexColumn()

            // Display Columns
            ->addColumn('dispatch_order', fn($row) => $row->dispatch->dispatch_order ?? '')
            ->addColumn('purchase_order_identifier', fn($row) => $row->dispatch->purchase_order_identifier ?? '')
            ->addColumn('customer_id', function ($row) {
                $customer = $row->purchaseOrder->party;
                return $customer ? "{$customer->first_name} {$customer->last_name}" : 'No customer';
            })
            ->addColumn('product_info', function ($row) {

                $html = "<table id='pinfo'><thead><th>Product Name</th><th>Total Quantity</th><th>From Production</th><th>From Stock</th></thead>";

                $purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id', $row->product->id)->select('requested_qty')->get();

                $c = sizeof($purchase_order_item);
                $reqqunatity = 0;

                if ($c <= 1) {
                    foreach ($purchase_order_item as $purchase_order_item) {

                        $reqqunatity = $purchase_order_item['requested_qty'];
                    }
                    $stock_qty = $row->quantity - $reqqunatity;


                    if (!empty($purchase_order_item['requested_qty'])) {
                        $html .= "<tr><td>" . $row->product->name . "</td><td>" . $row->quantity . "</td><td>" . $reqqunatity . "</td><td>" . $stock_qty . "</td></tr>";
                    } else {
                        $reqqunatity = 0;
                        $stock_qty = $row->quantity - $reqqunatity;
                        $html .= "<tr><td>" . $row->product->name . "</td><td>" . $row->quantity . "</td><td>" . $reqqunatity . "</td><td>" . $stock_qty . "</td></tr>";
                    }
                } else {

                    $purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id', $row->product->id)->select('requested_qty')->get();

                    foreach ($purchase_order_item as $purchase_order_item) {

                        $reqqunatity = $purchase_order_item['requested_qty'];
                        $stock_qty = $row->quantity - $reqqunatity;
                        $html .= "<tr><td>" . $row->product->name . "</td><td>" . $row->quantity . "</td><td></td><td></td></tr>";
                    }

                    if (empty($purchase_order_item)) {

                        $html .= "<tr><td>" . $row->product->name . "</td><td>" . $row->quantity . "</td><td></td><td></td></tr>";
                    } else {
                        $html .= "<tr><td>" . $row->product->name . "</td><td>" . $row->quantity . "</td><td></td><td></td></tr>";
                    }
                }

                return $html;
            })


            ->editColumn('created_at', function ($row) {
                return optional($row)?->created_at?->format('d-m-Y') ?? 'N/A';
            })

            ->editColumn('CreatedGap', function ($row) {
                $createdAt = optional($row)->created_at;

                if ($createdAt) {
                    $dispatchStatus = optional($row->dispatch)->status;
                    $updatedAt = optional($row)->updated_at;
                    $gap = now()->diffInDays($createdAt) . ' days';
                    $class = in_array($dispatchStatus, ['Completed', 'Dispatched']) ? 'text-success' : 'text-danger';

                    if ($dispatchStatus === "Completed" && $updatedAt) {
                        $gap = $updatedAt->diffInDays($createdAt) . ' days';
                        return "<span class='text-success'>{$gap}</span>";
                    }

                    return "<span class='{$class}'>{$gap}</span>";
                }

                return 'N/A';
            })

            ->editColumn('status', function ($row) {
                $status = optional($row->dispatch)->status;

                if (!$status) {
                    return "<span class='badge bg-secondary'>N/A</span>";
                }

                switch ($status) {
                    case 'Pending':
                        $class = 'bg-warning text-dark';
                        break;
                    case 'Completed':
                        $class = 'bg-success';
                        break;
                    case 'Dispatched':
                        $class = 'bg-primary';
                        break;
                    case 'Dispatch Pending':
                        $class = 'bg-warning text-dark';
                        break;
                    case 'Partial Dispatch':
                        $class = 'bg-warning text-dark';
                        break;
                    default:
                        $class = 'bg-secondary';
                        break;
                }

                return "<span class='badge {$class}'>{$status}</span>";
            })
            ->editColumn('dispatch_id', function ($row) {
                return $row->dispatch->id ?? '';
            })
            ->addColumn('action', function ($row) {
                $editUrl = '';
                $dispatchId = optional($row->dispatch)->id;

                if ($dispatchId) {
                    $editUrl = route('dispatch.edit', ['id' => $dispatchId]);
                }

                $html = '<div class="d-flex gap-2">';
                $html .= '<a class="btn btn-sm btn-primary" href="' . $editUrl . '">
                <i class="bx bx-edit"></i> Edit
              </a>';
                if ($row->returnDetail) {
                    $returnId = $row->returnDetail->id;
                    $html .= '<a class="btn btn-sm btn-info btn-view-return" data-id="' . $returnId . '">
                    <i class="bx bx-show"></i> View Return Details
                  </a>';
                } else {
                    $html .= '<a class="btn btn-sm btn-danger btn-return" data-id="' . $row->id . '">
                    <i class="bx bx-undo"></i> Return
                  </a>';
                }

                $html .= '</div>';

                return $html;
            })
            ->addColumn('return_status', function ($row) {
                return $row->returnDetail
                    ? '<span class="badge bg-danger">Returned</span>'
                    : '<span class="badge bg-success">Not Returned</span>';
            })

            ->rawColumns(['action', 'CreatedGap', 'product_info', 'status', 'return_status'])
            ->make(true);
    }

    
     public function edit($id): View
    {
        $dispatch = Dispatch::with(['purchaseOrder.items.product', 'customer'])->findOrFail($id);
        $mdispatch = dispatchmodify::where('dispatches_id', $id)->get();
        return view('dispatches.edit', compact('dispatch','mdispatch'));
    }

    
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
   DB::enableQueryLog();
           
           
           

/*if (($request->dispatch_status == 'Dispatch Pending')||($request->dispatch_status == 'Partial Dispatch')) {*/
    //update dispatch remark
         $dis_remark=$request->dispatch_remarks;

          if($request->pur_item_id)
            {

                $purchasearrayone=$request->pur_item_id;
            }
   foreach($purchasearrayone as $index => $pur_item_id)
        {

       $purchase_order_itemone = PurchaseOrderItem::where('id', $pur_item_id)->select('quantity','product_id')->get();


                           foreach($purchase_order_itemone as $purchase_order_item)
                           {

  PurchaseOrderItem::where('id', $pur_item_id)
                        ->where('dispatches_id', $request->dispatch_id) // Correct column
                        ->update([
                            'dispatch_remarks' => $dis_remark[$index],
                            'updated_by' => auth()->id(),
                        ]);
                                

                                }
           }


              $request->dispatch_id;
            $mdispatch = dispatchmodify::where('dispatches_id', $request->dispatch_id)->get();
            if($request->sel)
            {

                $selarray1=$request->sel;
            }
            else
            {
            $selarray1[]=0;

            }
          
             if(sizeof($mdispatch)==0)
            {
            if($selarray1[0]==0)
            {
               return response()->json([
                'success' => false,
                'message' => 'Please choose products for dispatch: '
            ], 500);
               exit;

            }
           }
           if($request->bal_qty)
            {

                $balarray1=$request->bal_qty;
            }
            else
            {
            $balarray1[]=0;

            }
            if($balarray1[0]!=0)
            {
               if($selarray1[0]==0)
            {
               return response()->json([
                'success' => false,
                'message' => 'Please choose products for dispatch: '
            ], 500);
               exit;

            }
           }
            

           $balance_array=$request->bal_qty;
        $new_qty=$request->new_qty;
                $dis_remark=$request->dispatch_remarks;

       $dispatch_remarks=$request->dispatch_remarks;
         $pur_item_id=$request->pur_item_id;
         foreach($balarray1 as $index => $balarray1)
            {

                 if($new_qty[$index]>$balarray1)
                 {

                return response()->json([
                'success' => false,
                'message' => 'check your product quantity:Enter Qty to be dispatched greater than the current '
            ], 500);
               exit;

                 }


            }

           if($request->pur_item_id)
            {

                $purchasearray=$request->pur_item_id;
            }
            else
            {
            $purchasearray[]=0;

            }

        foreach($purchasearray as $index => $pur_item_id)
        {


              $mdispatchcheckitem = dispatchmodify::where('purchase_order_items_id', $pur_item_id)->get();
                       if(sizeof($mdispatchcheckitem)!=0)
                       {

                        $totalqty=0;
                        $reqqty=0;
                          $totalreqqty=0;
                          $balance_dispatched=0;
                        foreach($mdispatchcheckitem as $mdischeckitem)
                        {

                            if (in_array($pur_item_id, $selarray1))
                            {
                             

                                $totalqty=$mdischeckitem['total_qty'];
                                $totalqty1=$totalqty;
                                $reqqty=$mdischeckitem['required_qty'];;
                                $totalreqqty=$totalreqqty+$reqqty;
                                $balance_dispatched=$totalqty-$totalreqqty;
                                if($balance_dispatched!=0) 
                               {
                        
                                  $modifydispatch = DB::table('cf')->insert([
                               'purchase_order_id' => $request->purchase_order_id,
                                 'purchase_order_items_id' => $pur_item_id,
                                 'dispatches_id' => $request->dispatch_id,
                                  'total_qty' => (string)$totalqty1,
                                     'required_qty' => $new_qty[$index],
                                   'status' => '1'

                                    ]);

                        PurchaseOrderItem::where('id', $pur_item_id)
                        ->where('dispatches_id', $request->dispatch_id) // Correct column
                        ->update([
                            'dispatch_remarks' => $dis_remark[$index],
                            'updated_by' => auth()->id(),
                        ]);

  

                                  }
                                  else
                                 {
                                                                  

                               //no operation
                        $c=PurchaseOrderItem::where('id', $pur_item_id)
                        ->where('dispatches_id', $request->dispatch_id) // Correct column
                        ->update([
                            'dispatch_remarks' => $dis_remark[$index]
                        ]);


                                 }
                             }
                             else
                             {

                               // no operation
                        PurchaseOrderItem::where('id', $pur_item_id)
                        ->where('dispatches_id', $request->dispatch_id) // Correct column
                        ->update([
                            'dispatch_remarks' => $dis_remark[$index],
                            'updated_by' => auth()->id(),
                        ]);
                             }

                        }


                       }
                       else
                       {

                      $purchase_order_item = PurchaseOrderItem::where('id', $pur_item_id)->select('quantity','product_id')->get();


                           foreach($purchase_order_item as $purchase_order_item)
                           {
                                
                                 $reqqunatity= $purchase_order_item['quantity'];
                                 if (in_array($pur_item_id, $selarray1))
                                {

                                
                                    
                                  $modifydispatch = DB::table('cf')->insert([
                               'purchase_order_id' => $request->purchase_order_id,
                                 'purchase_order_items_id' => $pur_item_id,
                                 'dispatches_id' => $request->dispatch_id,
                                  'total_qty' => (string)$reqqunatity,
                                     'required_qty' => $new_qty[$index],
                                   'status' => '1'

                                    ]);
                               PurchaseOrderItem::where('id', $pur_item_id)
                        ->where('dispatches_id', $request->dispatch_id) // Correct column
                        ->update([
                            'dispatch_remarks' => $dis_remark[$index],
                            'updated_by' => auth()->id(),
                        ]);
                                

                                }


                           }

                       }


            }
//setting dispatched status
               $dispatch = Dispatch::with(['purchaseOrder.items.product', 'customer'])->findOrFail($request->dispatch_id);
        $mdispatch = dispatchmodify::where('dispatches_id', $request->dispatch_id)->get();
        $dispatchflag="0";
        foreach($dispatch->purchaseOrder->items as $orderItem)
        {
            if($dispatch->id==$orderItem->dispatches_id)
            {

                $reqqty=0;
                $totalreqqty=0;
                $balance_dispatched=0;
                $orginal_qty=0;
                foreach($mdispatch as $md)
                {


                    $product_item_id=$orderItem->id;
                    if($md->purchase_order_items_id==$product_item_id)
                    {     
                        $orginal_qty=$orderItem->quantity;
                        $reqqty=$md->required_qty;
                        $totalreqqty=$totalreqqty+$reqqty;
                    }
                 }
                 $display_qty=$orderItem->quantity-$totalreqqty;
                $balance_dispatched=$orderItem->quantity-$totalreqqty;                                            
                 if($balance_dispatched!=0)
                {
                    $status="Not Fully Dispatched";
                    $dispatchflag="1";
                                                               
                }
               else{

                $status="Dispatched";
                 $dispatchflag="2";
                }
            }
       }


 // Validate the request
            $request->validate([
                'dispatch_id' => 'required|exists:dispatches,id',
                'dispatch_status' => 'required|string',
                'purchase_order_id' => 'required',
                'mode_of_delivery' => 'required',
                'remarks' => 'required',
            ]);
    

    
if($dispatchflag=="2")
{

   $request->dispatch_status=$request->dispatch_status; 
}
else
{

$request->dispatch_status="Partial Dispatch";
}

           

            // Find the existing dispatch record
            $dispatch = Dispatch::findOrFail($request->dispatch_id);
    
            // Update the dispatch record
            $dispatch->update([
                'status' => $request->dispatch_status,
                'remarks' => $request->remarks,
                'mode_of_delivery' => $request->mode_of_delivery,
                'updated_by' => auth()->id(),
            ]);
    
            // Check if the status was updated to 'Completed' (optional based on your business logic)
            // if ($request->dispatch_status == 'Completed') {
                PurchaseOrderMaster::where('id', $request->purchase_order_id)
                    ->update([
                        'purchase_order_status' => $request->dispatch_status,
                        'updated_by' => auth()->id(),
                    ]);
            // }

    
//new code check dispatch status is completed
                     $dispatchqtystatus=$dispatch['dispatch_qty_updation'];

                     //if ($request->dispatch_status == 'Completed') {


                         /*  $purchase_order_item = PurchaseOrderItem::where('purchase_order_id', $request->purchase_order_id)->select('quantity','product_id')->get();*/
$purchase_order_item = PurchaseOrderItem::where('dispatches_id', $request->dispatch_id)->select('quantity','product_id')->get();
                           //print_r($purchase_order_item);


                           foreach($purchase_order_item as $purchase_order_item)
                           {
                                
                        $reqqunatity= $purchase_order_item['quantity'];
                        $product_id=$purchase_order_item['product_id'];

                       if (($request->dispatch_status == 'Completed')||($request->dispatch_status == 'Dispatched')) {

                          if($dispatchqtystatus==0)
                          {

                            Dispatch::where('id', $request->dispatch_id)
                    ->update([
                        'dispatch_qty_updation' => 1
                    ]);


                    $item_transaction = ItemTransaction::where('item_id', $product_id)->select('quantity')->get();
                    $transaction_real_qty1=$item_transaction[0]['quantity'];
                    $transaction_real_qty= $transaction_real_qty1-$reqqunatity;
                      
                     if($transaction_real_qty>=0)
                     {
                       ItemTransaction::where('item_id', $product_id)
                    ->update([
                        'quantity' => $transaction_real_qty
                    ]);
                    }
                    else
                    {

                  ItemTransaction::where('item_id', $product_id)
                    ->update([
                        'quantity' => 0
                    ]);

                    }

                    $item_genqty = ItemGeneralQuantity::where('item_id', $product_id)->select('quantity')->get();
                    $itemgen_real_qty1=$item_genqty[0]['quantity'];
                    $itemgen_real_qty=$itemgen_real_qty1-$reqqunatity;
                       if($itemgen_real_qty>=0)
                     {
                      ItemGeneralQuantity::where('item_id', $product_id)
                    ->update([
                        'quantity' => $itemgen_real_qty
                    ]);
                }
                     else
                     {
                      ItemGeneralQuantity::where('item_id', $product_id)
                    ->update([
                        'quantity' => 0
                    ]);
                }







                          }
                          else
                          {
                                 //no action required

                          }



                       }
                       else
                       {


                                 //no action required


                       }
                   }

                //echo $purchase_order_item->quantity;

                   // }


            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => __('Dispatch updated successfully!'),
                'redirect' => route('dispatch.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => __('Error updating dispatch: ') . $e->getMessage()
            ], 500);
        }
    }

   public function FilterList($status): View  {

         

              return view('dispatches.filterlist',compact('status'));

    }
//     public function datatableFilterList(Request $request)
// {
//     $query = Dispatch::with(['purchaseOrder.items.product', 'customer','ProductionItemMaster']);

//     $ccstatus=$request->cstatus;
// if ($request->filled('cstatus')) {

//  if($ccstatus=="pending")
//     {
//         $ccstatus1='Pending';
//         $ccstatus2='Dispatch Pending';

//           $query->whereIn('status',  array($ccstatus1 ,$ccstatus2));

//     }
//     if($ccstatus=="partial")
//     {
//       $ccstatus1='Dispatched';

//           $query->where('status',  $ccstatus1 );

//     }
//      if($ccstatus=="completed")
//     {
//         $ccstatus1='Completed';

//           $query->where('status',  $ccstatus1 );

//     }

//     }


//     if ($request->filled('customer_id')) {

//          $query->whereHas('customer', function ($q) use ($request) {
//             $q->where('id', $request->customer_id);
//         });
//     }


//  if ($request->filled('mode_name')) {
//         $query->where('mode_of_delivery','like', "%{$request->mode_name}%"  );
//     }

//       if ($request->filled('duedate')) {
//         //echo "1";
//         // $request->duedate;

      


//           $query->whereHas('purchaseOrder', function ($q) use ($request) {
//                      $duedate1= date("Y-m-d", strtotime($request->duedate));

//                 $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$duedate1}%"]);
//             });
//     }
    

//     if ($request->filled('gapdate')) {
//         //echo "1";
//         //echo $request->podate;
//         $query->whereHas('purchaseOrder', function ($q) use ($request) {
//                     $gapdate= $request->gapdate;
//          $days = (int) preg_replace('/[^0-9]/', '', $gapdate);
//                 $date = today()->subDays($days);
//              $q->whereDate('created_at', $date->format('Y-m-d'));



//         });


//     }

// if ($request->filled('dispatch_order_status')) {
//         $query->where('status', $request->dispatch_order_status);
//     }
    


//     // Conditional Filters
//     if ($request->filled('party_id')) {
//         $query->whereHas('customer', function ($q) use ($request) {
//             $q->where('first_name', 'like', '%' . $request->party_id . '%')
//               ->orWhere('last_name', 'like', '%' . $request->party_id . '%');
//         });
//     }

//     if ($request->filled('user_id')) {
//         $query->where('user_id', $request->user_id);
//     }

//     if ($request->filled('from_date') && $request->filled('to_date')) {
//         $query->whereBetween('created_at', [
//             Carbon::parse($request->from_date)->startOfDay(),
//             Carbon::parse($request->to_date)->endOfDay()
//         ]);
//     }

//     // Order by latest
//     $query->orderByDesc('id');

//     return DataTables::of($query)
//         ->addIndexColumn()

//         // Searchable Columns
//         ->filterColumn('purchase_order_identifier', function ($query, $keyword) {
//             $query->where(function($q) use ($keyword) {
//                 $q->where('purchase_order_identifier', 'like', "%{$keyword}%")
//                   ->orWhere('purchase_order_id', 'like', "%{$keyword}%");
//             });
//         })

//         ->filterColumn('mode_of_delivery', function ($query, $keyword) {
//             $query->where('mode_of_delivery', 'like', "%{$keyword}%");
//         })

//         ->filterColumn('customer_id', function ($query, $keyword) {
//             $query->whereHas('customer', function ($q) use ($keyword) {
//                 $q->where('first_name', 'like', "%{$keyword}%")
//                   ->orWhere('last_name', 'like', "%{$keyword}%");
//             });
//         })

//         ->filterColumn('created_at', function ($query, $keyword) {
//             $query->whereHas('purchaseOrder', function ($q) use ($keyword) {
//                 $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$keyword}%"]);
//             });
//         })

//         ->filterColumn('CreatedGap', function ($query, $keyword) {
//             if (preg_match('/\d+/', $keyword, $matches)) {
//                 $days = (int) $matches[0];
//                 $targetDate = now()->subDays($days)->toDateString();

//                 $query->whereHas('purchaseOrder', function ($q) use ($targetDate) {
//                     $q->whereDate('created_at', $targetDate);
//                 });
//             }
//         })

//         // Display Columns
//         ->editColumn('purchase_order_identifier', fn($row) => $row->purchase_order_identifier)
//         ->editColumn('mode_of_delivery', fn($row) => $row->mode_of_delivery)
//         ->editColumn('remarks', fn($row) => $row->remarks)

//         ->editColumn('customer_id', function ($row) {
//             $customer = $row->customer;
//             return $customer ? "{$customer->first_name} {$customer->last_name}" : 'No customer';
//         })
//           ->editColumn('product_info', function ($row) {

//       $html="<table id='pinfo'><thead><th>Product Name</th><th>Total Quantity</th><th>From Production</th><th>From Stock</th></thead>";

//   $reqqunatity=0;


// $purchase_order_item_all = PurchaseOrderItem::where('dispatches_id', $row->id)->get();

//             foreach($purchase_order_item_all as $orderItem)
//             {


//   //$purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id',$orderItem->product->id)->select('requested_qty')->get();
// //echo $row->id;
//                 //$purchase_order_item = PurchaseOrderMaster::where('dispatches_id', $row->id)->WhereNotNull('dispatches_id')->where('item_id',$orderItem->product->id)->select('requested_qty')->get();
// //$purchase_order_item = PurchaseOrderItem::where('dispatches_id', $row->id)->where('product_id',$orderItem->product->id)->select('quantity')->get();

// $purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id',$orderItem->product->id)->select('requested_qty')->get();

//                   $c=sizeof($purchase_order_item);
//                 if($c<=1)                
                
//                 {        foreach($purchase_order_item as $purchase_order_item)
//                           {

//                           $reqqunatity= $purchase_order_item['requested_qty'];


//                           }
//                           $stock_qty=$orderItem->quantity-$reqqunatity;

         
//                       if(!empty($purchase_order_item['requested_qty']))
//                       {
//                     $html.="<tr><td>".$orderItem->product->name."</td><td>".$orderItem->quantity."</td><td>".$reqqunatity."</td><td>".$stock_qty."</td></tr>";
//                       }
//                       else
//                       {
//                         $reqqunatity=0;
//                          $stock_qty=$orderItem->quantity-$reqqunatity;
//                           $html.="<tr><td>".$orderItem->product->name."</td><td>".$orderItem->quantity."</td><td>".$reqqunatity."</td><td>".$stock_qty."</td></tr>";
//                       }
                

//                 }
//                 else
//                 {

//              $purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id',$orderItem->product->id)->select('requested_qty')->get();              

//                 foreach($purchase_order_item as $purchase_order_item)
//                           {

//                           $reqqunatity= $purchase_order_item['requested_qty'];
//                             $stock_qty=$orderItem->quantity-$reqqunatity;

                           
//                           }

//                  if(empty($purchase_order_item))
//                  {

//                              $html.="<tr><td>".$orderItem->product->name."</td><td>".$orderItem->quantity."</td><td></td><td></td></tr>";
//                          }

//                 }


//             }


                                                   

//             return $html;
//         })
       
 
//         ->editColumn('created_at', function ($row) {
//             return optional($row->purchaseOrder)?->created_at?->format('d-m-Y') ?? 'N/A';
//         })

//         ->editColumn('CreatedGap', function ($row) {
//             $createdAt = optional($row->purchaseOrder)->created_at;
//             if ($createdAt) {
//                 $gap = now()->diffInDays($createdAt) . ' days';
//                 $class = in_array($row->status, ['Completed', 'Dispatched']) ? 'text-success' : 'text-danger';
//                 if($row->status=="Completed")
//                 {
//             $createdAt = optional($row->purchaseOrder)->created_at;
//             $updated_at = optional($row->purchaseOrder)->updated_at;

//                                     $gap = $updated_at->diffInDays($createdAt) . ' days';

//                   return "<span class=''>{$gap}</span>";

//                 }
//                 else
//                 {


//                     return "<span class='{$class}'>{$gap}</span>";

//                 }
//             }
//             return 'N/A';
//         })

//         ->editColumn('action', function ($row) {
//             $editUrl = route('dispatch.edit', ['id' => $row->id]);
//             return '<a class="dropdown-item1" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> ' . __('app.edit') . '</a>';
//         })

//         ->rawColumns(['action', 'CreatedGap','product_info'])
//         ->make(true);
// }

 public function datatableFilterList(Request $request)
    {
        $query = PurchaseOrderItem::with(['product', 'purchaseOrder.party', 'dispatch'])->whereNotNull('dispatches_id')->orderByDesc('id');


        $ccstatus = $request->cstatus;
        if ($request->filled('cstatus')) {

            if ($ccstatus == "pending") {
                $ccstatus1 = 'Pending';
                $ccstatus2 = 'Dispatch Pending';

                $query->whereHas('dispatch',  function ($q) use ($ccstatus1, $ccstatus2) {
                    $q->whereIn('status',  array($ccstatus1, $ccstatus2));
                });
            }
            if ($ccstatus == "partial") {
                $ccstatus1 = 'Dispatched';

                $query->whereHas('dispatch',  function ($q) use ($ccstatus1) {
                    $q->where('status', $ccstatus1);
                });
            }
            if ($ccstatus == "completed") {
                $ccstatus1 = 'Completed';

                $query->whereHas('dispatch',  function ($q) use ($ccstatus1) {
                    $q->where('status', $ccstatus1);
                });
            }
        }


        if ($request->filled('customer_id')) {
            $query->whereHas('purchaseOrder.party', function ($q) use ($request) {
                $q->where('id', $request->customer_id);
            });
        }

        if ($request->filled('duedate')) {
            $duedate1 = date("Y-m-d", strtotime($request->duedate));
            $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$duedate1}%"]);
        }

        if ($request->filled('gapdate')) {
            $gapdate = $request->gapdate;
            $days = (int) preg_replace('/[^0-9]/', '', $gapdate);
            $targetDate = now()->subDays($days)->toDateString();
            $query->whereDate('created_at', $targetDate);
        }

        if ($request->filled('dispatch_order_status')) {
            $query->whereHas('dispatch',  function ($q) use ($request) {
                $q->where('status', $request->dispatch_order_status);
            });
        }


        return DataTables::of($query)
            ->addIndexColumn()

            // Display Columns
            ->addColumn('dispatch_order', fn($row) => $row->dispatch->dispatch_order ?? '')
            ->addColumn('purchase_order_identifier', fn($row) => $row->dispatch->purchase_order_identifier ?? '')
            ->addColumn('customer_id', function ($row) {
                $customer = $row->purchaseOrder->party;
                return $customer ? "{$customer->first_name} {$customer->last_name}" : 'No customer';
            })
            ->addColumn('product_info', function ($row) {

                $html = "<table id='pinfo'><thead><th>Product Name</th><th>Total Quantity</th><th>From Production</th><th>From Stock</th></thead>";

                $purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id', $row->product->id)->select('requested_qty')->get();

                $c = sizeof($purchase_order_item);
                $reqqunatity = 0;

                if ($c <= 1) {
                    foreach ($purchase_order_item as $purchase_order_item) {

                        $reqqunatity = $purchase_order_item['requested_qty'];
                    }
                    $stock_qty = $row->quantity - $reqqunatity;


                    if (!empty($purchase_order_item['requested_qty'])) {
                        $html .= "<tr><td>" . $row->product->name . "</td><td>" . $row->quantity . "</td><td>" . $reqqunatity . "</td><td>" . $stock_qty . "</td></tr>";
                    } else {
                        $reqqunatity = 0;
                        $stock_qty = $row->quantity - $reqqunatity;
                        $html .= "<tr><td>" . $row->product->name . "</td><td>" . $row->quantity . "</td><td>" . $reqqunatity . "</td><td>" . $stock_qty . "</td></tr>";
                    }
                } else {

                    $purchase_order_item = ProductionItemMaster::where('purchase_order_id', $row->purchase_order_id)->where('item_id', $row->product->id)->select('requested_qty')->get();

                    foreach ($purchase_order_item as $purchase_order_item) {

                        $reqqunatity = $purchase_order_item['requested_qty'];
                        $stock_qty = $row->quantity - $reqqunatity;
                    }

                    if (empty($purchase_order_item)) {

                        $html .= "<tr><td>" . $row->product->name . "</td><td>" . $row->quantity . "</td><td></td><td></td></tr>";
                    }else {
                        $html .= "<tr><td>" . $row->product->name . "</td><td>" . $row->quantity . "</td><td></td><td></td></tr>";
                    }
                }

                return $html;
            })


            ->editColumn('created_at', function ($row) {
                return optional($row)?->created_at?->format('d-m-Y') ?? 'N/A';
            })

            ->editColumn('CreatedGap', function ($row) {
                $createdAt = optional($row)->created_at;

                if ($createdAt) {
                    $dispatchStatus = optional($row->dispatch)->status;
                    $updatedAt = optional($row)->updated_at;
                    $gap = now()->diffInDays($createdAt) . ' days';
                    $class = in_array($dispatchStatus, ['Completed', 'Dispatched']) ? 'text-success' : 'text-danger';

                    if ($dispatchStatus === "Completed" && $updatedAt) {
                        $gap = $updatedAt->diffInDays($createdAt) . ' days';
                        return "<span class='text-success'>{$gap}</span>";
                    }

                    return "<span class='{$class}'>{$gap}</span>";
                }

                return 'N/A';
            })

            ->editColumn('status', function ($row) {
                $status = optional($row->dispatch)->status;

                if (!$status) {
                    return "<span class='badge bg-secondary'>N/A</span>";
                }

                switch ($status) {
                    case 'Pending':
                        $class = 'bg-warning text-dark';
                        break;
                    case 'Completed':
                        $class = 'bg-success';
                        break;
                    case 'Dispatched':
                        $class = 'bg-primary';
                        break;
                    case 'Dispatch Pending':
                        $class = 'bg-warning text-dark';
                        break;
                    case 'Partial Dispatch':
                        $class = 'bg-warning text-dark';
                        break;
                    default:
                        $class = 'bg-secondary';
                        break;
                }

                return "<span class='badge {$class}'>{$status}</span>";
            })
            ->editColumn('dispatch_id', function ($row) {
                return $row->dispatch->id ?? '';
            })
            ->addColumn('action', function ($row) {
                $editUrl = '';
                $dispatchId = optional($row->dispatch)->id;

                if ($dispatchId) {
                    $editUrl = route('dispatch.edit', ['id' => $dispatchId]);
                }

                return '<a class="dropdown-item1" href="' . $editUrl . '">
                <i class="bi bi-trash"></i>
                <i class="bx bx-edit"></i> ' . __('app.edit') . '
            </a>';
            })

            ->rawColumns(['action', 'CreatedGap', 'product_info', 'status'])
            ->make(true);
    }

        public function storeReturn(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:purchase_order_items,id',
            'is_damaged' => 'required|boolean',
            'reason'     => 'required_if:is_damaged,1|string|nullable',
        ], [
            'item_id.required'      => 'The item ID is required.',
            'item_id.exists'        => 'The selected item does not exist.',
            'is_damaged.required'   => 'Please select if the item is damaged or not.',
            'is_damaged.boolean'    => 'Invalid value for damaged status.',
            'reason.required_if'    => 'Please provide a reason since the item is marked as damaged.',
            'reason.string'         => 'The reason must be a valid text.',
        ]);

        ItemReturn::create([
            'purchase_order_item_id' => $request->item_id,
            'is_damaged' => $request->is_damaged,
            'reason' => $request->is_damaged ? $request->reason : null,
        ]);

        if (!$request->is_damaged) {
            $purchase = PurchaseOrderItem::find($request->item_id);
            $itemTransaction = ItemTransaction::where('item_id', $purchase->product_id)->first();
            if ($itemTransaction) {
                $itemTransaction->quantity += $purchase->quantity;
                $itemTransaction->save();
            } else {
                ItemTransaction::create([
                    'item_id' => $purchase->product_id,
                    'quantity' => $purchase->quantity,
                ]);
            }
        }

        return response()->json(['status' => true]);
    }

    public function returnDetails($id)
    {
        $return = ItemReturn::findOrFail($id);

        return response()->json([
            'status'      => true,
            'is_damaged'  => $return->is_damaged,
            'reason'      => $return->reason,
            'created_at' => $return->created_at->format('d M Y'),
        ]);
    }
}
