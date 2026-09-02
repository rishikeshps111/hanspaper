@extends('layouts.app')
@php($label = $type === 'box' ? 'Packing Box Stock' : 'Packing Cover Stock')
@section('title', $label)
@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">@endsection
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $label }}</h5><small class="text-muted">Manage {{ $type }} details
                        and available
                        quantities</small>
                </div><button class="btn btn-primary" id="addMaterial"><i class="bx bx-plus"></i> Add
                    {{ ucfirst($type) }}</button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="materialTable" class="table table-bordered align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Total Quantity</th>
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
    <div class="modal fade" id="materialModal">
        <div class="modal-dialog">
            <form id="materialForm" class="modal-content">@csrf<input type="hidden" id="materialId">
                <div class="modal-header">
                    <h5 class="modal-title">Add {{ ucfirst($type) }}</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label>Name <span class="text-danger">*</span></label><input name="name"
                                class="form-control" required></div>
                        <div class="col-12 opening"><label>Opening Quantity <span class="text-danger">*</span></label><input
                                type="number" min="0" name="quantity" value="0" class="form-control"></div>
                        <div class="col-12"><label>Status</label><select name="is_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select></div>
                        <div class="col-12 text-danger small" id="materialErrors"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">Close</button><button class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="adjustModal">
        <div class="modal-dialog">
            <form id="adjustForm" class="modal-content">@csrf<input type="hidden" id="adjustId">
                <div class="modal-header">
                    <h5>Adjust Quantity</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><select name="adjustment_type" class="form-select mb-3">
                        <option value="add">Add</option>
                        <option value="remove">Remove</option>
                    </select><input type="number" min="1" name="quantity" class="form-control mb-3" placeholder="Quantity"
                        required>
                    <textarea name="remarks" class="form-control" placeholder="Remarks" required></textarea>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="historyModal">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaction History</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="historyTable" class="table table-bordered align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction</th>
                                    <th>Quantity Change</th>
                                    <th>Quantity Before</th>
                                    <th>Quantity After</th>
                                    <th>Reference</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(function () {
            const type = @json($type),
                base = @json(url('packing-materials/' . $type));
            const cols = [{
                data: 'code'
            }, {
                data: 'name'
            }, {
                data: 'quantity'
            }, {
                data: 'is_active',
                render: d =>
                    `<span class="badge ${d ? 'bg-success' : 'bg-secondary'}">${d ? 'Active' : 'Inactive'}</span>`
            }, {
                data: 'action',
                orderable: false,
                searchable: false
            }];
            const table = $('#materialTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: base + '/data',
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [],
                columns: cols
            });
            const toast = m => window.iziToast ? iziToast.success({
                title: 'Success',
                message: m
            }) : Swal.fire('Success', m, 'success');
            $('#addMaterial').click(() => {
                $('#materialForm')[0].reset();
                $('#materialId').val('');
                $('#materialErrors').text('');
                $('.opening').show();
                $('#materialModal .modal-title').text('Add ' + (type === 'box' ? 'Box' : 'Cover'));
                $('#materialModal').modal('show')
            });
            $(document).on('click', '.edit-material', function () {
                const b = $(this);
                $('#materialForm')[0].reset();
                $('#materialErrors').text('');
                $('#materialId').val(b.data('id'));
                $('#materialForm [name=name]').val(b.attr('data-name'));
                $('#materialForm [name=is_active]').val(b.data('active'));
                $('.opening').hide();
                $('#materialModal .modal-title').text('Edit ' + b.data('code'));
                $('#materialModal').modal('show')
            });
            $('#materialForm').submit(function (e) {
                e.preventDefault();
                const id = $('#materialId').val(),
                    d = $(this).serialize() + (id ? '&_method=PUT' : '');
                $.post(base + (id ? '/' + id : ''), d).done(r => {
                    $('#materialModal').modal('hide');
                    toast(r.message);
                    table.ajax.reload(null, false)
                }).fail(x => $('#materialErrors').text(Object.values(x.responseJSON?.errors || {})
                    .flat()[0] || x.responseJSON?.message))
            });
            $(document).on('click', '.adjust-material', function () {
                $('#adjustForm')[0].reset();
                $('#adjustId').val($(this).data('id'));
                $('#adjustModal').modal('show')
            });
            $('#adjustForm').submit(function (e) {
                e.preventDefault();
                $.post(base + '/' + $('#adjustId').val() + '/adjust', $(this).serialize()).done(r => {
                    $('#adjustModal').modal('hide');
                    toast(r.message);
                    table.ajax.reload(null, false)
                }).fail(x => Swal.fire('Error', x.responseJSON?.message || 'Unable to adjust.',
                    'error'))
            });
            $(document).on('click', '.material-history', function () {
                const id = $(this).data('id'),
                    t = $('#historyTable');
                $('#historyModal .modal-title').text($(this).data('code') + ' - Transaction History');
                if ($.fn.DataTable.isDataTable(t)) t.DataTable().destroy();
                t.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: base + '/' + id + '/history',
                    columns: [{
                        data: 'created_at'
                    }, {
                        data: 'transaction_type'
                    }, {
                        data: 'quantity_change',
                        render: d => {
                            const value = parseInt(d) || 0;
                            return `<span class="badge ${value >= 0 ? 'bg-success' : 'bg-danger'}">${d}</span>`
                        }
                    }, {
                        data: 'quantity_before'
                    }, {
                        data: 'quantity_after'
                    }, {
                        data: 'reference',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'remarks'
                    }]
                });
                $('#historyModal').modal('show')
            });
        });
    </script>
@endsection