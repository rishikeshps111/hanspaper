<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Order;
use App\Models\Sale\SaleOrder;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Customer;
use App\Models\OrderPayment;
use App\Models\OrderedProduct;
use App\Traits\FormatNumber;
use App\Models\Items\ItemCategory;

use Illuminate\Support\Number;

use App\Models\Sale\Sale;
use App\Models\Sale\SaleReturn;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Party\Party;
use App\Models\Party\PartyTransaction;
use App\Models\Party\PartyPayment;
use App\Models\Expenses\Expense;
use App\Models\Items\ItemTransaction;
use App\Models\Items\ProductionItemMaster;
use App\Models\Dispatch\Dispatch;

class DashboardController extends Controller
{
    use formatNumber;


    public function index()
    {

        $pendingSaleOrders          = SaleOrder::whereDoesntHave('sale')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();
        $totalCompletedSaleOrders   = SaleOrder::whereHas('sale')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();

        $partyBalance               = $this->paymentReceivables();
        $totalPaymentReceivables    = $this->formatWithPrecision($partyBalance['receivable']);
        $totalPaymentPaybles        = $this->formatWithPrecision($partyBalance['payable']);

        $pendingPurchaseOrders          = PurchaseOrder::whereDoesntHave('purchase')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();
        $totalCompletedPurchaseOrders   = PurchaseOrder::whereHas('purchase')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();

        $totalCustomers = Party::where('party_type', 'customer')
                                                ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    // return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->count();

        $totalExpense         = Expense::when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->sum('grand_total');
        $totalExpense         = $this->formatWithPrecision($totalExpense);

        $recentInvoices       = Sale::when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                    return $query->where('created_by', auth()->user()->id);
                                                })
                                                ->orderByDesc('id')
                                                ->limit(10)
                                                ->get();

        $saleVsPurchase       = $this->saleVsPurchase();
        $trendingItems        = $this->trendingItems();
        
        $pendingDispatches = Dispatch::where('status', 'pending')
            ->count();
       $itemMasters = ProductionItemMaster::whereIn('status', ['Partial', 'Pending'])->count();
       
        $totalAssignPending = ProductionItemMaster::where('status', 'Assigning Pending')->count();
        $totalPending = ProductionItemMaster::where('status', 'Pending')->count();
        $totalPackingPending = ProductionItemMaster::where('status', 'Packing Pending')->count();
        $totalDispatchPending = Dispatch::whereIn('status', ['Pending', 'Dispatch Pending'])->count();
        $totalPartiallyDispatchedPending = Dispatch::where('status', 'Dispatched')->count();
                $totalPartial = ProductionItemMaster::where('status', 'Partial')->count();

        $categoryPendingProductions = ItemCategory::select(
            'item_categories.id',
            'item_categories.name'
        )
            ->leftJoin('items', 'items.item_category_id', '=', 'item_categories.id')
            ->leftJoin('production_item_masters', 'production_item_masters.item_id', '=', 'items.id')
            ->groupBy('item_categories.id', 'item_categories.name')
            ->selectRaw("
        SUM(CASE WHEN production_item_masters.status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN production_item_masters.status = 'Packing Pending' THEN 1 ELSE 0 END) as packing_pending_count,
        SUM(CASE WHEN production_item_masters.status = 'Assigning Pending' THEN 1 ELSE 0 END) as assigning_pending_count
    ")
            ->get();

        $inProgressProductions = ProductionItemMaster::query()
            ->where('status', 'In Progress')
            ->whereHas('activeRun')
            ->with([
                'item:id,name',
                'purchaseOrder:id,purchase_order_id,customer_id',
                'purchaseOrder.party:id,first_name,last_name',
                'activeRun.machine:id,machine_name',
                'activeRun.productionUser:id,full_name',
                'activeRun.reelStock.reel:id,code',
            ])
            ->withSum('productionLists as completed_quantity', 'quantity')
            ->orderByDesc(
                \App\Models\ProductionRun::select('started_at')
                    ->whereColumn('production_runs.production_id', 'production_item_masters.id')
                    ->where('production_runs.status', 'in_progress')
                    ->limit(1)
            )
            ->get();




        return view('dashboard', compact(
                                            'pendingSaleOrders',
                                            'pendingPurchaseOrders',

                                            'totalCompletedSaleOrders',
                                            'totalCompletedPurchaseOrders',

                                            'totalCustomers',
                                            'totalPaymentReceivables',
                                            'totalPaymentPaybles',
                                            'totalExpense',

                                            'saleVsPurchase',
                                            'trendingItems',
                                            'recentInvoices',
                                            'pendingDispatches',
                                            'itemMasters',
                                              
            'totalAssignPending',
            'totalPending',
            'totalPackingPending',
             'totalDispatchPending',
            'totalPartiallyDispatchedPending',
            'totalPartial',
             'categoryPendingProductions',
             'inProgressProductions'
                                        ));
    }

    public function inProgressProductionDetails(ProductionItemMaster $production): \Illuminate\Http\JsonResponse
    {
        $production->load([
            'item:id,name',
            'purchaseOrder:id,purchase_order_id,customer_id',
            'purchaseOrder.party:id,first_name,last_name',
            'activeRun.reelStock.reel',
            'activeRun.machine',
            'activeRun.productionUser',
            'activeRun.core',
            'productionLists.machine',
            'productionLists.producedBy',
            'productionLists.productionRun',
            'productionLists.core',
            'productionLists.reelStockUsage.stock.reel',
        ])->loadSum('productionLists as completed_quantity', 'quantity');

        $run = $production->activeRun;
        abort_unless($production->status === 'In Progress' && $run, 404, 'This production is no longer in progress.');

        $completed = (float) ($production->completed_quantity ?? 0);
        $current = (float) $run->production_quantity;
        $remaining = max(0, (float) $production->requested_qty - $completed - $current);
        $party = $production->purchaseOrder?->party;

        return response()->json([
            'work_order_id' => $production->purchaseOrder?->purchase_order_id ?? 'Not Available',
            'production_id' => $production->id,
            'customer' => trim(($party?->first_name ?? '') . ' ' . ($party?->last_name ?? '')) ?: 'Not Available',
            'product' => $production->item?->name ?? 'Not Available',
            'requested_quantity' => (float) $production->requested_qty,
            'completed_quantity' => $completed,
            'current_quantity' => $current,
            'remaining_quantity' => $remaining,
            'stock_code' => $run->reelStock?->stock_code ?? 'Not Available',
            'reel_code' => $run->reelStock?->reel?->code ?? 'Not Available',
            'machine' => $run->machine?->machine_name ?? 'Not Available',
            'production_user' => $run->productionUser?->full_name ?? 'Not Available',
            'core_code' => $run->core?->code ?? 'Not Available',
            'core_size' => $run->core ? (float) $run->core->size_mm : null,
            'core_quantity' => (int) $run->core_quantity,
            'output_roll_width' => (float) $run->output_roll_width,
            'roll_length' => (float) $run->roll_length,
            'started_at' => $run->started_at?->format('d M Y h:i a') ?? 'Not Available',
            'completed_runs' => $production->productionLists
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($entry) {
                    $usage = $entry->reelStockUsage;
                    return [
                        'quantity' => (float) $entry->quantity,
                        'stock_code' => $usage?->stock?->stock_code ?? 'Not Available',
                        'reel_code' => $usage?->stock?->reel?->code ?? 'Not Available',
                        'machine' => $entry->machine?->machine_name ?? 'Not Available',
                        'production_user' => $entry->producedBy?->full_name ?? 'Not Available',
                        'core_code' => $entry->core?->code ?? 'Not Available',
                        'core_size' => $entry->core ? (float) $entry->core->size_mm : null,
                        'core_quantity' => (int) ($entry->core_quantity ?? 0),
                        'output_roll_width' => $usage ? (float) $usage->output_roll_width : null,
                        'roll_length' => $usage ? (float) $usage->roll_length : null,
                        'completed_at' => $entry->productionRun?->finished_at?->format('d M Y h:i a')
                            ?? $entry->created_at?->format('d M Y h:i a')
                            ?? 'Not Available',
                    ];
                }),
            'track_url' => route('item.production.edit', ['id' => $production->id]),
        ]);
    }

    public function saleVsPurchase()
    {
        $labels = [];
        $sales = [];
        $purchases = [];

        $now = now();
        for ($i = 0; $i < 6; $i++) {
            $month = $now->copy()->subMonths($i)->format('M Y');
            $labels[] = $month;

            // Get value for this month, e.g. from database
            $sales[] = Sale::whereMonth('sale_date', $now->copy()->subMonths($i)->month)
                   ->whereYear('sale_date', $now->copy()->subMonths($i)->year)
                   ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                        return $query->where('created_by', auth()->user()->id);
                    })
                   ->count();

            $purchases[] = Purchase::whereMonth('purchase_date', $now->copy()->subMonths($i)->month)
                   ->whereYear('purchase_date', $now->copy()->subMonths($i)->year)
                   ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                        return $query->where('created_by', auth()->user()->id);
                    })
                   ->count();

        }

        $labels = array_reverse($labels);
        $sales = array_reverse($sales);
        $purchases = array_reverse($purchases);

        $saleVsPurchase = [];

        for($i = 0; $i < count($labels); $i++) {
          $saleVsPurchase[] = [
            'label'     => $labels[$i],
            'sales'     => $sales[$i],
            'purchases' => $purchases[$i],
          ];
        }

        return $saleVsPurchase;
    }

    public function trendingItems() : array
    {
        // Get top 4 trending items (adjust limit as needed)
        return ItemTransaction::query()
            ->select([
                'items.name',
                DB::raw('SUM(item_transactions.quantity) as total_quantity')
            ])
            ->join('items', 'items.id', '=', 'item_transactions.item_id')
            ->where('item_transactions.transaction_type', getMorphedModelName(Sale::class))
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('item_transactions.created_by', auth()->user()->id);
            })
            ->groupBy('item_transactions.item_id', 'items.name')
            ->orderByDesc('total_quantity')
            ->limit(4)
            ->get()
            ->toArray();
    }



    public function paymentReceivables(){
        // Retrieve opening balance from PartyTransaction
        $openingBalance = PartyTransaction::selectRaw('COALESCE(SUM(to_receive) - SUM(to_pay), 0) as opening_balance')
                                            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                                                return $query->where('created_by', auth()->user()->id);
                                            })
                                            ->first()
                                            ->opening_balance ?? 0;

        // Get total amount received from customers (Sale Adjustments)
        $partyPaymentReceiveSum = PartyPayment::where('payment_direction', 'receive')
            ->leftJoin('party_payment_allocations', 'party_payments.id', '=', 'party_payment_allocations.party_payment_id')
            ->leftJoin('payment_transactions', 'party_payment_allocations.payment_transaction_id', '=', 'payment_transactions.id')
            ->selectRaw('SUM(party_payments.amount) - COALESCE(SUM(payment_transactions.amount), 0) AS total_amount')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('party_payments.created_by', auth()->user()->id);
            })
            ->value('total_amount') ?? 0;

        // Get total amount paid to suppliers (Purchase Adjustments)
        $partyPaymentPaySum = PartyPayment::where('payment_direction', 'pay')
            ->leftJoin('party_payment_allocations', 'party_payments.id', '=', 'party_payment_allocations.party_payment_id')
            ->leftJoin('payment_transactions', 'party_payment_allocations.payment_transaction_id', '=', 'payment_transactions.id')
            ->selectRaw('SUM(party_payments.amount) - COALESCE(SUM(payment_transactions.amount), 0) AS total_amount')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('party_payments.created_by', auth()->user()->id);
            })
            ->value('total_amount') ?? 0;

        // Sale balance (grand_total - paid_amount)
        $saleBalance = Sale::selectRaw('coalesce(sum(grand_total - paid_amount), 0) as total')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        // Sale Return balance
        $saleReturnBalance = SaleReturn::selectRaw('coalesce(sum(grand_total - paid_amount), 0) as total')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        // Purchase balance
        $purchaseBalance = Purchase::selectRaw('coalesce(sum(grand_total - paid_amount), 0) as total')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        // Purchase Return balance
        $purchaseReturnBalance = PurchaseReturn::selectRaw('coalesce(sum(grand_total - paid_amount), 0) as total')
            ->when(auth()->user()->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        // Calculate balance for party
        $partyReceivable = $openingBalance + $partyPaymentReceiveSum + $saleBalance - $saleReturnBalance;
        $partyPayable = $partyPaymentPaySum + $purchaseBalance - $purchaseReturnBalance;

        return [
                'payable' => abs($partyPayable),
                'receivable' => abs($partyReceivable),
            ];
    }
    
     public function getCategoryItems(Request $request)
    {
        $categoryId = $request->category_id;
        $status = $request->status;

        $items = ProductionItemMaster::whereRelation('item', 'item_category_id', $categoryId)
            ->where('status', $status)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->item->name,
                    'brand' => $item->item->brand->name ?? '-',
                    'requested_qty' => $item->requested_qty ?? '-',
                    'requestedBy' => $item->purchaseOrder->party->first_name ?? '-',
                    'track_url' => route('item.production.edit', $item->id),

                ];
            });

        return response()->json(['items' => $items]);
    }
}
