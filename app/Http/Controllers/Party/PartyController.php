<?php

namespace App\Http\Controllers\Party;

use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\PartyRequest;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\QueryException;

use App\Services\AccountTransactionService;
use App\Services\PartyTransactionService;
use App\Services\PartyService;
use App\Models\Party\Party;
use App\Models\Party\PartyTransaction;

use App\Enums\AccountUniqueCode;
use App\Models\Accounts\AccountGroup;
use App\Models\Accounts\Account;
use App\Models\Items\ProductionItemMaster;
use App\Models\Dispatch\Dispatch;


class PartyController extends Controller
{
    use FormatsDateInputs;

    use FormatNumber;

    public $accountTransactionService;

    public $partyTransactionService;

    public $partyService;

    public function __construct(PartyTransactionService $partyTransactionService, AccountTransactionService $accountTransactionService, PartyService $partyService)
    {
        $this->partyTransactionService = $partyTransactionService;
        $this->accountTransactionService = $accountTransactionService;
        $this->partyService = $partyService;
    }

    public function getLang($partyType) : array
    {
        if($partyType == 'customer'){
            $lang = [
                'party_list' => __('customer.list'),
                'party_create' => __('customer.create_customer'),
                'party_update' => __('customer.update_customer'),
                'party_type' => $partyType,
                'party_details' => __('customer.details'),
            ];
        }else{
            $lang = [
                'party_list' => __('supplier.list'),
                'party_create' => __('supplier.create_supplier'),
                'party_update' => __('supplier.update_supplier'),
                'party_type' => $partyType,
                'party_details' => __('supplier.details'),
            ];
        }
        return $lang;
    }

    /**
     * Create a new party.
     *
     * @return \Illuminate\View\View
     */
    public function create($partyType) : View {
        $lang = $this->getLang($partyType);
        return view('party.create', compact('lang'));
    }

    /**
     * Edit a party.
     *
     * @param int $id The ID of the party to edit.
     * @return \Illuminate\View\View
     */
    public function edit($partyType, $id) : View {
        $lang = $this->getLang($partyType);

        $party = Party::where('party_type', $partyType)->whereId($id)->get()->first();
        if(!$party){
            return abort(403, 'Unauthorized');
        }


        $transaction = $party->transaction()->get()->first();//Used Morph

        $opening_balance_type = 'to_pay';
        $to_receive = false;
        if($transaction){
            $transaction->opening_balance = ($transaction->to_pay > 0) ? $this->formatWithPrecision($transaction->to_pay, comma:false) : $this->formatWithPrecision($transaction->to_receive, comma:false);

            $opening_balance_type = ($transaction->to_pay > 0) ? 'to_pay' : 'to_receive';
        }

        /**
         * Todays Date
         * */
        $todaysDate = $this->toUserDateFormat(now());

        return view('party.edit', compact('party', 'transaction', 'opening_balance_type', 'todaysDate', 'lang'));
    }



