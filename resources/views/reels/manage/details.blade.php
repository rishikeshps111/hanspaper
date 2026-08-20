@extends('layouts.app')

@section('title', 'Reel Details - ' . $reel->code)

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['Reels', 'Manage Reels', 'Reel Details']" />

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ $reel->code }}</h5>
                    <span class="badge {{ $reel->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $reel->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('reels.manage.stock', $reel) }}" class="btn btn-outline-info">
                        <i class="bx bx-package"></i> Stock Details
                    </a>
                    <a href="{{ route('reels.manage.edit', $reel) }}" class="btn btn-outline-primary">
                        <i class="bx bx-edit"></i> Edit
                    </a>
                    <a href="{{ route('reels.manage.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
            <div class="card-body">
                <h6 class="text-uppercase mb-3">Reel Information</h6>
                <div class="row g-3 mb-4">
                    @php
                        $details = [
                            'Code' => $reel->code,
                            'Brand' => $reel->brand->name,
                            'Brand Short Name' => $reel->brand->short_name ?: '—',
                            'Reel Type' => $reel->type->name,
                            'Type Short Name' => $reel->type->short_name ?: '—',
                            'GSM' => $reel->gsm->gsm . ' GSM',
                            'Width' => number_format((float) $reel->width, 2) . ' mm',
                            'Length' => number_format((float) $reel->length, 2) . ' m',
                            'Unit Price' => $reel->unit_price,
                            'Selling Price' => $reel->selling_price,
                            'Remarks' => $reel->remarks,
                        ];
                    @endphp
                    @foreach($details as $label => $value)
                        <div class="{{ $label === 'Remarks' ? 'col-12' : 'col-md-4' }}">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted">{{ $label }}</small>
                                <div class="fw-semibold text-break">{{ $value }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @php
                    $totalReels = $stockSummary->sum('total');
                    $cards = [
                        ['Total Reels', $totalReels, 'primary'],
                        ['Full', $stockSummary['full']->total ?? 0, 'success'],
                        ['Bit', $stockSummary['bit']->total ?? 0, 'warning'],
                        ['Finished', $stockSummary['finished']->total ?? 0, 'secondary'],
                        ['Sold', $stockSummary['sold']->total ?? 0, 'info'],
                    ];
                @endphp
                <h6 class="text-uppercase mb-3">Stock Summary</h6>
                <div class="row g-3 mb-3">
                    @foreach($cards as [$label, $value, $color])
                        <div class="col-md-4 col-xl">
                            <div class="border border-{{ $color }} rounded p-3">
                                <small class="text-muted">{{ $label }}</small>
                                <div class="fs-4 fw-bold text-{{ $color }}">{{ $value }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <h6 class="text-uppercase mb-3">Transaction History</h6>
                <div class="table-responsive">
                    <table id="reelTransactionsTable" class="table table-striped table-bordered align-middle w-100">
                        <thead>
                            <tr>
                                <th>Sl No.</th>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Type</th>
                                <th>Items</th>
                                <th>Provider</th>
                                <th>Warehouse</th>
                                <th>Customer</th>
                                <th>Reference</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
    $('#reelTransactionsTable').DataTable({
        processing: true,
        serverSide: true,
        order: [],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ajax: @json(route('reels.manage.transactions.data', $reel, false)),
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'created_at', name: 'created_at'},
            {data: 'activity', name: 'activity'},
            {data: 'transaction_type', name: 'transaction_type'},
            {data: 'quantity', name: 'quantity'},
            {data: 'provider', name: 'provider'},
            {data: 'warehouse', name: 'warehouse'},
            {data: 'customer', name: 'customer'},
            {data: 'reference', orderable: false, searchable: false},
            {data: 'remarks', name: 'remarks', defaultContent: '—'}
        ]
    });
});
</script>
@endsection
