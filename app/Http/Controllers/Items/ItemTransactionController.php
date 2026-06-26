<?php

namespace App\Http\Controllers\Items;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Traits\FormatNumber; 
use App\Traits\FormatsDateInputs;
use App\Models\Items\Item;
use App\Models\Items\ItemTransaction;
use App\Models\Items\ItemGeneralQuantity;
use App\Models\PurchaseOrders\PurchaseOrderMaster;
use App\Models\PurchaseOrders\PurchaseOrderItem;
use App\Services\StockImpact;

class ItemTransactionController extends Controller
{
    use FormatsDateInputs;

    use FormatNumber;

    private $stockImpact;

    function __construct(StockImpact $stockImpact)
    {
        $this->stockImpact = $stockImpact;
    }

    public function list($id) : View {
        $item = Item::with('baseUnit', 'category')->find($id);
        return view('items.transaction.list', compact('item'));
    }
    
    public function edit($id)
    {

       // $item = ItemTransaction::with(['item'])->findOrFail($id);
        $item = ItemTransaction::with(['item'])->where('item_id',$id)->get();
               // print_r($item);

       return view('items.transaction.edit', compact('item'));
 
    }
    
    public function update(Request $request,$id)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric',
            'item_id' => 'required|integer'
        ]);
    

$extraQty=$validated['quantity'];
$currentquantity=$request['currentquantity'];
$avaquantity=$request['avaquantity'];

$sum=$currentquantity+$extraQty;
$sum1=$avaquantity+$extraQty;

if($sum<0)
{
     $item = ItemTransaction::with(['item'])->where('item_id',$request->item_id)->get();

$error="Failed to update stock due to negative values";
        return view('items.transaction.edit', compact('item','error'));
        exit;

}

if($sum1<0)
{
     $item = ItemTransaction::with(['item'])->where('item_id',$request->item_id)->get();

$error="Failed to update stock due to negative values1";
        return view('items.transaction.edit', compact('item','error'));
        exit;

}


                ItemTransaction::updateOrCreate(
                       ['item_id' => $request->item_id],
                         [

                            'quantity' => DB::raw("quantity + $extraQty"),
                            'avaquantity' => DB::raw("avaquantity + $extraQty")

                     ]
                     );

                     ItemGeneralQuantity::updateOrCreate(
                        [
                            'item_id' => $request->item_id,
                             'warehouse_id' => 1,
                        ],
                         [
                            'quantity' => DB::raw("quantity + $extraQty"),
                            'avaquantity' => DB::raw("avaquantity + $extraQty")
                         ]
                     );


                         /*  $purchase_order_item = PurchaseOrderItem::where('product_id', $request->item_id)->select('quantity','purchase_order_id')->get();

                         $flag=0;
                           foreach($purchase_order_item as $purchase_order_item)
                           {

                               $reqqunatity= $purchase_order_item['quantity'];
                                $purchase_order_id=$purchase_order_item['purchase_order_id'];
                                $purchase_masters = PurchaseOrderMaster::where('id', $purchase_order_id)->select('purchase_order_status')->get();
                                        $purchase_order_status=$purchase_masters[0]['purchase_order_status'];
                                       $status_array=array("Dispatched","Completed");
                                       if(!in_array($purchase_order_status, $status_array))
                                     {
                                      

                                            $flag=1;
                                         if($flag==1)
                                         {



                                   $item = ItemTransaction::with(['item'])->where('item_id',$request->item_id)->get();




$error="Failed to update stock due to some dispatch process";
        return view('items.transaction.edit', compact('item','error'));

                                          }
                                        }

               

                           }
                          

*/
       // Update ItemTransaction where item_id = $request->item_id and id = $id
       /* $transaction = ItemTransaction::where('item_id', $request->item_id)
                        ->firstOrFail();
    
        $transaction->quantity = $validated['quantity'];
         $transaction->avaquantity=$validated['quantity'];
        $transaction->save();
    
        // Update ItemGeneralQuantity where item_id = $request->item_id and warehouse_id = 1
        $generalQty = ItemGeneralQuantity::where('item_id', $request->item_id)
                        ->where('warehouse_id', 1)
                        ->first();
    
        if ($generalQty) {
            $generalQty->quantity = $validated['quantity'];
            $generalQty->avaquantity=$validated['quantity'];
            $generalQty->save();
        }*/
                 return redirect()->route('item.transaction.stocklist')->with('success', 'Quantity updated successfully.');

    
    
    }


   public function stockList()
    {
        // Fetch all item transactions with the related 'item'
        $itemTransactions1 = ItemTransaction::with('item','itemsfull')->get();
        $itemTransactions=array();
        $i=1;
        foreach($itemTransactions1 as $itemtrans)
        {

             $itemTransactions[$i]['id']= $itemtrans->item->id;
             $itemTransactions[$i]['name']= $itemtrans->item->name;
             $itemTransactions[$i]['quantity']= $itemtrans->quantity;
             $itemTransactions[$i]['avaquantity']= $itemtrans->avaquantity;
             $itemTransactions[$i]['created_at']= $itemtrans->created_at;
            $itemTransactions[$i]['updated_at']= $itemtrans->updated_at;
$itemTransactions[$i]['comm_stock']=0;
$purchase_order_item = PurchaseOrderItem::where('product_id', $itemtrans->item->id)->select('quantity','purchase_order_id')->get();
                      $comm_stock[$i]=0;
                         $flag=0;
                           foreach($purchase_order_item as $purchase_order_item)
                           {

                                $reqqunatity[$i]= $purchase_order_item['quantity'];
                                 $purchase_order_id=$purchase_order_item['purchase_order_id'];

                                $purchase_masters = PurchaseOrderMaster::where('id', $purchase_order_id)->select('purchase_order_status')->get();
                                foreach( $purchase_masters as $fg)
                                {
                                         $purchase_order_status=$fg['purchase_order_status'];
                                       $status_array=array("Dispatched","Completed");
                                       if(!in_array($purchase_order_status, $status_array))
                                     {
                                      

                                            $flag=1;
                                         if($flag==1)
                                         {



                                               $comm_stock[$i]=$comm_stock[$i]+$reqqunatity[$i];
                                              $itemTransactions[$i]['comm_stock']=$comm_stock[$i];





                                          }
                                          else
                                          {


                                          }
                                        }
                                        else
                                        {


                                        }
                                }
               

                           }



$i=$i+1;

        }

        // Return the view with the data
        return view('items.transaction.stocklist', compact('itemTransactions'));
    }


    public function datatableList(Request $request){
        $data = ItemTransaction::with('unit')->where('item_id', $request->item_id);

        return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('transaction_date', function ($row) {
                        return $row->formatted_transaction_date;
                    })
                    ->addColumn('reference_no', function ($row) {
                        return $row->transaction->getTableCode()??'';
                    })
                    ->addColumn('price', function ($row) {
                        return $this->formatWithPrecision($row->unit_price);
                    })
                    ->addColumn('quantity', function ($row) {
                        return $this->formatQuantity($row->quantity);
                    })
                    ->addColumn('stock_impact', function ($row) {
                        return $this->stockImpact->returnStockImpact($row->unique_code, $row->quantity);
                    })
                    ->addColumn('unit_name', function ($row) {
                        return $row->unit->name;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

}
