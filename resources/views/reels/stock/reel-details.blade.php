@extends('layouts.app')

@section('title', 'Stock - ' . $reel->code)

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reels', 'Manage Reels', 'Stock Details']" />

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $reel->code }}</h5>
                        <small class="text-muted">{{ $reel->brand->name }} / {{ $reel->type->name }} / {{ $reel->gsm->gsm }}
                            GSM</small>
                    </div>
                    <a href="{{ route('reels.manage.index') }}" class="btn btn-secondary">Back to Reels</a>
                </div>
                <div class="card-body">
                    @php
                        $cards = [
                            ['Total Reels', $totalReels, 'primary'],
                            ['Full', $statusCounts['full'] ?? 0, 'success'],
                            ['Bit', $statusCounts['bit'] ?? 0, 'warning'],
                            ['Finished', $statusCounts['finished'] ?? 0, 'secondary'],
                            ['Sold', $statusCounts['sold'] ?? 0, 'info'],
                        ];
                    @endphp
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Warehouse Summary</label>
                            <select id="filterStockWarehouse" class="form-select single-select-clear-field w-100"
                                data-placeholder="All Warehouses">
                                <option value=""></option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected((string) request('reel_warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        @foreach($cards as [$label, $value, $color])
                            <div class="col-md-4 col-xl">
                                <div class="border rounded p-3 border-{{ $color }}">
                                    <small class="text-muted">{{ $label }}</small>
                                    <div class="fs-4 fw-bold text-{{ $color }}"
                                        data-stock-stat="{{ strtolower(str_replace(' ', '_', $label)) }}">{{ $value }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row g-3 mb-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select id="filterStockStatus" class="form-select single-select-clear-field w-100"
                                data-placeholder="All Statuses">
                                <option value=""></option>
                                @foreach(['full', 'bit', 'finished', 'sold'] as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Actual Code</label>
                            <input type="search" id="filterActualCode" class="form-control"
                                placeholder="Search actual code">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="button" id="resetStockFilters"
                                class="btn btn-outline-secondary flex-fill">Reset</button>
                            <button type="button" id="transferSelectedStocks" class="btn btn-primary flex-fill">
                                <i class="bx bx-transfer"></i> Transfer
                            </button>
                            <button type="button" id="printSelectedBarcodes" class="btn btn-dark flex-fill" disabled>
                                <i class="bx bx-barcode"></i> Print Selected
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="physicalStockTable" class="table table-striped table-bordered align-middle w-100">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" class="form-check-input" id="selectAllStocks"></th>
                                    <th>Sl No.</th>
                                    <th>Stock Code</th>
                                    <th>Actual Code</th>
                                    <th>Provider</th>
                                    <th>Warehouse</th>
                                    {{-- <th>Original Length (m)</th>
                                    <th>Balance Length (m)</th> --}}
                                    <th>Stock Added Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="actualCodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="actualCodeForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add/Edit Actual Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="actualCodeStockId">
                    <div class="mb-2"><small class="text-muted">Stock Code</small>
                        <div class="fw-bold" id="actualCodeStockLabel"></div>
                    </div>
                    <label class="form-label">Actual Code</label>
                    <input type="text" id="actualCodeValue" class="form-control" maxlength="100"
                        placeholder="Optional actual code">
                    <div class="text-danger small mt-1 d-none" id="actualCodeError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="transferStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="transferStockForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Transfer Selected Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="transferStockErrors"></div>
                    <div class="mb-3">
                        <label class="form-label">Source Warehouse <span class="text-danger">*</span></label>
                        <select id="sourceWarehouseId" class="form-select w-100" data-placeholder="Select Source Warehouse"
                            required>
                            <option value=""></option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="border border-success rounded p-3">
                                <small class="text-muted">Full Available</small>
                                <div class="fs-4 fw-bold text-success" id="transferFullAvailable">0</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border border-warning rounded p-3">
                                <small class="text-muted">Bit Available</small>
                                <div class="fs-4 fw-bold text-warning" id="transferBitAvailable">0</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Full Quantity <span class="text-danger">*</span></label>
                            <input type="number" id="transferFullQuantity" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Bit Quantity <span class="text-danger">*</span></label>
                            <input type="number" id="transferBitQuantity" class="form-control" min="0" value="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Destination Warehouse <span class="text-danger">*</span></label>
                        <select id="destinationWarehouseId" class="form-select w-100"
                            data-placeholder="Select Destination Warehouse" required>
                            <option value=""></option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Remarks</label>
                        <textarea id="transferRemarks" class="form-control" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="confirmTransferButton">Transfer Stock</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/libraries/barcode-lib/bwip-js-min.js') }}"></script>
    <script>
        $(function () {
            const selectedStocks = new Map();
            const actualCodeModal = new bootstrap.Modal(document.getElementById('actualCodeModal'));
            const transferStockModal = new bootstrap.Modal(document.getElementById('transferStockModal'));
            const stockBaseUrl = @json(route('reels.stock.bulk-store', [], false)).replace(/\/bulk$/, '');
            const stockStatsUrl = @json(route('reels.manage.stock.stats', $reel, false));
            const stockTransferUrl = @json(route('reels.manage.stock.transfer', $reel, false));
            let actualCodeTimer;
            $('#sourceWarehouseId, #destinationWarehouseId').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                dropdownParent: $('#transferStockModal')
            });

            const table = $('#physicalStockTable').DataTable({
                processing: true,
                serverSide: true,
                order: [],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                ajax: {
                    url: @json(route('reels.manage.stock.data', $reel, false)),
                    data: request => {
                        request.status = $('#filterStockStatus').val();
                        request.reel_warehouse_id = $('#filterStockWarehouse').val();
                        request.actual_code = $('#filterActualCode').val();
                    }
                },
                columns: [
                    { data: 'select', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'stock_code', name: 'stock_code' },
                    { data: 'actual_code', name: 'actual_code' },
                    { data: 'provider_name', orderable: false },
                    { data: 'warehouse_name', orderable: false, searchable: false },
                    //{ data: 'original_length', name: 'original_length' },
                    //{ data: 'balance_length', name: 'balance_length' },
                    { data: 'created_at', name: 'created_at' },
                    {
                        data: 'status',
                        name: 'status',
                        render: status => `<span class="badge bg-${status === 'full' ? 'success' : status === 'bit' ? 'warning' : status === 'sold' ? 'info' : 'secondary'}">${status}</span>`
                    },
                    { data: 'action', orderable: false, searchable: false }
                ],
                drawCallback: function () {
                    $('.stock-checkbox').each(function () {
                        this.checked = selectedStocks.has(String(this.dataset.id));
                    });
                    updateSelectAll();
                    updateSelectionButtons();
                }
            });

            const updateSelectionButtons = () => {
                $('#printSelectedBarcodes').prop('disabled', selectedStocks.size === 0);
            };
            const updateSelectAll = () => {
                const boxes = $('.stock-checkbox');
                $('#selectAllStocks').prop('checked', boxes.length > 0 && boxes.toArray().every(box => box.checked));
            };

            const refreshStats = () => {
                $.get(stockStatsUrl, { reel_warehouse_id: $('#filterStockWarehouse').val() }, stats => {
                    $('[data-stock-stat="total_reels"]').text(stats.total);
                    $('[data-stock-stat="full"]').text(stats.full);
                    $('[data-stock-stat="bit"]').text(stats.bit);
                    $('[data-stock-stat="finished"]').text(stats.finished);
                    $('[data-stock-stat="sold"]').text(stats.sold);
                });
            };
            $('#filterStockStatus').on('change', () => table.ajax.reload());
            $('#filterStockWarehouse').on('change', () => {
                table.ajax.reload();
                refreshStats();
            });
            $('#filterActualCode').on('input', function () {
                clearTimeout(actualCodeTimer);
                actualCodeTimer = setTimeout(() => table.ajax.reload(), 350);
            });
            $('#resetStockFilters').on('click', function () {
                $('#filterStockStatus, #filterStockWarehouse').val('').trigger('change.select2');
                $('#filterActualCode').val('');
                table.ajax.reload();
                refreshStats();
            });

            $(document).on('change', '.stock-checkbox', function () {
                if (this.checked) selectedStocks.set(String(this.dataset.id), {
                    code: this.dataset.code,
                    reelCode: this.dataset.reelCode,
                    provider: this.dataset.provider,
                    addedDate: this.dataset.addedDate
                });
                else selectedStocks.delete(String(this.dataset.id));
                updateSelectAll();
                updateSelectionButtons();
            });
            $('#selectAllStocks').on('change', function () {
                $('.stock-checkbox').each((_index, checkbox) => {
                    checkbox.checked = this.checked;
                    if (this.checked) selectedStocks.set(String(checkbox.dataset.id), {
                        code: checkbox.dataset.code,
                        reelCode: checkbox.dataset.reelCode,
                        provider: checkbox.dataset.provider,
                        addedDate: checkbox.dataset.addedDate
                    });
                    else selectedStocks.delete(String(checkbox.dataset.id));
                });
                updateSelectionButtons();
            });

            $('#transferSelectedStocks').on('click', function () {
                $('#transferStockErrors').addClass('d-none').empty();
                $('#sourceWarehouseId, #destinationWarehouseId').val('').trigger('change');
                $('#transferFullAvailable, #transferBitAvailable').text('0');
                $('#transferFullQuantity, #transferBitQuantity').val(0).attr('max', 0);
                $('#transferRemarks').val('');
                transferStockModal.show();
            });
            if (@json(request()->boolean('open_transfer'))) {
                $('#transferSelectedStocks').trigger('click');
            }
            $('#sourceWarehouseId').on('change', function () {
                const warehouseId = $(this).val();
                $('#transferFullAvailable, #transferBitAvailable').text('0');
                $('#transferFullQuantity, #transferBitQuantity').val(0).attr('max', 0);
                if (!warehouseId) return;
                $.get(stockStatsUrl, { reel_warehouse_id: warehouseId, transferable: 1 }, stats => {
                    $('#transferFullAvailable').text(stats.full);
                    $('#transferBitAvailable').text(stats.bit);
                    $('#transferFullQuantity').attr('max', stats.full);
                    $('#transferBitQuantity').attr('max', stats.bit);
                });
            });
            $('#transferStockForm').on('submit', function (event) {
                event.preventDefault();
                const button = $('#confirmTransferButton').prop('disabled', true);
                $.ajax({
                    url: stockTransferUrl,
                    type: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        source_warehouse_id: $('#sourceWarehouseId').val(),
                        destination_warehouse_id: $('#destinationWarehouseId').val(),
                        full_quantity: $('#transferFullQuantity').val(),
                        bit_quantity: $('#transferBitQuantity').val(),
                        remarks: $('#transferRemarks').val()
                    },
                    success: response => {
                        transferStockModal.hide();
                        table.ajax.reload(null, false);
                        refreshStats();
                        iziToast.success({
                            title: 'Transferred',
                            message: response.message
                        });
                    },
                    error: xhr => {
                        const errors = xhr.responseJSON?.errors;
                        const messages = errors ? Object.values(errors).flat() : [xhr.responseJSON?.message || 'Unable to transfer stock.'];
                        $('#transferStockErrors').html(messages.map(message => `<div>${$('<div>').text(message).html()}</div>`).join('')).removeClass('d-none');
                    },
                    complete: () => button.prop('disabled', false)
                });
            });

            $(document).on('click', '.edit-actual-code', function () {
                $('#actualCodeStockId').val(this.dataset.id);
                $('#actualCodeStockLabel').text(this.dataset.stockCode);
                $('#actualCodeValue').val(this.dataset.code);
                $('#actualCodeError').addClass('d-none').text('');
                actualCodeModal.show();
            });
            $('#actualCodeForm').on('submit', function (event) {
                event.preventDefault();
                const id = $('#actualCodeStockId').val();
                $.ajax({
                    url: `${stockBaseUrl}/${id}/actual-code`,
                    type: 'POST',
                    data: { _method: 'PUT', _token: @json(csrf_token()), actual_code: $('#actualCodeValue').val() },
                    success: response => {
                        actualCodeModal.hide();
                        table.ajax.reload(null, false);
                        Swal.fire('Saved', response.message, 'success');
                    },
                    error: xhr => {
                        const message = xhr.responseJSON?.errors?.actual_code?.[0] || xhr.responseJSON?.message || 'Unable to update actual code.';
                        $('#actualCodeError').text(message).removeClass('d-none');
                    }
                });
            });

            const printBarcodes = stocks => {
                if (!stocks.length) {
                    Swal.fire('Select Stock', 'Select at least one physical reel to print.', 'warning');
                    return;
                }
                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    Swal.fire('Popup Blocked', 'Please allow popups to print barcode labels.', 'warning');
                    return;
                }
                const escapeHtml = value => $('<div>').text(value || '—').html();
                const labels = stocks.flatMap(stock => [stock, stock]);
                const canvases = labels.map((stock, index) => `
                    <div class="label">
                        <div class="added-date">${escapeHtml(stock.addedDate)}</div>
                        <canvas id="barcode-${index}"></canvas>
                        <div class="stock-code">${escapeHtml(stock.code)}</div>
                        <div class="reel-code">${escapeHtml(stock.reelCode)}</div>
                        <div class="provider">${escapeHtml(stock.provider)}</div>
                    </div>`).join('');
                const barcodeData = JSON.stringify(labels).replace(/</g, '\\u003c');
                printWindow.document.write(`<!doctype html><html><head><title>Reel Barcodes</title>
                    <style>
                        @page{margin:5mm}
                        *{box-sizing:border-box}
                        body{margin:0;font-family:Arial,sans-serif;color:#111}
                        .labels{display:flex;flex-wrap:wrap;gap:4mm}
                        .label{width:90mm;min-height:55mm;padding:2.5mm;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;page-break-inside:avoid;overflow:hidden}
                        .added-date{font-size:14px;font-weight:700;margin-bottom:2mm}
                        .label canvas{width:70mm!important;height:20mm!important;max-width:100%;margin-bottom:1mm}
                        .stock-code{font-size:18px;font-weight:600;line-height:1.15}
                        .reel-code{width:100%;font-size:15px;font-weight:700;line-height:1.2;overflow-wrap:anywhere}
                        .provider{width:100%;font-size:15px;font-weight:700;line-height:1.2;overflow-wrap:anywhere}
                    </style>
                    </head><body><div class="labels">${canvases}</div>
                    <script src="{{ asset('custom/libraries/barcode-lib/bwip-js-min.js') }}"><\/script>
                    <script>const stocks=${barcodeData};stocks.forEach((stock,index)=>bwipjs.toCanvas('barcode-'+index,{bcid:'code128',text:stock.code,scale:3,height:12,includetext:false,paddingwidth:0,paddingheight:0}));setTimeout(()=>window.print(),300);<\/script>
                    </body></html>`);
                printWindow.document.close();
            };

            $(document).on('click', '.print-stock-barcode', function () {
                printBarcodes([{
                    code: this.dataset.code,
                    reelCode: this.dataset.reelCode,
                    provider: this.dataset.provider,
                    addedDate: this.dataset.addedDate
                }]);
            });
            $('#printSelectedBarcodes').on('click', function () {
                printBarcodes(Array.from(selectedStocks.values()));
                selectedStocks.clear();
                $('.stock-checkbox, #selectAllStocks').prop('checked', false);
                updateSelectionButtons();
            });
        });
    </script>
@endsection
