@extends('layouts.app')

@section('title', 'Reel Stock Correction')

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Reel Stock Correction</h5><small class="text-muted">Audited corrections for stock
                        quantities and reel details</small>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                                data-bs-target="#quantityTab">Stock Quantity Corrections</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reelTab">Reel
                                Detail Corrections</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#historyTab">Correction History</button></li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="quantityTab">
                            <div class="row g-2 mb-3">
                                <div class="col-md-3"><select id="batchReel" class="form-select correction-select">
                                        <option value="">All Reels</option>
                                        @foreach ($reels as $reel)
                                            <option value="{{ $reel->id }}">{{ $reel->code }}</option>
                                        @endforeach
                                    </select></div>
                                <div class="col-md-3"><select id="batchProvider" class="form-select correction-select">
                                        <option value="">All Providers</option>
                                        @foreach ($providers as $provider)
                                            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                        @endforeach
                                    </select></div>
                                <div class="col-md-3"><select id="batchWarehouse" class="form-select correction-select">
                                        <option value="">All Warehouses</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select></div>
                                <div class="col-md-2"><input type="date" id="batchDate" class="form-control"></div>
                                <div class="col-md-1"><button id="resetBatchFilters" class="btn btn-light w-100"
                                        title="Reset"><i class="bx bx-reset"></i></button></div>
                            </div>
                            <div class="table-responsive">
                                <table id="batchTable" class="table table-bordered align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Added Date</th>
                                            <th>Reel Code</th>
                                            <th>Provider</th>
                                            <th>Warehouse</th>
                                            <th>Originally Added</th>
                                            <th>Current Qty</th>
                                            <th>Eligible to Remove</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="reelTab">
                            <div class="table-responsive">
                                <table id="reelTable" class="table table-bordered align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Reel Code</th>
                                            <th>Brand</th>
                                            <th>Type</th>
                                            <th>GSM</th>
                                            <th>Width (mm)</th>
                                            <th>Length (m)</th>
                                            <th>Unit Price</th>
                                            <th>Selling Price</th>
                                            <th>Stocks</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="historyTab">
                            <h6>Stock Quantity Correction History</h6>
                            <div class="table-responsive">
                                <table id="historyTable" class="table table-bordered align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Reel</th>
                                            <th>Provider</th>
                                            <th>Warehouse</th>
                                            <th>Previous Qty</th>
                                            <th>Corrected Qty</th>
                                            <th>Change</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <h6 class="mt-4">Reel Detail Correction History</h6>
                            <div class="table-responsive">
                                <table id="reelHistoryTable" class="table table-bordered align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Reel</th>
                                            <th>Changed Fields</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="stockCorrectionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="stockCorrectionForm" class="modal-content">
                @csrf<input type="hidden" name="stock_batch_uuid"><input type="hidden" name="reel_id"><input
                    type="hidden" name="reel_provider_id"><input type="hidden" name="reel_warehouse_id">
                <div class="modal-header">
                    <h5 class="modal-title">Correct Stock Quantity</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border" id="stockCorrectionSummary"></div>
                    <div class="mb-3"><label class="form-label">Corrected Quantity <span
                                class="text-danger">*</span></label><input type="number" min="0" max="10000"
                            name="corrected_quantity" class="form-control" required>
                        <div class="invalid-feedback" data-error="corrected_quantity"></div>
                    </div>
                    <div><label class="form-label">Correction Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="3" maxlength="2000" class="form-control" required></textarea>
                        <div class="invalid-feedback" data-error="reason"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Apply
                        Correction</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="reelCorrectionModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="reelCorrectionForm" class="modal-content">
                @csrf<input type="hidden" id="correctionReelId">
                <div class="modal-header">
                    <h5 class="modal-title">Correct Reel Details</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Brand <span
                                    class="text-danger">*</span></label><select name="reel_brand_id"
                                class="form-select correction-select" required>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error="reel_brand_id"></div>
                        </div>
                        <div class="col-md-4"><label class="form-label">Reel Type <span
                                    class="text-danger">*</span></label><select name="reel_type_id"
                                class="form-select correction-select" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error="reel_type_id"></div>
                        </div>
                        <div class="col-md-4"><label class="form-label">GSM <span
                                    class="text-danger">*</span></label><select name="reel_gsm_id"
                                class="form-select correction-select" required>
                                @foreach ($gsms as $gsm)
                                    <option value="{{ $gsm->id }}">{{ $gsm->gsm }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error="reel_gsm_id"></div>
                        </div>
                        <div class="col-md-3"><label class="form-label">Width (mm) <span
                                    class="text-danger">*</span></label><input type="number" step="0.01"
                                name="width" class="form-control" required>
                            <div class="invalid-feedback" data-error="width"></div>
                        </div>
                        <div class="col-md-3"><label class="form-label">Length (m) <span
                                    class="text-danger">*</span></label><input type="number" step="0.01"
                                name="length" class="form-control" required>
                            <div class="invalid-feedback" data-error="length"></div>
                        </div>
                        <div class="col-md-3"><label class="form-label">Unit Price <span
                                    class="text-danger">*</span></label><input type="number" step="0.01"
                                name="unit_price" class="form-control" required>
                            <div class="invalid-feedback" data-error="unit_price"></div>
                        </div>
                        <div class="col-md-3"><label class="form-label">Selling Price <span
                                    class="text-danger">*</span></label><input type="number" step="0.01"
                                name="selling_price" class="form-control" required>
                            <div class="invalid-feedback" data-error="selling_price"></div>
                        </div>
                        <div class="col-md-4"><label class="form-label">Status <span
                                    class="text-danger">*</span></label><select name="is_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select></div>
                        <div class="col-md-8"><label class="form-label">Remarks</label><input type="text"
                                maxlength="5000" name="remarks" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Correction Reason <span
                                    class="text-danger">*</span></label>
                            <textarea name="reason" rows="2" maxlength="2000" class="form-control" required></textarea>
                            <div class="invalid-feedback" data-error="reason"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save
                        Correction</button></div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(function() {
            const urls = {
                batches: @json(route('reels.corrections.stock-batches', [], false)),
                correct: @json(route('reels.corrections.stock', [], false)),
                reels: @json(route('reels.corrections.reels', [], false)),
                reel: @json(route('reels.corrections.reel', ['reel' => '__ID__'], false)),
                history: @json(route('reels.corrections.history', [], false)),
                reelHistory: @json(route('reels.corrections.reel-history', [], false))
            };
            $('#batchReel,#batchProvider,#batchWarehouse').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
            $('#reelCorrectionModal .correction-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#reelCorrectionModal')
            });
            const toast = m => window.iziToast ? iziToast.success({
                title: 'Success',
                message: m
            }) : Swal.fire('Success', m, 'success');
            const errors = (form, x) => {
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('[data-error]').text('');
                Object.entries(x.responseJSON?.errors || {}).forEach(([key, value]) => {
                    const input = form.find(`[name="${key}"]`);
                    input.addClass('is-invalid');
                    form.find(`[data-error="${key}"]`).text(value[0]);
                });
                if (!x.responseJSON?.errors) Swal.fire('Error', x.responseJSON?.message ||
                    'Unable to save correction.', 'error');
            };
            const batchTable = $('#batchTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                order: [],
                ajax: {
                    url: urls.batches,
                    data: d => Object.assign(d, {
                        reel_id: $('#batchReel').val(),
                        reel_provider_id: $('#batchProvider').val(),
                        reel_warehouse_id: $('#batchWarehouse').val(),
                        added_date: $('#batchDate').val()
                    })
                },
                columns: [{
                    data: 'added_at',
                    name: 'added_at'
                }, {
                    data: 'reel_code',
                    name: 'reels.code'
                }, {
                    data: 'provider_name',
                    name: 'providers.name'
                }, {
                    data: 'warehouse_name',
                    name: 'warehouses.name'
                }, {
                    data: 'original_quantity',
                    searchable: false,
                    orderable: false
                }, {
                    data: 'current_quantity',
                    searchable: false
                }, {
                    data: 'eligible_quantity',
                    searchable: false,
                    orderable: false
                }, {
                    data: 'action',
                    searchable: false,
                    orderable: false
                }]
            });
            $('#batchReel,#batchProvider,#batchWarehouse,#batchDate').on('change', () => batchTable.ajax.reload());
            $('#resetBatchFilters').click(() => {
                $('#batchReel,#batchProvider,#batchWarehouse').val('').trigger('change.select2');
                $('#batchDate').val('');
                batchTable.ajax.reload();
            });
            $(document).on('click', '.correct-stock', function() {
                const b = $(this),
                    row = batchTable.row(b.closest('tr')).data(),
                    f = $('#stockCorrectionForm');
                f[0].reset();
                f.find('.is-invalid').removeClass('is-invalid');
                f.find('[name=stock_batch_uuid]').val(row.batch_uuid);
                f.find('[name=reel_id]').val(row.reel_id);
                f.find('[name=reel_provider_id]').val(row.reel_provider_id);
                f.find('[name=reel_warehouse_id]').val(row.reel_warehouse_id);
                f.find('[name=corrected_quantity]').val(row.current_quantity);
                $('#stockCorrectionSummary').html(
                    `<strong>${$('<div>').text(row.reel_code).html()}</strong><br>Current quantity: ${row.current_quantity} &nbsp;|&nbsp; Eligible to remove: ${row.eligible_quantity}`
                    );
                $('#stockCorrectionModal').modal('show');
            });
            $('#stockCorrectionForm').submit(function(e) {
                e.preventDefault();
                const f = $(this),
                    button = f.find('[type=submit]').prop('disabled', true);
                $.post(urls.correct, f.serialize()).done(r => {
                    $('#stockCorrectionModal').modal('hide');
                    toast(r.message);
                    batchTable.ajax.reload(null, false);
                    historyTable.ajax.reload(null, false);
                }).fail(x => errors(f, x)).always(() => button.prop('disabled', false));
            });
            const reelTable = $('#reelTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                order: [],
                ajax: urls.reels,
                columns: [{
                    data: 'code'
                }, {
                    data: 'brand_name',
                    orderable: false
                }, {
                    data: 'type_name',
                    orderable: false
                }, {
                    data: 'gsm_value',
                    orderable: false
                }, {
                    data: 'width'
                }, {
                    data: 'length'
                }, {
                    data: 'unit_price'
                }, {
                    data: 'selling_price'
                }, {
                    data: 'stocks_count',
                    searchable: false
                }, {
                    data: 'is_active',
                    render: d =>
                        `<span class="badge ${d ? 'bg-success' : 'bg-secondary'}">${d ? 'Active' : 'Inactive'}</span>`
                }, {
                    data: 'action',
                    searchable: false,
                    orderable: false
                }]
            });
            $(document).on('click', '.edit-reel', function() {
                const id = $(this).data('id'),
                    f = $('#reelCorrectionForm');
                f[0].reset();
                f.find('.is-invalid').removeClass('is-invalid');
                $.get(urls.reel.replace('__ID__', id)).done(({
                    reel: r
                }) => {
                    $('#correctionReelId').val(r.id);
                    Object.entries(r).forEach(([key, value]) => {
                        const input = f.find(`[name="${key}"]`);
                        input.val(value === true ? 1 : value === false ? 0 : value);
                        if (input.is('select')) input.trigger('change.select2');
                    });
                    $('#reelCorrectionModal .modal-title').text('Correct ' + r.code);
                    $('#reelCorrectionModal').modal('show');
                });
            });
            $('#reelCorrectionForm').submit(function(e) {
                e.preventDefault();
                const f = $(this),
                    id = $('#correctionReelId').val(),
                    button = f.find('[type=submit]').prop('disabled', true);
                $.ajax({
                    url: urls.reel.replace('__ID__', id),
                    type: 'PUT',
                    data: f.serialize()
                }).done(r => {
                    $('#reelCorrectionModal').modal('hide');
                    toast(r.message);
                    reelTable.ajax.reload(null, false);
                    batchTable.ajax.reload(null, false);
                }).fail(x => errors(f, x)).always(() => button.prop('disabled', false));
            });
            const historyTable = $('#historyTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                order: [],
                ajax: urls.history,
                columns: [{
                    data: 'created_at'
                }, {
                    data: 'reel_code',
                    orderable: false
                }, {
                    data: 'provider_name',
                    orderable: false
                }, {
                    data: 'warehouse_name',
                    orderable: false
                }, {
                    data: 'previous_quantity'
                }, {
                    data: 'corrected_quantity'
                }, {
                    data: 'quantity_change',
                    render: d =>
                        `<span class="badge ${parseInt(d) >= 0 ? 'bg-success' : 'bg-danger'}">${d}</span>`
                }, {
                    data: 'reason'
                }]
            });
            const reelHistoryTable = $('#reelHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                order: [],
                ajax: urls.reelHistory,
                columns: [{
                    data: 'created_at'
                }, {
                    data: 'reel_code',
                    orderable: false
                }, {
                    data: 'changes',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'reason'
                }]
            });
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', () => $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust());
        });
    </script>
@endsection
