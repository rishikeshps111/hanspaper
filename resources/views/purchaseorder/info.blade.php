@extends('layouts.app')
@section('title', __('Work Order Details'))
@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Work Orders', 'Work Order Details']" />
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Work Order Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-2">
                                <div class="col-md-3">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Work Order
                                    </label>
                                    <div class="input-group">
                                        <br><b>{{ $order->purchase_order_id ?? '' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Customer
                                    </label>
                                    <div class="input-group">
                                        <br><b>{{ $order->party->first_name ?? '' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Representative
                                    </label>
                                    <div class="input-group">
                                        <br><b>{{ $order->representative->full_name ?? 'No Representative' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Ordered Date
                                    </label>
                                    <div class="input-group">
                                        <br><b>{{ \Carbon\Carbon::parse($order->po_date)->format('d F Y') }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Due Date
                                    </label>
                                    <div class="input-group">
                                        <br><b>{{ \Carbon\Carbon::parse($order->due_date)->format('d F Y') }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Created By
                                    </label>
                                    <div class="input-group">
                                        <br><b>{{ $order->user->first_name ?? 'N/A' }}
                                            {{ $order->user->last_name ?? '' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Created Gap
                                    </label>
                                    <div class="input-group">
                                        <br><b>{!! $order->gap_in_days_badge !!}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Over ALL Status
                                    </label>
                                    @php
                                        $status = $order->purchase_order_status;
                                        $badgeClass = match ($status) {
                                            'Pending' => 'bg-warning',
                                            'Processing' => 'bg-info',
                                            'Cancelled' => 'bg-danger',
                                            'Completed' => 'bg-success',
                                            'Production' => 'bg-primary',
                                            'Dispatched' => 'bg-secondary',
                                            'Ready to Dispatch' => 'bg-dark',
                                            'Dispatch Pending' => 'bg-warning text-dark',
                                            'Partial Dispatch' => 'bg-info text-dark',
                                            default => 'bg-light text-dark',
                                        };
                                    @endphp

                                    <div class="input-group">
                                        <span class="badge {{ $badgeClass }}"
                                            style="font-size: 0.9rem; padding: 8px 12px; border-radius: 8px;">
                                            {{ $status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-4">
                                    <h5>Customer Details</h5>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Name
                                    </label>
                                    <div class="input-group">
                                        <br><b>
                                            {{ $order->party->first_name }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Email
                                    </label>
                                    <div class="input-group">
                                        <br><b>
                                            {{ $order->party->email ?? 'N/A' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Phone
                                    </label>
                                    <div class="input-group">
                                        <br><b>
                                            {{ $order->party->phone ?? 'N/A' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Billing Address
                                    </label>
                                    <div class="input-group">
                                        <br><b>
                                            {{ $order->party->billing_address ?? 'N/A' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Shipping Address
                                    </label>
                                    <div class="input-group">
                                        <br><b>{{ $order->party->shipping_address ?? 'N/A' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label class="form-label " for="item_id" id="" data-name="">
                                        Party Type
                                    </label>
                                    <div class="input-group">
                                        <br><b>{{ $order->party->party_type ?? 'N/A' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-4">
                                    <h5>Worke Order Items</h5>
                                </div>
                                <div class="col-md-12 mt-4">
                                    @if ($order->items->count() > 0)
                                        <div class="table-responsive">
                                            <div class="table-responsive">
                                                <table class="table table-bordered align-middle text-center table-sm">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Sl No</th>
                                                            <th>Item Code</th>
                                                            <th>Product Name</th>
                                                            <th>Brand</th>
                                                            <th>Category</th>
                                                            <th>Total Qty</th>
                                                            <th>From Production</th>
                                                            <th>From Stock</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody style="word-wrap: break-word; white-space: normal;">
                                                        @foreach ($order->items as $index => $item)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $item->product->item_code ?? 'N/A' }}</td>
                                                                <td class="text-wrap">{{ $item->product->name ?? 'N/A' }}
                                                                </td>
                                                                <td>{{ $item->product->brand->name ?? 'N/A' }}</td>
                                                                <td>{{ $item->product->Category->name ?? 'N/A' }}</td>
                                                                <td>{{ number_format($item->quantity) }}</td>
                                                                @php
                                                                    $productionItem = App\Models\Items\ProductionItemMaster::where(
                                                                        'purchase_order_id',
                                                                        $item->purchase_order_id,
                                                                    )
                                                                        ->where('item_id', $item->product_id)
                                                                        ->first();
                                                                @endphp
                                                                <td>{{ number_format(optional($productionItem)->requested_qty ?? 0) }}
                                                                </td>
                                                                <td>{{ number_format($item->quantity - (optional($productionItem)->requested_qty ?? 0)) }}
                                                                </td>

                                                                <td>
                                                                    @php
                                                                        $status = $item->status ?? 'N/A';
                                                                        $badgeClass = match ($status) {
                                                                            'Push To Production' => 'bg-primary',
                                                                            'Completed' => 'bg-success',
                                                                            'Cancelled' => 'bg-danger',
                                                                            'Pending' => 'bg-warning text-dark',
                                                                            default => 'bg-secondary',
                                                                        };
                                                                    @endphp
                                                                    <span
                                                                        class="badge {{ $badgeClass }}">{{ $status }}</span>
                                                                </td>
                                                                <td>
                                                                    @if ($productionItem)
                                                                        <a href="javascript:void(0)"
                                                                            class="btn btn-sm btn-primary view-production"
                                                                            title="View Details"
                                                                            data-id="{{ $productionItem->id }}"> View
                                                                        </a>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted">No items found for this order.</p>
                                    @endif
                                </div>
                                <div class="col-md-12 mt-4">
                                    <h5>Dispatch Details</h5>
                                </div>
                                <div class="col-12">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Sl</th>
                                                <th>Products</th>
                                                <th>Customer Req Qty</th>
                                                <th>Work Comp Qty</th>
                                                <th>Dispatched Qty</th>
                                                <th>Rem to be Dispatched</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @if ($dispatch && $dispatch->purchaseOrder && $dispatch->purchaseOrder->items->count() > 0)
                                                @foreach ($dispatch->purchaseOrder->items as $orderItem)
                                                    @if ($dispatch->id == ($orderItem->dispatches_id ?? null))
                                                        @php
                                                            $reqqty = 0;
                                                            $totalreqqty = 0;
                                                            $balance_dispatched = 0;
                                                            $original_qty = $orderItem->quantity ?? 0;

                                                            if (!empty($mdispatch)) {
                                                                foreach ($mdispatch as $md) {
                                                                    if (
                                                                        ($md->purchase_order_items_id ?? null) ==
                                                                        ($orderItem->id ?? null)
                                                                    ) {
                                                                        $reqqty = $md->required_qty ?? 0;
                                                                        $totalreqqty += $reqqty;
                                                                    }
                                                                }
                                                            }

                                                            $balance_dispatched = $original_qty - $totalreqqty;
                                                            $status =
                                                                $balance_dispatched != 0
                                                                    ? 'Not Fully Dispatched'
                                                                    : 'Dispatched';
                                                        @endphp

                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $orderItem->product->name ?? 'Product Not Found' }}</td>
                                                            <td>{{ $original_qty }}</td>
                                                            <td>{{ $original_qty }}</td>
                                                            <td>{{ $totalreqqty }}</td>
                                                            <td>{{ $balance_dispatched }}</td>
                                                            <td>{{ $status }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td>
                                                        <p class="text-muted">No Data Available.</p>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>

    <!-- Production Item Modal -->
    <div class="modal fade" id="productionModal" tabindex="-1" aria-labelledby="productionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productionModalLabel">Production Item Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="productionModalBody" class="p-2 text-center text-muted">
                        Loading...
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).on('click', '.view-production', function() {
            let id = $(this).data('id');

            // Show modal immediately
            $('#productionModal').modal('show');
            $('#productionModalBody').html('<div class="text-center py-4 text-muted">Loading...</div>');

            // Fetch details via AJAX
            $.ajax({
                url: '{{ route('purchaseorder.produaction.details') }}', // 👈 create this route in your web.php
                method: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $('#productionModalBody').html(response);
                },
                error: function() {
                    $('#productionModalBody').html(
                        '<div class="text-danger">Failed to load details.</div>');
                }
            });
        });
    </script>
@endsection
