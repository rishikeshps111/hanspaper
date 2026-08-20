@extends('layouts.app')
@section('title', 'Reel Stock')
@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">@endsection
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reels', 'Reel Stock']" />
            @include('layouts.session')
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">REEL STOCK</h5><a href="{{ route('reels.stock.create') }}" class="btn btn-primary">Add
                        Stock</a>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3"><select id="filterReel"
                                class="form-select stock-filter single-select-clear-field w-100"
                                data-placeholder="All Reel Products">
                                <option value=""></option>@foreach($reels as $reel)
                                    <option value="{{ $reel->id }}">
                                        {{ $reel->code }}
                                </option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="filterBrand"
                                class="form-select stock-filter single-select-clear-field w-100"
                                data-placeholder="All Brands">
                                <option value=""></option>@foreach($brands as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }}
                                </option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="filterProvider"
                                class="form-select stock-filter single-select-clear-field w-100"
                                data-placeholder="All Providers">
                                <option value=""></option>@foreach($providers as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }}
                                </option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="filterType"
                                class="form-select stock-filter single-select-clear-field w-100"
                                data-placeholder="All Types">
                                <option value=""></option>@foreach($types as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }}
                                </option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="filterGsm"
                                class="form-select stock-filter single-select-clear-field w-100" data-placeholder="All GSM">
                                <option value=""></option>@foreach($gsms as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->gsm }}
                                </option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="filterWarehouse"
                                class="form-select stock-filter single-select-clear-field w-100"
                                data-placeholder="All Warehouses">
                                <option value=""></option>@foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="filterStatus"
                                class="form-select stock-filter single-select-clear-field w-100"
                                data-placeholder="All Statuses">
                                <option value=""></option>@foreach(['full', 'bit', 'finished', 'sold'] as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach
                            </select></div>
                        <div class="col-md-3"><button id="resetFilters" class="btn btn-outline-secondary w-100">Reset
                                Filters</button></div>
                    </div>
                    <div class="table-responsive">
                        <table id="stockTable" class="table table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Sl No.</th>
                                    <th>Stock Code</th>
                                    <th>Reel Code</th>
                                    <th>Actual Code</th>
                                    <th>Provider</th>
                                    <th>Specification</th>
                                    <th>Warehouse</th>
                                    <th>Original Length (m)</th>
                                    <th>Balance Length (m)</th>
                                    <th>Stock Added Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
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
            const table = $('#stockTable').DataTable({
                processing: true, serverSide: true, order: [], ajax: { url: @json(route('reels.stock.data', [], false)), data: d => { d.reel_id = $('#filterReel').val(); d.reel_brand_id = $('#filterBrand').val(); d.reel_provider_id = $('#filterProvider').val(); d.reel_type_id = $('#filterType').val(); d.reel_gsm_id = $('#filterGsm').val(); d.reel_warehouse_id = $('#filterWarehouse').val(); d.status = $('#filterStatus').val(); } }, columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false }, { data: 'stock_code', name: 'stock_code' }, { data: 'reel_code', orderable: false }, { data: 'actual_code', orderable: false }, { data: 'provider_name', orderable: false }, { data: 'specification', orderable: false }, { data: 'warehouse_name', orderable: false }, { data: 'original_length', name: 'original_length' }, { data: 'balance_length', name: 'balance_length' }, { data: 'created_at', name: 'created_at' }, { data: 'status', name: 'status', render: d => `<span class="badge bg-${d === 'full' ? 'success' : d === 'bit' ? 'warning' : d === 'sold' ? 'info' : 'secondary'}">${d}</span>` }, { data: 'action', orderable: false, searchable: false }]
            });
            $('.stock-filter').on('change', () => table.ajax.reload()); $('#resetFilters').on('click', () => { $('.stock-filter').val('').trigger('change.select2'); table.ajax.reload(); });
        });
    </script>
@endsection