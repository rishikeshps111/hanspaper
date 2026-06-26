<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Production Print</title>
    <style>
        @media print {
            @page {
                size: A5 portrait;
                margin: 8mm;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #000;
                line-height: 1.3;
                margin: 0;
                padding: 0;
            }

            .container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .section {
                margin-bottom: 6px;
                padding-bottom: 5px;
                break-inside: avoid;
            }

            .section-title {
                font-weight: bold;
                font-size: 11px;
                margin-bottom: 4px;
                padding-bottom: 2px;
                border-bottom: 1px solid #ddd;
                color: #333;
            }

            .row {
                display: flex;
                margin-bottom: 3px;
            }

            .label {
                font-weight: bold;
                width: 70px;
                color: #555;
                flex-shrink: 0;
            }

            .value {
                flex: 1;
            }

            h3 {
                text-align: center;
                font-size: 14px;
                margin: 0 0 8px 0;
                padding-bottom: 5px;
                border-bottom: 2px solid #333;
                color: #333;
                grid-column: 1 / -1;
            }

            .full-width {
                grid-column: 1 / -1;
            }

            .remarks {
                margin-top: 2px;
                padding: 3px;
                background-color: #f9f9f9;
                border-left: 2px solid #ddd;
                font-size: 9px;
            }

            .status-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 5px;
            }

            .status-item {
                margin-bottom: 3px;
            }
        }

        body {
            margin: 5px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="container">
        <h3>Production and Packing Summary</h3>

        <div class="section">
            <div class="section-title">Order Info</div>
            <div class="row">
                <span class="label">Customer:</span>
                <span class="value">{{ $productionItemMaster->purchaseOrder->party->first_name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">PO ID:</span>
                <span class="value">{{ $productionItemMaster->purchaseOrder->purchase_order_id ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Item:</span>
                <span class="value">{{ $productionItemMaster->item->name ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Product Details</div>
            <div class="row">
                <span class="label">Brand:</span>
                <span class="value">{{ $productionItemMaster->item->brand->name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Category:</span>
                <span class="value">{{ $productionItemMaster->item->category->name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Product Type:</span>
                <span class="value">{{ $productionItemMaster->production_type ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Dates</div>
            <div class="row">
                <span class="label">Ordered:</span>
                <span
                    class="value">{{ optional($productionItemMaster->purchaseOrder)->po_date ? \Carbon\Carbon::parse($productionItemMaster->purchaseOrder->po_date)->format('d M Y') : 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Due Date:</span>
                <span
                    class="value">{{ optional($productionItemMaster->purchaseOrder)->due_date ? \Carbon\Carbon::parse($productionItemMaster->purchaseOrder->due_date)->format('d M Y') : 'N/A' }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Quantities</div>
            <div class="row">
                <span class="label">Requested:</span>
                <span class="value">{{ $productionItemMaster->requested_qty ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Produced:</span>
                <span class="value">{{ $productionItemMaster->productionLists->sum('quantity') }}</span>
            </div>
            <div class="row">
                <span class="label">Packed:</span>
                <span class="value">{{ $productionItemMaster->packingLists->sum('quantity') }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Progress</div>
            <div class="row">
                <span class="label">Production Remain:</span>
                <span
                    class="value">{{ $productionItemMaster->requested_qty - $productionItemMaster->productionLists->sum('quantity') }}</span>
            </div>
            <div class="row">
                <span class="label">Packing Remain:</span>
                <span
                    class="value">{{ $productionItemMaster->requested_qty - $productionItemMaster->packingLists->sum('quantity') }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Resources</div>
            <div class="row">
                <span class="label">Machine:</span>
                <span class="value">{{ $productionItemMaster->assignedMachine->machine_name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Production Staff:</span>
                <span class="value">{{ $productionItemMaster->assignedProductionUser->full_name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Packing Staff:</span>
                <span class="value">{{ $productionItemMaster->assignedPackingUser->full_name ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="section full-width">
            <div class="section-title">Status</div>
            <div class="status-grid">
                <div class="status-item">
                    <span class="label">Overall:</span>
                    <span class="value">{{ $productionItemMaster->status ?? 'N/A' }}</span>
                </div>
                <div class="status-item">
                    <span class="label">Production:</span>
                    <span class="value">{{ $productionItemMaster->production_status ?? 'N/A' }}</span>
                </div>
                <div class="status-item">
                    <span class="label">Packing:</span>
                    <span class="value">{{ $productionItemMaster->packing_status ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="section full-width">
            <div class="section-title">Remarks</div>
            <div class="row">
                <span class="label">Production:</span>
                <span class="value">
                    <div class="remarks">{{ $productionItemMaster->production_remarks ?? 'N/A' }}</div>
                </span>
            </div>
            <div class="row">
                <span class="label">Packing:</span>
                <span class="value">
                    <div class="remarks">{{ $productionItemMaster->packing_remarks ?? 'N/A' }}</div>
                </span>
            </div>
            <div class="row">
                <span class="label">Dispatch:</span>
                <span class="value">
                    <div class="remarks">{{ $productionItemMaster->dispatch_remarks ?? 'N/A' }}</div>
                </span>
            </div>
        </div>
    </div>
</body>

</html>
