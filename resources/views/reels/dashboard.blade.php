@extends('layouts.app')
@section('title', 'Reel Dashboard')

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        #reelDashboardTable th,
        #reelDashboardTable td {
            white-space: nowrap;
            vertical-align: middle
        }

        #reelDashboardTable {
            width: max-content !important;
            min-width: 100%;
            table-layout: auto
        }

        #reelDashboardTable_wrapper {
            width: max-content;
            min-width: 100%
        }

        #reelDashboardTable thead tr:first-child th:nth-child(1),
        #reelDashboardTable td:nth-child(1),
        #reelDashboardTable thead tr:first-child th:nth-child(2),
        #reelDashboardTable td:nth-child(2) {
            min-width: 140px
        }

        #reelDashboardTable th,
        #reelDashboardTable td {
            min-width: 82px
        }

        #reelDashboardTable thead th {
            background: #34495e !important;
            color: #fff !important;
            text-align: center;
            border-color: #536b82;
            font-size: 11px
        }

        #reelDashboardTable .reel-group-header {
            font-size: 12px;
            font-weight: 700
        }

        #reelDashboardTable tbody tr:nth-child(odd) td {
            background: #fff
        }

        #reelDashboardTable tbody tr:nth-child(even) td {
            background: #eef1f4
        }

        #reelDashboardTable .dashboard-stock-details,
        #reelDashboardTable .dashboard-stock-details:hover,
        #reelDashboardTable .dashboard-stock-details:focus {
            text-decoration: none !important
        }

        /* #dashboardStocksModal .modal-content {
            overflow: hidden
        }

        #dashboardStocksModal .modal-body {
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden
        }

        #dashboardStocksModal .dashboard-stocks-table-wrap {
            flex: 1;
            min-height: 0;
            overflow: hidden
        }

        #dashboardStocksModal .dataTables_wrapper {
            height: 100%;
            display: flex;
            flex-direction: column;
            min-height: 0;
            position: relative;
            padding-bottom: 48px
        }

        #dashboardStocksModal .dataTables_wrapper > .row {
            flex: 0 0 auto
        }

        #dashboardStocksModal .dataTables_wrapper > .row:last-child {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            min-height: 42px;
            margin: 0;
            padding-top: 8px;
            background: #fff;
            z-index: 3
        }

        #dashboardStocksModal .dataTables_scroll {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column
        }

        #dashboardStocksModal .dataTables_scrollBody {
            flex: 1 1 auto;
            height: auto !important;
            min-height: 0;
            max-height: none !important;
            overflow-y: auto !important
        }

        #dashboardStocksModal #dashboardStocksTable th,
        #dashboardStocksModal #dashboardStocksTable td {
            padding: .4rem .5rem
        } */

        #dashboardLoader {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, .94);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center
        }

        #dashboardLoader .loader-box {
            text-align: center;
            color: #34495e
        }

        #dashboardLoader .spinner-border {
            width: 3.25rem;
            height: 3.25rem
        }
    </style>
@endsection

