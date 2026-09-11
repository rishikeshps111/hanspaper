@extends('layouts.app')
@section('title', 'Finished Reels')
@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        #finishedReelsTable th {
            white-space: nowrap;
        }
    </style>
@endsection
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reels', 'Finished Reels']" />
            @include('layouts.session')
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 text-uppercase">Finished Reels</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3"><select id="finishedReel"
                                class="form-select finished-filter single-select-clear-field w-100"
                                data-placeholder="All Reels">
                                <option value=""></option>@foreach($reels as $reel)
                                <option value="{{ $reel->id }}">{{ $reel->code }}</option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="finishedBrand"
                                class="form-select finished-filter single-select-clear-field w-100"
                                data-placeholder="All Brands">
                                <option value=""></option>@foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="finishedType"
                                class="form-select finished-filter single-select-clear-field w-100"
                                data-placeholder="All Reel Types">
                                <option value=""></option>@foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="finishedGsm"
                                class="form-select finished-filter single-select-clear-field w-100"
                                data-placeholder="All GSM">
                                <option value=""></option>@foreach($gsms as $gsm)
                                <option value="{{ $gsm->id }}">{{ $gsm->gsm }}</option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="finishedProvider"
                                class="form-select finished-filter single-select-clear-field w-100"
                                data-placeholder="All Providers">
                                <option value=""></option>@foreach($providers as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>@endforeach
                            </select></div>
                        <div class="col-md-3"><select id="finishedWarehouse"
                                class="form-select finished-filter single-select-clear-field w-100"
                                data-placeholder="All Warehouses">
                                <option value=""></option>@foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach
                            </select></div>
                        <div class="col-md-2"><input id="finishedFrom" type="date" class="form-control finished-filter"
                                title="Finished From"></div>
                        <div class="col-md-2"><input id="finishedTo" type="date" class="form-control finished-filter"
                                title="Finished To"></div>
                        <div class="col-md-2"><button id="resetFinishedFilters" type="button"
                                class="btn btn-outline-secondary w-100"><i class="bx bx-reset me-1"></i>Reset</button></div>
                    </div>
                    <div class="table-responsive">
                        <table id="finishedReelsTable" class="table table-striped table-bordered align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Sl No.</th>
                                    <th>Stock Code</th>
                                    <th>Actual Code</th>
                                    <th>Reel Code</th>
                                    <th>Brand</th>
                                    <th>Reel Type</th>
                                    <th>GSM</th>
                                    <th>Width (mm)</th>
                                    <th>Original Length (m)</th>
                                    <th>Provider</th>
                                    <th>Warehouse</th>
                                    <th>Stock Added Date</th>
                                    <th>Finished Date</th>
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
            const table = $('#finishedReelsTable').DataTable({
                processing: true, serverSide: true, order: [], pageLength: 10, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]], ajax: { url: @json(route('reels.finished.data', [], false)), data: d => { d.reel_id = $('#finishedReel').val(); d.reel_brand_id = $('#finishedBrand').val(); d.reel_type_id = $('#finishedType').val(); d.reel_gsm_id = $('#finishedGsm').val(); d.reel_provider_id = $('#finishedProvider').val(); d.reel_warehouse_id = $('#finishedWarehouse').val(); d.finished_from = $('#finishedFrom').val(); d.finished_to = $('#finishedTo').val() } }, columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false }, { data: 'stock_code', name: 'reel_stocks.stock_code' }, { data: 'actual_code', name: 'reel_stocks.actual_code' }, { data: 'reel_code', orderable: false, searchable: false }, { data: 'brand_name', orderable: false, searchable: false }, { data: 'type_name', orderable: false, searchable: false }, { data: 'gsm_value', orderable: false, searchable: false }, { data: 'width', orderable: false, searchable: false }, { data: 'original_length', name: 'reel_stocks.original_length' }, { data: 'provider_name', orderable: false, searchable: false }, { data: 'warehouse_name', orderable: false, searchable: false }, { data: 'created_at', name: 'reel_stocks.created_at' }, { data: 'finished_at', orderable: false, searchable: false }, { data: 'action', orderable: false, searchable: false }
                ]
            });
            $('.finished-filter').on('change', () => table.ajax.reload());
            $('#resetFinishedFilters').on('click', function () { $('.finished-filter').val(''); $('select.finished-filter').trigger('change.select2'); table.ajax.reload() });
        });
    </script>
@endsection