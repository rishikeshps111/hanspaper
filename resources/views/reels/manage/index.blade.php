@extends('layouts.app')

@section('title', 'Manage Reels')

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reels', 'Manage Reels']" />

            <div class="row">
                <div class="col-12">@include('layouts.session')</div>
            </div>

            <div class="card">
                <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-uppercase">Manage Reels</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('reels.manage.create') }}" class="btn btn-primary px-4">
                            <i class="bx bx-plus me-1"></i>Create Reel
                        </a>
                        <button type="button" class="btn btn-success px-4 add-reel-stock">
                            <i class="bx bx-package me-1"></i>Add Stock
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4 col-xl">
                            <label for="filterBrand" class="form-label">Brand</label>
                            <select id="filterBrand" class="form-select reel-filter single-select-clear-field w-100"
                                data-placeholder="All Brands" style="width: 100%">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}
                                </option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-xl">
                            <label for="filterType" class="form-label">Type</label>
                            <select id="filterType" class="form-select reel-filter single-select-clear-field w-100"
                                data-placeholder="All Types" style="width: 100%">
                                <option value="">All Types</option>
                                @foreach($types as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-xl">
                            <label for="filterGsm" class="form-label">GSM</label>
                            <select id="filterGsm" class="form-select reel-filter single-select-clear-field w-100"
                                data-placeholder="All GSM" style="width: 100%">
                                <option value="">All GSM</option>
                                @foreach($gsms as $gsm)<option value="{{ $gsm->id }}">{{ $gsm->gsm }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-xl">
                            <label for="filterStatus" class="form-label">Status</label>
                            <select id="filterStatus" class="form-select reel-filter single-select-clear-field w-100"
                                data-placeholder="All Statuses" style="width: 100%">
                                <option value="">All Statuses</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-xl d-flex align-items-end">
                            <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">Reset
                                Filters</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle w-100" id="reelsTable">
                            <thead>
                                <tr>
                                    <th>Sl No.</th>
                                    <th>Code</th>
                                    <th>Brand</th>
                                    <th>Type</th>
                                    <th>GSM</th>
                                    <th>Width (mm)</th>
                                    <th>Length (m)</th>
                                    <th>Unit Price</th>
                                    <th>Selling Price</th>
                                    <th>Total Reels</th>
                                    <th>Full</th>
                                    <th>Bit</th>
                                    <th>Finished</th>
                                    <th>Sold</th>
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

    @include('reels.stock._bulk_modal')
@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(function () {
            const table = $('#reelsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('reels.manage.data', [], false)),
                    data: function (request) {
                        request.reel_brand_id = $('#filterBrand').val();
                        request.reel_type_id = $('#filterType').val();
                        request.reel_gsm_id = $('#filterGsm').val();
                        request.is_active = $('#filterStatus').val();
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'Unable to load reels. Please refresh and try again.';
                        Swal.fire('Unable to load data', message, 'error');
                    }
                },
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'code', name: 'code' },
                    { data: 'brand_name', name: 'brand_name', orderable: false },
                    { data: 'type_name', name: 'type_name', orderable: false },
                    { data: 'gsm_value', name: 'gsm_value', orderable: false },
                    { data: 'width', name: 'width' },
                    { data: 'length', name: 'length' },
                    { data: 'unit_price', name: 'unit_price' },
                    { data: 'selling_price', name: 'selling_price' },
                    {
                        data: 'stocks_count', name: 'stocks_count', searchable: false,
                        render: data => `<span class="badge bg-primary rounded-pill px-3">${data ?? 0}</span>`
                    },
                    {
                        data: 'full_stock_count', name: 'full_stock_count', searchable: false,
                        render: data => `<span class="badge bg-success rounded-pill px-3">${data ?? 0}</span>`
                    },
                    {
                        data: 'bit_stock_count', name: 'bit_stock_count', searchable: false,
                        render: data => `<span class="badge bg-warning text-dark rounded-pill px-3">${data ?? 0}</span>`
                    },
                    {
                        data: 'finished_stock_count', name: 'finished_stock_count', searchable: false,
                        render: data => `<span class="badge bg-secondary rounded-pill px-3">${data ?? 0}</span>`
                    },
                    {
                        data: 'sold_stock_count', name: 'sold_stock_count', searchable: false,
                        render: data => `<span class="badge bg-danger rounded-pill px-3">${data ?? 0}</span>`
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: data => data
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Inactive</span>'
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            $('.reel-filter').on('change', () => table.ajax.reload());
            $('#resetFilters').on('click', function () {
                $('.reel-filter').val('').trigger('change.select2');
                table.ajax.reload();
            });

            $(document).on('reel:stock-added reel:created', () => table.ajax.reload(null, false));

            $(document).on('click', '.delete-reel', function () {
                const id = this.dataset.id;
                Swal.fire({
                    title: 'Delete this reel?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Delete'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: `${@json(route('reels.manage.index', [], false))}/${id}`,
                        type: 'POST',
                        data: { _method: 'DELETE', _token: @json(csrf_token()) },
                        success: response => {
                            table.ajax.reload(null, false);
                            Swal.fire('Deleted', response.message, 'success');
                        },
                        error: xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Unable to delete reel.', 'error')
                    });
                });
            });
        });
    </script>
    @include('reels.stock._bulk_modal_script')
@endsection