@section('content')
    <div id="dashboardLoader">
        <div class="loader-box">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="fw-semibold mt-3">Loading Reel stock dashboard...</div>
        </div>
    </div>

    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reels', 'Dashboard']" />
            @include('layouts.session')
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0 mt-2">REEL STOCK DASHBOARD</h5>
                    <div class="d-flex gap-2">
                        <button type="button" id="printReelDashboard" class="btn btn-dark">
                            <i class="bx bx-printer me-1"></i>Print
                        </button>
                        <button type="button" class="btn btn-success add-reel-stock">
                            <i class="bx bx-package me-1"></i>Add Stock
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label class="form-label">Brand</label>
                            <select id="dashboardBrand" class="form-select dashboard-filter w-100"
                                data-placeholder="All Brands">
                                <option value=""></option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Reel Type</label>
                            <select id="dashboardType" class="form-select dashboard-filter w-100"
                                data-placeholder="All Reel Types">
                                <option value=""></option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">GSM</label>
                            <select id="dashboardGsm" class="form-select dashboard-filter w-100" data-placeholder="All GSM">
                                <option value=""></option>
                                @foreach ($gsms as $gsm)
                                    <option value="{{ $gsm->id }}">{{ $gsm->gsm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Width (mm)</label>
                            <input type="number" id="dashboardWidth" class="form-control dashboard-number-filter" min="0.01"
                                step="0.01" placeholder="All Widths">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Length (m)</label>
                            <input type="number" id="dashboardLength" class="form-control dashboard-number-filter"
                                min="0.01" step="0.01" placeholder="All Lengths">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="resetDashboardFilters" class="btn btn-outline-secondary w-100">
                                <i class="bx bx-reset me-1"></i>Reset
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="reelDashboardTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2">Brand</th>
                                    <th rowspan="2">Reel Type</th>
                                    <th rowspan="2">GSM</th>
                                    <th rowspan="2">Width (mm)</th>
                                    <th rowspan="2">Length (m)</th>
                                    <th class="reel-group-header" colspan="{{ $warehouses->count() + 1 }}">Full Reels</th>
                                    <th class="reel-group-header" colspan="{{ $warehouses->count() + 1 }}">Bit Reels</th>
                                </tr>
                                <tr>
                                    @foreach ($warehouses as $warehouse)
                                        <th class="{{ $loop->first ? 'reel-group-start' : '' }}">{{ $warehouse->name }}
                                        </th>
                                    @endforeach
                                    <th>Total</th>
                                    @foreach ($warehouses as $warehouse)
                                        <th class="{{ $loop->first ? 'reel-group-start' : '' }}">{{ $warehouse->name }}
                                        </th>
                                    @endforeach
                                    <th>Total</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dashboardStocksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reel Stock - <span id="dashboardStocksReelCode"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" id="dashboardSaleSelected" class="btn btn-success" disabled><i
                                class="bx bx-cart me-1"></i>Sale</button>
                        <button type="button" id="dashboardTransferSelected" class="btn btn-primary d-none" disabled><i
                                class="bx bx-transfer me-1"></i>Transfer</button>
                        <button type="button" id="dashboardPrintSelected" class="btn btn-dark" disabled><i
                                class="bx bx-barcode me-1"></i>Print Selected</button>
                        <div class="ms-auto d-flex align-items-center gap-3">
                            <label class="d-flex align-items-center gap-2 mb-0 text-nowrap">Rows per page
                                <select id="dashboardStocksPerPage" class="form-select form-select-sm" style="width:80px">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </label>
                            <span class="text-muted"><span id="dashboardSelectedCount">0</span> selected</span>
                        </div>
                    </div>
                    <div id="dashboardSalePanel" class="card border-success d-none mb-3">
                        <div class="card-body">
                            <div class="alert alert-danger d-none dashboard-operation-errors"></div>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3"><label class="form-label">Customer <span
                                            class="text-danger">*</span></label><select id="dashboardSaleCustomer"
                                        class="form-select w-100" data-placeholder="Select Customer">
                                        <option value=""></option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">
                                                {{ trim($customer->first_name . ' ' . $customer->last_name) }}{{ $customer->mobile ? ' - ' . $customer->mobile : '' }}
                                            </option>
                                        @endforeach
                                    </select></div>
                                <div class="col-md-2"><label class="form-label">Sale Date <span
                                            class="text-danger">*</span></label><input type="date" id="dashboardSaleDate"
                                        class="form-control" value="{{ now()->format('Y-m-d') }}"></div>
                                <div class="col-md-2"><small class="text-muted">Selling Price</small>
                                    <div class="fs-5 fw-bold" id="dashboardSaleSellingPrice">0.00</div>
                                </div>
                                <div class="col-md-1"><small class="text-muted">Item Count</small>
                                    <div class="fs-5 fw-bold" id="dashboardSaleItemCount">0</div>
                                </div>
                                <div class="col-md-2"><small class="text-muted">Total Amount</small>
                                    <div class="fs-5 fw-bold" id="dashboardSaleTotal">0.00</div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-grid gap-2">
                                        <button type="button" id="confirmDashboardSale" class="btn btn-success">Confirm
                                            Sale</button>
                                        <button type="button"
                                            class="btn btn-outline-secondary cancel-dashboard-operation">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="dashboardTransferPanel" class="card border-primary d-none mb-3">
                        <div class="card-body">
                            <div class="alert alert-danger d-none dashboard-operation-errors"></div>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-8"><label class="form-label">Transfer To <span
                                            class="text-danger">*</span></label><select id="dashboardTransferDestination"
                                        class="form-select w-100" data-placeholder="Select Destination Warehouse">
                                        <option value=""></option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select></div>
                                <div class="col-md-4">
                                    <div class="d-flex gap-2">
                                        <button type="button" id="confirmDashboardTransfer"
                                            class="btn btn-primary flex-fill">Confirm Transfer</button>
                                        <button type="button"
                                            class="btn btn-outline-secondary cancel-dashboard-operation">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stocks-table-wrap">
                        <table id="dashboardStocksTable" class="table table-striped table-bordered align-middle w-100">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="dashboardSelectAllStocks" class="form-check-input">
                                    </th>
                                    <th>Sl No.</th>
                                    <th>Stock Code</th>
                                    <th>Actual Code</th>
                                    <th>Provider</th>
                                    <th>Warehouse</th>
                                    <th>Stock Added Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary px-5"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @include('reels.stock._bulk_modal')
@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/libraries/barcode-lib/bwip-js-min.js') }}"></script>
    <script>
        $(function () {
            $('.dashboard-filter').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true
            });

            const columns = [{
                data: 'brand_name',
                orderable: false,
                searchable: false
            },
            {
                data: 'type_name',
                orderable: false,
                searchable: false
            },
            {
                data: 'gsm_value',
                orderable: false,
                searchable: false
            },
            {
                data: 'width',
                orderable: false,
                searchable: false
            },
            {
                data: 'length',
                orderable: false,
                searchable: false
            },
            @foreach ($warehouses as $warehouse)
                {
                        data: 'warehouse_{{ $warehouse->id }}_full',
                        orderable: false,
                        searchable: false,
                        className: 'text-center {{ $loop->first ? 'reel-group-start' : '' }}'
                },
            @endforeach
            {
                data: 'full_total',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            @foreach ($warehouses as $warehouse)
                {
                        data: 'warehouse_{{ $warehouse->id }}_bit',
                        orderable: false,
                        searchable: false,
                        className: 'text-center {{ $loop->first ? 'reel-group-start' : '' }}'
                },
            @endforeach
            {
                data: 'bit_total',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
            ];

            $('#reelDashboardTable')
                .on('preXhr.dt', () => {
                    $('#printReelDashboard').prop('disabled', true);
                    $('#dashboardLoader').stop(true, true).fadeIn(100);
                })
                .on('draw.dt', () => {
                    $('#printReelDashboard').prop('disabled', false);
                    $('#dashboardLoader').fadeOut(200);
                });

            const dashboardTable = $('#reelDashboardTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: false,
                info: false,
                lengthChange: false,
                autoWidth: false,
                order: [],
                ajax: {
                    url: @json(route('reels.dashboard.data', [], false)),
                    data: request => {
                        request.reel_brand_id = $('#dashboardBrand').val();
                        request.reel_type_id = $('#dashboardType').val();
                        request.reel_gsm_id = $('#dashboardGsm').val();
                        request.width = $('#dashboardWidth').val();
                        request.length = $('#dashboardLength').val();
                    },
                    error: () => {
                        $('#printReelDashboard').prop('disabled', false);
                        $('#dashboardLoader').fadeOut(150);
                        Swal.fire('Unable to load dashboard', 'Please refresh the page and try again.',
                            'error');
                    }
                },
                columns,
                initComplete: () => $('#dashboardLoader').fadeOut(200)
            });

            $('.dashboard-filter').on('change', () => dashboardTable.ajax.reload());
            let numberFilterTimer;
            $('.dashboard-number-filter').on('input', function () {
                clearTimeout(numberFilterTimer);
                numberFilterTimer = setTimeout(() => dashboardTable.ajax.reload(), 350);
            });
            $('#resetDashboardFilters').on('click', function () {
                $('.dashboard-filter').val('').trigger('change.select2');
                $('.dashboard-number-filter').val('');
                dashboardTable.ajax.reload();
            });

            $('#printReelDashboard').on('click', function () {
                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    Swal.fire('Popup Blocked', 'Please allow popups to print the Reel Dashboard.', 'warning');
                    return;
                }

                const table = $('#reelDashboardTable').clone();
                table.removeAttr('id style').find('button').each(function () {
                    $(this).replaceWith($('<span>').text($(this).text().trim()));
                });

                const filterValues = [
                    ['Brand', $('#dashboardBrand option:selected').text().trim()],
                    ['Reel Type', $('#dashboardType option:selected').text().trim()],
                    ['GSM', $('#dashboardGsm option:selected').text().trim()],
                    ['Width', $('#dashboardWidth').val() ? `${$('#dashboardWidth').val()} mm` : ''],
                    ['Length', $('#dashboardLength').val() ? `${$('#dashboardLength').val()} m` : '']
                ].filter(([, value]) => value);
                const filterText = filterValues.length ? filterValues.map(([label, value]) =>
                    `${label}: ${value}`).join(' | ') : 'All Reels';
                const safeFilterText = $('<div>').text(filterText).html();

                printWindow.document.write(`<!doctype html>
                    <html><head><title>Reel Stock</title>
                    <style>
                        @page{size:landscape;margin:8mm}
                        *{box-sizing:border-box}
                        body{margin:0;font-family:Arial,sans-serif;color:#212529;-webkit-print-color-adjust:exact;print-color-adjust:exact}
                        h2{margin:0 0 5px;text-align:center;font-size:18px}
                        .filters{margin:0 0 12px;text-align:center;font-size:10px;color:#555}
                        table{width:100%;border-collapse:collapse;table-layout:auto;font-size:8px}
                        th,td{border:1px solid #536b82;padding:4px 5px;white-space:nowrap;vertical-align:middle;text-align:center}
                        thead th{background:#34495e!important;color:#fff!important;font-weight:700}
                        tbody tr:nth-child(odd) td{background:#fff!important}
                        tbody tr:nth-child(even) td{background:#eef1f4!important}
                        tbody td:first-child,tbody td:nth-child(2){text-align:left}
                    </style></head><body>
                    <h2>REEL STOCK</h2>
                    <div class="filters">${safeFilterText}</div>
                    ${table.prop('outerHTML')}
                    <script>window.addEventListener('load',()=>setTimeout(()=>window.print(),200));<\/script>
                    </body></html>`);
                printWindow.document.close();
            });

            $(document).on('reel:stock-added reel:created', () => dashboardTable.ajax.reload());

            const stocksModal = new bootstrap.Modal(document.getElementById('dashboardStocksModal'));
            $('#dashboardSaleCustomer,#dashboardTransferDestination').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                dropdownParent: $('#dashboardStocksModal')
            });
            let stocksTable = null;
            let stockContext = {};
            let operationMode = null;
            const selectedDashboardStocks = new Map();
            const selectedStockIds = () => Array.from(selectedDashboardStocks.keys());
            const updateDashboardStockActions = () => {
                const stocks = Array.from(selectedDashboardStocks.values());
                const allFull = stocks.length > 0 && stocks.every(stock => stock.status === 'full');
                $('#dashboardSelectedCount').text(stocks.length);
                $('#dashboardSaleSelected').prop('disabled', !allFull);
                $('#dashboardTransferSelected').prop('disabled', stocks.length === 0);
                $('#dashboardPrintSelected').prop('disabled', stocks.length === 0 || operationMode !== null);
                $('.dashboard-stock-action').prop('disabled', operationMode !== null);
                $('#dashboardSaleSellingPrice').text(stocks.length ? stocks[0].price.toFixed(2) : '0.00');
                $('#dashboardSaleItemCount').text(stocks.length);
                $('#dashboardSaleTotal').text(stocks.reduce((sum, stock) => sum + stock.price, 0).toFixed(2));
                const boxes = $('.dashboard-stock-checkbox');
                $('#dashboardSelectAllStocks').prop('checked', boxes.length > 0 && boxes.toArray().every(box =>
                    box.checked));
            };
            const clearDashboardStockSelection = () => {
                selectedDashboardStocks.clear();
                $('.dashboard-stock-checkbox,#dashboardSelectAllStocks').prop('checked', false);
                updateDashboardStockActions();
            };
            const showOperationErrors = (xhr, panel) => {
                const errors = xhr.responseJSON?.errors;
                const messages = errors ? Object.values(errors).flat() : [xhr.responseJSON?.message ||
                    'Unable to complete this operation.'
                ];
                $(panel).find('.dashboard-operation-errors').html(messages.map(message =>
                    `<div>${$('<div>').text(message).html()}</div>`).join('')).removeClass('d-none');
            };
            const setOperationMode = mode => {
                operationMode = mode;
                $('#dashboardSalePanel').toggleClass('d-none', mode !== 'sale');
                $('#dashboardTransferPanel').toggleClass('d-none', mode !== 'transfer');
                updateDashboardStockActions();
            };

            $(document).on('click', '.dashboard-stock-details', function () {
                stockContext = {
                    reelId: this.dataset.reelId,
                    warehouseId: this.dataset.warehouseId,
                    status: this.dataset.status
                };
                $('#dashboardStocksReelCode').text(this.dataset.reelCode);
                clearDashboardStockSelection();
                setOperationMode(null);
                $('.dashboard-operation-errors').addClass('d-none').empty();
                $('#dashboardSaleSelected').toggleClass('d-none', stockContext.status === 'bit');
                $('#dashboardTransferSelected').toggleClass('d-none', !stockContext.warehouseId);
                $('#dashboardTransferDestination option').prop('disabled', false);
                $(`#dashboardTransferDestination option[value="${stockContext.warehouseId}"]`).prop(
                    'disabled', true);
                $('#dashboardTransferDestination,#dashboardSaleCustomer').val(null).trigger('change');

                if (stocksTable) stocksTable.destroy();
                stocksTable = $('#dashboardStocksTable').DataTable({
                    processing: true,
                    serverSide: true,
                    order: [],
                    pageLength: parseInt($('#dashboardStocksPerPage').val(), 10) || 10,
                    lengthChange: false,
                    scrollY: '50vh',
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: @json(url('/reels/dashboard')) + '/' + stockContext.reelId + '/stocks',
                        data: request => {
                            request.reel_warehouse_id = stockContext.warehouseId;
                            request.status = stockContext.status;
                        }
                    },
                    columns: [{
                        data: 'select',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'stock_code',
                        name: 'stock_code'
                    },
                    {
                        data: 'actual_code',
                        name: 'actual_code'
                    },
                    {
                        data: 'provider_name',
                        orderable: false
                    },
                    {
                        data: 'warehouse_name',
                        orderable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: value =>
                            `<span class="badge bg-${value === 'full' ? 'primary' : 'warning text-dark'}">${value}</span>`
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                    ],
                    drawCallback: function () {
                        $('.dashboard-stock-checkbox').each(function () {
                            this.checked = selectedDashboardStocks.has(String(this
                                .dataset.id));
                        });
                        updateDashboardStockActions();
                    }
                });
                stocksModal.show();
            });

            $('#dashboardStocksPerPage').on('change', function () {
                if (stocksTable) stocksTable.page.len(parseInt(this.value, 10) || 10).draw();
            });

            $(document).on('change', '.dashboard-stock-checkbox', function () {
                const id = String(this.dataset.id);
                if (this.checked) selectedDashboardStocks.set(id, {
                    status: this.dataset.status,
                    price: parseFloat(this.dataset.price) || 0,
                    code: this.dataset.code,
                    reelCode: this.dataset.reelCode,
                    provider: this.dataset.provider,
                    addedDate: this.dataset.addedDate
                });
                else selectedDashboardStocks.delete(id);
                updateDashboardStockActions();
            });
            $('#dashboardSelectAllStocks').on('change', function () {
                $('.dashboard-stock-checkbox').each((_index, box) => {
                    box.checked = this.checked;
                    const id = String(box.dataset.id);
                    if (this.checked) selectedDashboardStocks.set(id, {
                        status: box.dataset.status,
                        price: parseFloat(box.dataset.price) || 0,
                        code: box.dataset.code,
                        reelCode: box.dataset.reelCode,
                        provider: box.dataset.provider,
                        addedDate: box.dataset.addedDate
                    });
                    else selectedDashboardStocks.delete(id);
                });
                updateDashboardStockActions();
            });
            $('#dashboardSaleSelected').on('click', () => setOperationMode('sale'));
            $('#dashboardTransferSelected').on('click', () => setOperationMode('transfer'));
            $(document).on('click', '.cancel-dashboard-operation', function () {
                $('.dashboard-operation-errors').addClass('d-none').empty();
                setOperationMode(null);
            });

            const printDashboardBarcodes = stocks => {
                if (!stocks.length || operationMode !== null) return;
                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    Swal.fire('Popup Blocked', 'Please allow popups to print barcode labels.', 'warning');
                    return;
                }
                const escapeHtml = value => $('<div>').text(value || '—').html();
                const labels = stocks.flatMap(stock => [stock, stock]);
                const canvases = labels.map((stock, index) =>
                    `<div class="label"><div class="date">${escapeHtml(stock.addedDate)}</div><canvas id="barcode-${index}"></canvas><div class="stock">${escapeHtml(stock.code)}</div><div class="detail">${escapeHtml(stock.reelCode)}</div><div class="detail">${escapeHtml(stock.provider)}</div></div>`
                ).join('');
                const data = JSON.stringify(labels).replace(/</g, '\\u003c');
                printWindow.document.write(
                    `<!doctype html><html><head><title>Reel Barcodes</title><style>@page{margin:5mm}*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif}.labels{display:flex;flex-wrap:wrap;gap:4mm}.label{width:90mm;min-height:55mm;padding:2.5mm;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;page-break-inside:avoid;overflow:hidden}.date{font-size:14px;font-weight:700;margin-bottom:2mm}.label canvas{width:70mm!important;height:20mm!important;max-width:100%;margin-bottom:1mm}.stock{font-size:18px;font-weight:600;line-height:1.15}.detail{width:100%;font-size:15px;font-weight:700;line-height:1.2;overflow-wrap:anywhere}</style></head><body><div class="labels">${canvases}</div><script src="{{ asset('custom/libraries/barcode-lib/bwip-js-min.js') }}"><\/script><script>const stocks=${data};stocks.forEach((stock,index)=>bwipjs.toCanvas('barcode-'+index,{bcid:'code128',text:stock.code,scale:3,height:12,includetext:false,paddingwidth:0,paddingheight:0}));setTimeout(()=>window.print(),300);<\/script></body></html>`
                );
                printWindow.document.close();
            };
            $(document).on('click', '.print-dashboard-stock', function () {
                if (this.disabled) return;
                printDashboardBarcodes([{
                    code: this.dataset.code,
                    reelCode: this.dataset.reelCode,
                    provider: this.dataset.provider,
                    addedDate: this.dataset.addedDate
                }]);
            });
            $('#dashboardPrintSelected').on('click', () => printDashboardBarcodes(Array.from(selectedDashboardStocks
                .values())));

            $(document).on('click', '.edit-dashboard-actual', function () {
                if (this.disabled) return;
                const button = $(this),
                    row = button.closest('tr'),
                    cell = stocksTable.cell(row, 3);
                $(cell.node()).empty().append($('<input>', {
                    type: 'text',
                    class: 'form-control form-control-sm dashboard-actual-input',
                    maxlength: 100
                }).val(button.data('code') || ''));
                button.removeClass('edit-dashboard-actual btn-outline-primary').addClass(
                    'save-dashboard-actual btn-success').attr('title', 'Save Actual Code').html(
                        '<i class="bx bx-save"></i>');
                $(cell.node()).find('input').trigger('focus');
            });
            $(document).on('click', '.save-dashboard-actual', function () {
                const button = $(this),
                    row = button.closest('tr'),
                    cell = stocksTable.cell(row, 3),
                    input = $(cell.node()).find('input'),
                    actualCode = input.val();
                $.ajax({
                    url: `${@json(url('/reels/stock'))}/${button.data('id')}/actual-code`,
                    type: 'POST',
                    data: {
                        _method: 'PUT',
                        _token: @json(csrf_token()),
                        actual_code: actualCode
                    },
                    success: response => {
                        $(cell.node()).text(actualCode || '—');
                        button.data('code', actualCode).removeClass(
                            'save-dashboard-actual btn-success').addClass(
                                'edit-dashboard-actual btn-outline-primary').attr('title',
                                    'Edit Actual Code').html('<i class="bx bx-edit"></i>');
                        const selected = selectedDashboardStocks.get(String(button.data('id')));
                        if (selected) selected.actualCode = actualCode;
                        iziToast.success({
                            title: 'Success',
                            message: response.message
                        });
                    },
                    error: xhr => iziToast.error({
                        title: 'Error',
                        message: xhr.responseJSON?.errors?.actual_code?.[0] || xhr
                            .responseJSON?.message || 'Unable to update actual code.'
                    })
                });
            });

            $('#confirmDashboardTransfer').on('click', function () {
                const button = $(this).prop('disabled', true);
                $.ajax({
                    url: `${@json(url('/reels/dashboard'))}/${stockContext.reelId}/stocks/transfer`,
                    type: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        stock_ids: selectedStockIds(),
                        source_warehouse_id: stockContext.warehouseId,
                        destination_warehouse_id: $('#dashboardTransferDestination').val()
                    },
                    success: response => {
                        clearDashboardStockSelection();
                        setOperationMode(null);
                        stocksTable.ajax.reload(null, false);
                        dashboardTable.ajax.reload();
                        iziToast.success({
                            title: 'Success',
                            message: response.message
                        });
                    },
                    error: xhr => showOperationErrors(xhr, '#dashboardTransferPanel'),
                    complete: () => button.prop('disabled', false)
                });
            });
            $('#confirmDashboardSale').on('click', function () {
                const button = $(this).prop('disabled', true);
                $.ajax({
                    url: `${@json(url('/reels/dashboard'))}/${stockContext.reelId}/stocks/sale`,
                    type: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        stock_ids: selectedStockIds(),
                        customer_id: $('#dashboardSaleCustomer').val(),
                        sale_date: $('#dashboardSaleDate').val()
                    },
                    success: response => {
                        clearDashboardStockSelection();
                        setOperationMode(null);
                        stocksTable.ajax.reload(null, false);
                        dashboardTable.ajax.reload();
                        iziToast.success({
                            title: 'Success',
                            message: response.message
                        });
                    },
                    error: xhr => showOperationErrors(xhr, '#dashboardSalePanel'),
                    complete: () => button.prop('disabled', false)
                });
            });
        });
    </script>
    @include('reels.stock._bulk_modal_script')
@endsection
