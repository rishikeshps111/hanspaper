@extends('layouts.app')
@section('title', $barcodeWorkOrder->code)

@section('content')
    @php
        $statusClasses = [
            'pending' => 'bg-warning text-dark',
            'partial_pending' => 'bg-info text-dark',
            'completed' => 'bg-success',
            'dispatched' => 'bg-primary',
            'delivered' => 'bg-dark',
            'cancelled' => 'bg-danger',
        ];
    @endphp
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Barcode WorkOrder', 'Work Order Details']" />
            @include('layouts.session')
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $barcodeWorkOrder->code }}</h5><span
                            class="badge {{ $statusClasses[$barcodeWorkOrder->status] }}">{{ ucwords(str_replace('_', ' ', $barcodeWorkOrder->status)) }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        @if ($barcodeWorkOrder->status === 'pending')
                            <a href="{{ route('barcode-work-orders.edit', $barcodeWorkOrder) }}" class="btn btn-primary"><i
                                    class="bx bx-edit me-1"></i>Edit</a>
                        @endif
                        <a href="{{ route('barcode-work-orders.index') }}" class="btn btn-outline-secondary">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3"><small
                                class="text-muted d-block">Customer</small><strong>{{ trim(($barcodeWorkOrder->customer?->first_name ?? '') . ' ' . ($barcodeWorkOrder->customer?->last_name ?? '')) ?: '—' }}</strong>
                        </div>
                        <div class="col-md-3"><small
                                class="text-muted d-block">Representative</small><strong>{{ $barcodeWorkOrder->representative?->full_name ?? '—' }}</strong>
                        </div>
                        <div class="col-md-3"><small
                                class="text-muted d-block">Date</small><strong>{{ $barcodeWorkOrder->work_order_date->format('d M Y') }}</strong>
                        </div>
                        <div class="col-md-3"><small class="text-muted d-block">Due
                                Date</small><strong>{{ $barcodeWorkOrder->due_date->format('d M Y') }}</strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Completed
                                Date</small><strong>{{ $barcodeWorkOrder->completed_date?->format('d M Y') ?? '—' }}</strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Dispatched
                                Date</small><strong>{{ $barcodeWorkOrder->dispatched_date?->format('d M Y') ?? '—' }}</strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Delivered
                                Date</small><strong>{{ $barcodeWorkOrder->delivered_date?->format('d M Y') ?? '—' }}</strong></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">ORDER DETAILS</h5>
                </div>
                <div class="card-body d-grid gap-3">
                    @foreach ($barcodeWorkOrder->items as $item)
                        <div class="border rounded p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Item {{ $loop->iteration }}</h6><span
                                    class="badge bg-primary">{{ $item->type }}</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-6 col-md-3 col-xl-2"><small class="text-muted d-block">Number of
                                        Rolls</small><strong>{{ $item->number_of_rolls }}</strong></div>
                                <div class="col-6 col-md-3 col-xl-2"><small class="text-muted d-block">Stickers per
                                        Roll</small><strong>{{ $item->stickers_per_roll }}</strong></div>
                                <div class="col-6 col-md-3 col-xl-2"><small class="text-muted d-block">Sticker
                                        Length</small><strong>{{ rtrim(rtrim(number_format($item->sticker_length, 2, '.', ''), '0'), '.') }}
                                        mm</strong></div>
                                <div class="col-6 col-md-3 col-xl-2"><small class="text-muted d-block">Sticker
                                        Width</small><strong>{{ rtrim(rtrim(number_format($item->sticker_width, 2, '.', ''), '0'), '.') }}
                                        mm</strong></div>
                                <div class="col-6 col-md-3 col-xl-2"><small
                                        class="text-muted d-block">Gap</small><strong>{{ $item->gap === 'with_gap' ? 'With Gap' : 'Without Gap' }}</strong>
                                </div>
                                <div class="col-6 col-md-3 col-xl-2"><small class="text-muted d-block">Gap
                                        (mm)</small><strong>{{ $item->gap_mm ? rtrim(rtrim(number_format($item->gap_mm, 2, '.', ''), '0'), '.') . ' mm' : '—' }}</strong>
                                </div>
                                <div class="col-6 col-md-3 col-xl-2"><small class="text-muted d-block">Is
                                        Printing</small><strong>{{ $item->is_printing ? 'Yes' : 'No' }}</strong></div>
                                <div class="col-6 col-md-3 col-xl-3"><small class="text-muted d-block">Printing
                                        Colors</small><strong>{{ $item->printing_colors ? ucwords(str_replace('_', ' ', $item->printing_colors)) : '—' }}</strong>
                                </div>
                                <div class="col-12 col-xl-7"><small
                                        class="text-muted d-block">Remarks</small><strong>{{ $item->remarks ?: '—' }}</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
