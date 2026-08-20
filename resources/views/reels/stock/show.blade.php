@extends('layouts.app')
@section('title', $stock->stock_code)
@section('content')
    <div class="page-wrapper">
        <div class="page-content"><x-breadcrumb :langArray="['Reels', 'Reel Stock', 'Details']" />
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>{{ $stock->stock_code }}</h5><a href="{{ route('reels.stock.index') }}"
                        class="btn btn-secondary">Back</a>
                </div>F
                <div class="card-body">
                    <div class="mb-3"><strong>Provider:</strong> {{ $stock->provider?->name ?? '—' }}</div>
                    <div class="row g-3 mb-4">
                        @foreach(['Reel Code' => $stock->reel->code, 'Actual Code' => $stock->actual_code ?: '—', 'Warehouse' => $stock->warehouse->name, 'Original Length' => $stock->original_length . ' m', 'Balance Length' => $stock->balance_length . ' m', 'Status' => ucfirst($stock->status)] as $label => $value)
                            <div class="col-md-4"><small class="text-muted">{{ $label }}</small>
                                <div class="fw-bold">{{ $value }}</div>
                        </div>@endforeach
                    </div>
                    <h6>Stock Movements</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Provider</th>
                                    <th>Length (m)</th>
                                    <th>Before (m)</th>
                                    <th>After (m)</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stock->movements->sortByDesc('created_at') as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at }}</td>
                                        <td>{{ ucfirst($movement->transaction_type) }}</td>
                                        <td>{{ $movement->provider?->name ?? '—' }}</td>
                                        <td>{{ $movement->length }}</td>
                                        <td>{{ $movement->balance_before }}</td>
                                        <td>{{ $movement->balance_after }}</td>
                                        <td>{{ $movement->remarks }}</td>
                                </tr>@endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection