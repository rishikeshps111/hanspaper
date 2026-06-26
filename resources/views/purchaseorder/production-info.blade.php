<style>
    /* Ensures no horizontal scroll inside modal */
    .modal-body {
        overflow-x: hidden;
    }

    table.table td,
    table.table th {
        white-space: normal !important;
        /* allow text wrapping */
        word-break: break-word;
        /* wrap long text */
        vertical-align: middle;
    }

    table.table {
        width: 100%;
        table-layout: fixed;
        /* ensures even column sizing */
    }
</style>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <tbody>
            <!-- Basic Info -->
            <tr class="table-primary">
                <th colspan="2" class="text-start">Production Item Info</th>
            </tr>
            <tr>
                <th>Item Name</th>
                <td>{{ $item->item->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Item Code</th>
                <td>{{ $item->item->item_code ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Production Type</th>
                <td>{{ $item->production_type ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Requested Quantity</th>
                <td>{{ number_format($item->requested_qty ?? 0) }}</td>
            </tr>
            @php
                $productionCompletedQty = $item->productionLists->sum('quantity') ?? 0;
                $packingCompletedQty = $item->packingLists->sum('quantity') ?? 0;
                $requestedQty = $item->requested_qty ?? 0;
                $productionRemainingQty = max($requestedQty - $productionCompletedQty, 0);
                $packingRemainingQty = max($requestedQty - $packingCompletedQty, 0);
            @endphp
            <tr>
                <th>Production Completed Quantity</th>
                <td>{{ number_format($productionCompletedQty) }}</td>
            </tr>
            <tr>
                <th>Production Remaining Quantity</th>
                <td>{{ number_format($productionRemainingQty) }}</td>
            </tr>
            <tr>
                <th>Packing Completed Quantity</th>
                <td>{{ number_format($packingCompletedQty) }}</td>
            </tr>
            <tr>
                <th>Packing Remaining Quantity</th>
                <td>{{ number_format($packingRemainingQty) }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span class="badge bg-success">{{ $item->status ?? 'N/A' }}</span></td>
            </tr>


            <!-- Assigned Users -->
            <tr class="table-primary">
                <th colspan="2" class="text-start">Assigned Users and Machine</th>
            </tr>
            <tr>
                <th>Production User</th>
                <td>{{ $item->assignedProductionUser->full_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Packing User</th>
                <td>{{ $item->assignedPackingUser->full_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Assigned Machine</th>
                <td>{{ $item->assignedMachine->machine_name ?? 'N/A' }}</td>
            </tr>

            <!-- Remarks -->
            <tr class="table-primary">
                <th colspan="2" class="text-start">Remarks</th>
            </tr>
            <tr>
                <th>Production Remarks</th>
                <td>{{ $item->production_remarks ?? '-' }}</td>
            </tr>
            <tr>
                <th>Packing Remarks</th>
                <td>{{ $item->packing_remarks ?? '-' }}</td>
            </tr>
            <tr>
                <th>Dispatch Remarks</th>
                <td>{{ $item->dispatch_remarks ?? '-' }}</td>
            </tr>

            <!-- Production Lists -->
            <tr class="table-primary">
                <th colspan="2" class="text-start">Production Lists</th>
            </tr>
            <tr>
                <td colspan="2">
                    @if(optional($item->productionLists)->count())
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sl NO</th>
                                    <th>Quantity</th>
                                    <th>Produced By</th>
                                    <th>Machine</th>
                                    <th>Real</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item->productionLists as $index => $prod)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ number_format($prod->quantity) }}</td>
                                        <td>{{ $prod->producedBy->full_name ?? 'N/A' }}</td>
                                        <td>{{ $prod->machine->machine_name ?? 'N/A' }}</td>
                                        <td>{{ $prod->real->real_no ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($prod->created_at)->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mb-0">No production records found.</p>
                    @endif
                </td>
            </tr>

            <!-- Packing Lists -->
            <tr class="table-primary">
                <th colspan="2" class="text-start">Packing Lists</th>
            </tr>
            <tr>
                <td colspan="2">
                    @if(optional($item->packingLists)->count())
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sl NO</th>
                                    <th>Quantity</th>
                                    <th>Packed By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item->packingLists as $index => $pack)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ number_format($pack->quantity) }}</td>
                                        <td>{{ $pack->packedBy->full_name ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($pack->created_at)->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mb-0">No packing records found.</p>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>