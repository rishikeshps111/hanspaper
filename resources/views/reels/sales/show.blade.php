@extends('layouts.app')
@section('title', $sale->invoice_number ?: $sale->sale_code)
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reels', 'Reel Sales', 'Details']" />
            @include('layouts.session')
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        {{-- <h5 class="mb-1">{{ $sale->invoice_number }}</h5><small class="text-muted">{{ $sale->sale_code
                            }}</small> --}}
                        <h5 class="mb-1">Reel Sale Details</h5>
                    </div>
                    <div>
                        {{-- <a href="{{ route('reels.sales.stocks', $sale) }}" class="btn btn-outline-secondary"><i
                                class="bx bx-list-ul"></i> Physical Stocks</a>
                        <a href="{{ route('reels.sales.invoice', $sale) }}" class="btn btn-outline-danger"><i
                                class="bx bx-download"></i> Download Invoice</a> --}}
                        <a href="{{ route('reels.sales.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><small class="text-muted">Customer</small>
                            <div class="fw-bold">{{ trim($sale->customer->first_name . ' ' . $sale->customer->last_name) }}
                            </div>
                        </div>
                        <div class="col-md-4"><small class="text-muted">Sale Date</small>
                            <div class="fw-bold">{{ $sale->sale_date->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-4"><small class="text-muted">Total Amount</small>
                            <div class="fw-bold">{{ number_format($sale->total, 2) }}</div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Reel Product</th>
                                    <th>Warehouse Quantity</th>
                                    <th class="text-center">Total Quantity</th>
                                    <th class="text-end">Selling Price</th>
                                    {{-- <th class="text-end">Discount</th> --}}
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="fw-semibold">{{ $product['reel']->code }}</td>
                                        <td>
                                            @foreach ($product['warehouses'] as $warehouse)
                                                <div>{{ $warehouse['name'] }}: <strong>{{ $warehouse['quantity'] }}</strong>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td class="text-center">{{ $product['quantity'] }}</td>
                                        <td class="text-end">{{ number_format($product['unit_price'], 2) }}</td>
                                        {{-- <td class="text-end">{{ number_format($product['discount'], 2) }}</td> --}}
                                        <td class="text-end">{{ number_format($product['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="d-none">
                                    <th colspan="5" class="text-end">Subtotal</th>
                                    <th class="text-end">{{ number_format($sale->subtotal, 2) }}</th>
                                </tr>
                                <tr class="d-none">
                                    <th colspan="5" class="text-end">Discount</th>
                                    <th class="text-end">{{ number_format($sale->discount, 2) }}</th>
                                </tr>
                                @if ($sale->is_gst_applicable)
                                    <tr>
                                        <th colspan="5" class="text-end">SGST
                                            ({{ number_format($sale->sgst_percentage, 2) }}%)</th>
                                        <th class="text-end">{{ number_format($sale->sgst_amount, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">CGST
                                            ({{ number_format($sale->cgst_percentage, 2) }}%)</th>
                                        <th class="text-end">{{ number_format($sale->cgst_amount, 2) }}</th>
                                    </tr>
                                @endif
                                <tr class="table-light">
                                    <th colspan="4" class="text-end">Total Amount</th>
                                    <th class="text-end">{{ number_format($sale->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <h6 class="text-uppercase mt-4 mb-3">Reel Stocks Associated to these sale</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Stock Code</th>
                                    <th>Actual Code</th>
                                    <th>Provider</th>
                                    {{-- <th>Stock Added Date</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sale->items as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->stock->stock_code }}</td>
                                        <td>{{ $item->stock->actual_code ?: '—' }}</td>
                                        <td>{{ $item->stock->provider?->name ?: '—' }}</td>
                                        {{-- <td>{{ $item->stock->created_at?->format('d M Y h:i a') ?: '—' }}</td> --}}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($sale->remarks)
                        <div class="mt-3"><small class="text-muted">Remarks</small>
                            <div>{{ $sale->remarks }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection