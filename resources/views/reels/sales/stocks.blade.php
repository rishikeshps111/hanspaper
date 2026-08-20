@extends('layouts.app')
@section('title', 'Physical Stocks - '.($sale->invoice_number ?: $sale->sale_code))
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['Reels','Reel Sales','Physical Stocks']"/>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><h5 class="mb-1">ASSOCIATED PHYSICAL REEL STOCKS</h5><small class="text-muted">{{ $sale->invoice_number }} · {{ $sale->sale_code }}</small></div>
                <a href="{{ route('reels.sales.index') }}" class="btn btn-secondary">Back</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead><tr><th>Sl No.</th><th>Stock Code</th><th>Actual Code</th><th>Reel Product</th><th>Warehouse</th><th class="text-end">Selling Price</th><th class="text-end">Discount</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                        @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->stock->stock_code }}</td>
                                <td>{{ $item->stock->actual_code ?: '-' }}</td>
                                <td>{{ $item->stock->reel->code }}</td>
                                <td>{{ $item->stock->warehouse?->name ?: 'Unknown warehouse' }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                                <td class="text-end">{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
