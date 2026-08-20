@extends('layouts.app')
@section('title', 'Reel Sales')
@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">@endsection
@section('content')
    <div class="page-wrapper">
        <div class="page-content"><x-breadcrumb :langArray="['Reels', 'Reel Sales']" />
            @include('layouts.session')
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">REEL SALES</h5>
                    {{-- <a href="{{ route('reels.sales.create') }}" class="btn btn-primary">New
                        Reel Sale</a> --}}
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3"><label class="form-label">Customer</label><select id="filterSaleCustomer"
                                class="form-select sale-filter w-100" data-placeholder="All Customers">
                                <option value=""></option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ trim($customer->first_name . ' ' . $customer->last_name) }}{{ $customer->mobile ? ' - ' . $customer->mobile : '' }}
                                    </option>
                                @endforeach
                            </select></div>
                        <div class="col-md-3"><label class="form-label">Sale Date</label><input type="date"
                                id="filterSaleDate" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Reel</label><select id="filterSaleReel"
                                class="form-select sale-filter w-100" data-placeholder="All Reels">
                                <option value=""></option>
                                @foreach ($reels as $reel)
                                    <option value="{{ $reel->id }}">{{ $reel->code }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-md-3 d-flex align-items-end"><button type="button" id="resetSaleFilters"
                                class="btn btn-outline-secondary w-100"><i class="bx bx-reset me-1"></i>Reset</button></div>
                    </div>
                    <div class="table-responsive">
                        <table id="salesTable" class="table table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Sl No.</th>
                                    {{-- <th>Invoice Number</th>
                                    <th>Sale Code</th> --}}
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Reel</th>
                                    <th>Unit Selling Price</th>
                                    <th>Items</th>
                                    {{-- <th>Subtotal</th>
                                    <th>Discount</th> --}}
                                    <th>Total Amount</th>
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
            $('.sale-filter').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true
            });
            const table = $('#salesTable').DataTable({
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: @json(route('reels.sales.data', [], false)),
                    data: request => {
                        request.customer_id = $('#filterSaleCustomer').val();
                        request.sale_date = $('#filterSaleDate').val();
                        request.reel_id = $('#filterSaleReel').val();
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                // {
                //     data: 'invoice_number',
                //     name: 'invoice_number'
                // },
                // {
                //     data: 'sale_code',
                //     name: 'sale_code'
                // },
                {
                    data: 'sale_date',
                    name: 'sale_date'
                }, {
                    data: 'customer_name',
                    orderable: false
                }, {
                    data: 'reels_badges',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'unit_selling_prices',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'items_count',
                    orderable: false,
                    searchable: false
                },
                // {
                //     data: 'subtotal',
                //     name: 'subtotal'
                // },
                // {
                //     data: 'discount',
                //     name: 'discount'
                // },
                {
                    data: 'total',
                    name: 'total'
                }, {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
                ]
            });
            $('.sale-filter,#filterSaleDate').on('change', () => table.ajax.reload());
            $('#resetSaleFilters').on('click', function () {
                $('.sale-filter').val(null).trigger('change.select2');
                $('#filterSaleDate').val('');
                table.ajax.reload();
            });
        });
    </script>
@endsection