    /**
     * Return JsonResponse
     * */
    public function store(PartyRequest $request)  {
        try {

            DB::beginTransaction();

            /**
             * Get the validated data from the ItemRequest
             * */
            $validatedData = $request->validated();

            /**
             * Know which party type
             * `supplier` or `customer`
             * */
            $partyType = $request->party_type;

            /**
             * Know which operation want
             * `save` or `update`
             * */
            $operation = $request->operation;

            /**
             * Save or Update the Items Model
             * */
            $recordsToSave = [
                'first_name'        =>  $request->first_name,
                'last_name'         =>  $request->last_name,
                'email'             =>  $request->email,
                'mobile'            =>  $request->mobile,
                'phone'             =>  $request->phone,
                'whatsapp'          =>  $request->whatsapp,
                'party_type'        =>  $partyType,
                'tax_number'        =>  $request->tax_number,
                'shipping_address'  =>  $request->shipping_address,
                'billing_address'   =>  $request->billing_address,
                'is_set_credit_limit'=>  $request->is_set_credit_limit,
                'credit_limit'      =>  $request->credit_limit,
                'status'            =>  $request->status,
                'default_party'     =>  $request->default_party,
                'currency_id'       =>  $request->currency_id,
            ];
            if($request->has('state_id')){
                $recordsToSave['state_id'] = $request->state_id??null;
            }

            /**
             * for Party_type = "Customer"
             * */
            if($request->has('is_wholesale_customer')){
                $recordsToSave['is_wholesale_customer'] = $request->is_wholesale_customer;
            }

            if($request->operation == 'save'){
                $partyModel = Party::create($recordsToSave);
            }else{
                $partyModel = Party::find($request->party_id);

                //Load Party Transactions
                $partyTransactions = $partyModel->transaction;

                foreach ($partyTransactions as $partyTransaction) {
                    //Delete Account Transaction
                    $partyTransaction->accountTransaction()->delete();

                    //Delete Party Transaction
                    $partyTransaction->delete();
                }

                //Update the party records
                $partyModel->update($recordsToSave);
            }

            $request->request->add(['partyModel' => $partyModel]);

            /**
             * Update Party Transaction for opening Balance
             * */

            $transaction = $this->partyTransactionService->recordPartyTransactionEntry($partyModel, [
                    'transaction_date'      =>  $request->transaction_date,
                    'to_pay'                =>  ($request->opening_balance_type == 'to_pay')? $request->opening_balance??0 : 0,
                    'to_receive'                =>  ($request->opening_balance_type == 'to_receive')? $request->opening_balance??0 : 0,
                ]);
            if(!$transaction){
                throw new \Exception(__('party.failed_to_record_party_transactions'). " " . $recordDetails);
            }

            $this->accountTransactionService->partyOpeningBalanceTransaction($partyModel);

            //Account Create or Update
            $acccountCreateOrUpdate = $this->accountTransactionService->createOrUpdateAccountOfParty(partyId: $request->partyModel->id, partyName: $request->partyModel->first_name." ".$request->partyModel->last_name, partyType: $request->partyModel->party_type );
            if(!$acccountCreateOrUpdate){
                throw new \Exception(__('account.failed_to_create_or_update_account'));
            }

            //Update Other Default Party as a 0
            if($request->default_party){
                Party::where('party_type', $partyType)
                     ->whereNot('id', $request->partyModel->id)
                     ->update(['default_party' => 0]);
            }

            DB::commit();


            return response()->json([
                'status' => true,
                'message' => __('app.record_saved_successfully'),
                'data'  => [
                    'id' => $partyModel->id,
                    'first_name' => $partyModel->first_name,
                    'last_name' => $partyModel->last_name??'',
                    'curreny_id' => $partyModel->currency_id,
                ]
            ]);
        } catch (\Exception $e) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 409);

        }
    }


    /**
     * partyType: customer or supplier
     * */
    public function list($partyType) : View {
        $lang = $this->getLang($partyType);
        return view('party.list', compact('lang'));
    }

    public function datatableList(Request $request, $partyType){
        /**
         * party_type == customer then filter wholesale or retail customer
         * */
        $isWholesaleCustomer = $request->input('is_wholesale_customer');

        $data = Party::query()->where('party_type', $partyType);
        return DataTables::of($data)
                    ->filter(function ($query) use ($request, $isWholesaleCustomer) {
                        if ($request->has('search')) {
                            $searchTerm = $request->search['value'];
                            $query->where(function ($q) use ($searchTerm) {
                                $q->where('first_name', 'like', "%{$searchTerm}%")
                                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                                  ->orWhere('whatsapp', 'like', "%{$searchTerm}%")
                                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                                  ->orWhere('email', 'like', "%{$searchTerm}%")
                                  ;
                            });
                        }
                        if($isWholesaleCustomer!==null){
                            $query->where('is_wholesale_customer', $isWholesaleCustomer);
                        }
                    })
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app('company')['date_format']);
                    })
                    ->addColumn('name', function ($row) {
                        return $row->first_name." ".$row->last_name;
                    })
                    ->addColumn('username', function ($row) {
                        return $row->user->username??'';
                    })
                    ->addColumn('balance', function ($row) {
                        // Store the balance data in the row
                        $row->balanceData = $this->partyService->getPartyBalance($row->id);

                        // Return the formatted balance
                        return $this->formatWithPrecision($row->balanceData['balance']);
                    })
                    ->addColumn('balance_type', function ($row) {
                        // Return the status using the stored balance data
                        return $row->balanceData['status'];
                    })
                    ->addColumn('action', function($row) use ($partyType){
                            $id = $row->id;

                            $editUrl = route('party.edit', ['id' => $id, 'partyType' => $partyType]);
                            $deleteUrl = route('party.delete', ['id' => $id, 'partyType' => $partyType]);
                            $transactionUrl = route('party.transaction.list', ['id' => $id, 'partyType' => $partyType]);
                            $paymentUrl = route('party.payment.create', ['id' => $id, 'partyType' => $partyType]);

                            $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">

                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> '.__('app.edit').'</a>
                                </li>
                               
                                <li>
                                    <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id='.$id.'><i class="bx bx-trash"></i> '.__('app.delete').'</button>
                                </li>
                            </ul>
                        </div>';
                            return $actionBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
                    
                    //  <ul class="dropdown-menu">

                    //             <li>
                    //                 <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> '.__('app.edit').'</a>
                    //             </li>
                    //             <li>
                    //                 <a class="dropdown-item" href="' . $paymentUrl . '"><i class="bi bi-trash"></i><i class="bx bx-money"></i> '.__('payment.payment').'</a>
                    //             </li>
                    //             <li>
                    //                 <a class="dropdown-item party-payment-history" data-party-id="' . $id . '" role="button"></i><i class="bx bx-table"></i> '.__('payment.history').'</a>
                    //             </li>
                    //             <li>
                    //                 <a class="dropdown-item" href="' . $transactionUrl . '"><i class="bi bi-trash"></i><i class="bx bx-transfer-alt"></i> '.__('app.transactions').'</a>
                    //             </li>
                    //             <li>
                    //                 <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id='.$id.'><i class="bx bx-trash"></i> '.__('app.delete').'</button>
                    //             </li>
                    //         </ul>
    }

    public function delete(Request $request) : JsonResponse{

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = Party::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record_id',['record_id' => $recordId]),
                ]);

            }
            // You can perform additional validation checks here if needed before deletion
        }

        /**
         * All selected record IDs are valid, proceed with the deletion
         * Delete all records with the selected IDs in one query
         * */
        try{


            // Attempt deletion (as in previous responses)
            Party::whereIn('id', $selectedRecordIds)->chunk(100, function ($parties) {
                foreach ($parties as $party) {
                    //Load Party Transactions like Opening Balance and other payments
                    $partyTransactions = $party->transaction;

                    foreach ($partyTransactions as $partyTransaction) {
                        //Delete Payment Account Transactions
                        $partyTransaction->accountTransaction()->delete();

                        //Delete Party Transaction
                        $partyTransaction->delete();
                    }
                }
            });

            //Delete party
            Party::whereIn('id', $selectedRecordIds)->delete();

        }catch (QueryException $e){
            return response()->json(['message' => __('app.cannot_delete_records')], 409);
        }

        return response()->json([
            'status'    => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }
    /**
     * Ajax Response
     * Search Bar for select2 list
     * */
    function getAjaxSearchBarList(){
        $search = request('search');
        $partyType = request('party_type');

        $parties = Party::with('currency')->where(function($query) use ($search) {
                        $query->where('first_name', 'LIKE', "%{$search}%")
                              ->orWhere('last_name', 'LIKE', "%{$search}%")
                              ->orWhere('mobile', 'LIKE', "%{$search}%")
                              ->orWhere('phone', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->select('id', 'first_name', 'last_name', 'mobile', 'is_wholesale_customer', 'to_pay', 'to_receive', 'currency_id')
                    ->where('party_type', $partyType)
                    ->limit(8)
                    ->get();

        $response = [
            'results' => $parties->map(function ($party) {
                $partyBalance = $this->partyService->getPartyBalance($party->id);

                return [
                    'id' => $party->id,
                    'text' => $party->getFullName(),
                    'mobile' => $party->mobile,
                    'currency_id' => $party->currency_id,
                    'currency_name' => $party->currency->name,
                    'currency_code' => $party->currency->code,
                    'currency_exchange_rate_current' => $party->currency->exchange_rate,
                    'is_wholesale_customer' => $party->is_wholesale_customer,
                    'to_pay' => $partyBalance['status']=='you_pay' ? $partyBalance['balance'] : 0,
                    'to_receive' => $partyBalance['status']=='you_collect' ? $partyBalance['balance'] : 0,
                ];
            })->toArray(),
        ];

        return json_encode($response);

    }
    
     public function details($id)
    {
        $record = Party::find($id);
        return view('party.details', compact('record'));
    }

    public function getPage($id)
    {
        $perPage = request()->get('length', 10); // same as DataTables pageLength

        // Count how many rows come before this one in DESC order
        $position = Party::where('id', '>', $id)->count() + 1;

        // Convert position to page (0-based index)
        $page = floor(($position - 1) / $perPage);

        return response()->json(['page' => $page]);
    }

    public function show($id)
    {
        $record = Party::with('user', 'transaction')->findOrFail($id);

        $html = view('party.partials.details', compact('record', 'id'))->render();

        return response()->json([
            'html' => $html,
            'id'   => $id,
        ]);
    }

    public function productionList(Request $request, $id)
    {
        $query = ProductionItemMaster::with('item', 'item.brand', 'item.category', 'requestedBy', 'approvedBy', 'purchaseOrder', 'purchaseOrder.party', 'productionLists', 'packingLists')->whereHas('purchaseOrder', function ($q) use ($id) {
            $q->where('customer_id', $id);
        });
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy('id', 'desc');

        return datatables()->of($query)
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
            ->addColumn('item', function ($row) {
                if ($row->item) {
                    $itemName = $row->item->name ?? 'N/A';
                    $brand = $row->item->brand->name ?? 'N/A';
                    $category = $row->item->category->name ?? 'N/A';

                    return '
            <a href="javascript:void(0)" class="text-dark fw-semibold">' . $itemName . '</a><br>
            <span class="badge rounded-pill bg-primary text-white me-1">' . $brand . '</span>
            <span class="badge rounded-pill bg-secondary text-white">' . $category . '</span>
        ';
                } else {
                    return '<span class="text-muted">N/A</span>';
                }
            })
            ->editColumn('requested_qty', function ($row) {

                $data = $row->requested_qty;
                $editUrl = route('item.production.edit', ['id' => $row->id]);

                return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';
            })
            ->editColumn('due_date', function ($row) {

                if ($row->purchaseOrder && $row->purchaseOrder->due_date) {
                    $data = ($row->purchaseOrder->due_date)?->format('d M Y');
                    $editUrl = route('item.production.edit', ['id' => $row->id]);

                    return '<a href="' . $editUrl . '" class="text-dark">
                                                   ' . $data . ' </a>';
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
            ->rawColumns(['item', 'customer', 'requested_qty', 'due_date',  'status'])
            ->make(true);
    }

    public function dispatchList(Request $request, $customerId)
    {
        $dispatches = Dispatch::where('customer_id', $customerId)
            ->with(['customer', 'ProductionItemMaster', 'purchaseOrder'])
            ->select('dispatches.*');

        if ($request->filled('status')) {
            $dispatches->where('status', $request->status);
        }

        return DataTables::of($dispatches)
            ->addColumn('item', function ($dispatch) {
                if ($dispatch->ProductionItemMaster && $dispatch->ProductionItemMaster->count() > 0) {
                    $html = '';
                    foreach ($dispatch->ProductionItemMaster as $production) {
                        if ($production->item) {
                            $itemName = $production->item->name ?? 'N/A';
                            $brand = $production->item->brand->name ?? 'N/A';
                            $category = $production->item->category->name ?? 'N/A';

                            $html .= '
                            <a href="javascript:void(0)" class="text-dark fw-semibold">' . $itemName . '</a><br>
                            <span class="badge rounded-pill bg-primary text-white me-1">' . $brand . '</span>
                            <span class="badge rounded-pill bg-secondary text-white mb-1">' . $category . '</span><br>';
                        }
                    }
                    return $html;
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('total_quantity', function ($dispatch) {
                // Sum all items in the related purchase order
                if ($dispatch->purchaseOrder) {
                    return $dispatch->purchaseOrder->items->sum('quantity');
                }
                return 0;
            })
            ->addColumn('quantity_from_production', function ($dispatch) {
                return $dispatch->ProductionItemMaster->sum('requested_qty');
            })
            ->addColumn('quantity_from_stock', function ($dispatch) {
                if ($dispatch->purchaseOrder) {
                    $totalPoQty = $dispatch->purchaseOrder->items->sum('quantity');
                    $producedQty = $dispatch->ProductionItemMaster->sum('requested_qty');
                    return max(0, $totalPoQty - $producedQty);
                }
                return 0;
            })
            ->addColumn('customer', function ($dispatch) {
                $firstName = $dispatch->customer->first_name ?? '';
                $lastName = $dispatch->customer->last_name ?? '';
                $fullName = trim($firstName . ' ' . $lastName);
                return $fullName !== '' ? $fullName : 'N/A';
            })
            ->addColumn('status', function ($row) {
                $statusClass = match ($row->status) {
                    'Pending' => 'bg-warning text-dark',
                    'Completed' => 'bg-success text-dark',
                    'Dispatched' => 'bg-warning text-dark',
                    'Dispatch Pending' => 'bg-warning text-white',
                    'Partial Dispatch' => 'bg-info text-dark',
                    default => 'bg-secondary text-white',
                };
                return '<span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase shadow-sm ' . $statusClass . '">' . $row->status . '</span>';
            })
            ->rawColumns(['item', 'customer', 'status'])
            ->make(true);
    }
}
