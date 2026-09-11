@extends('layouts.app')
@section('title', 'Usage - ' . $stock->stock_code)
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reels', 'Physical Stock', 'Production Usage']" />
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $stock->stock_code }}</h5>
                        <small class="text-muted">{{ $stock->reel->code }} · {{ $stock->warehouse?->name }}</small>
                    </div>
                    <a href="{{ request()->routeIs('reels.finished.*') ? route('reels.finished.index') : route('reels.manage.stock', $stock->reel_id) }}"
                        class="btn btn-secondary">Back</a>
                </div>
                <div class="card-body">
                    @php
                        $latestUsage = $stock->usages->sortBy([
                            ['created_at', 'desc'],
                            ['id', 'desc'],
                        ])->first();
                        $stockWidthSplits = $latestUsage
                            ? max(1, (int) $latestUsage->output_roll_count)
                            : ((float) $stock->cut_width > 0
                                ? max(1, (int) floor((float) $stock->reel->width / (float) $stock->cut_width))
                                : 1);
                        $physicalStockLength = $latestUsage
                            ? max(0, ((float) $latestUsage->balance_before - (float) $latestUsage->consumed_length) / $stockWidthSplits)
                            : (float) $stock->balance_length / $stockWidthSplits;
                    @endphp
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="border rounded p-3"><small class="text-muted">Status</small>
                                <div class="fw-bold text-capitalize">{{ $stock->status }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3"><small class="text-muted">Original Length</small>
                                <div class="fw-bold">{{ number_format($stock->original_length, 2) }} m</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3"><small class="text-muted">Wastage Length</small>
                                <div class="fw-bold">{{ number_format($physicalStockLength, 2) }} m</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3"><small class="text-muted">Usage Entries</small>
                                <div class="fw-bold">{{ $stock->usages->count() }}</div>
                            </div>
                        </div>
                    </div>
                    @if (request()->routeIs('reels.finished.*'))
                        <div class="row g-3">
                            @forelse($stock->usages as $usage)
                                @php
                                    $widthSplits = max(1, (int) $usage->output_roll_count);
                                    $actualAvailableLength = (float) $usage->total_output_length / $widthSplits;
                                    $actualUsedLength = (float) $usage->consumed_length / $widthSplits;
                                    $actualBalanceAfter = max(
                                        0,
                                        ((float) $usage->balance_before - (float) $usage->consumed_length) /
                                        $widthSplits,
                                    );
                                @endphp
                                <div class="col-12">
                                    <div class="card h-100 mb-0 border shadow-none">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Production #{{ $usage->production_id }}</strong>
                                                <div class="small text-muted">
                                                    {{ $usage->created_at->format('d M Y h:i a') }}
                                                </div>
                                            </div>
                                            <span
                                                class="badge bg-{{ $usage->resulting_status === 'finished' ? 'secondary' : 'warning' }}">{{ ucfirst($usage->resulting_status) }}</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-sm-4"><small
                                                        class="text-muted d-block">Product</small><strong>{{ $usage->production?->item?->item_name ?? ($usage->production?->item?->name ?? '—') }}</strong>
                                                </div>
                                                <div class="col-sm-4"><small
                                                        class="text-muted d-block">Machine</small><strong>{{ $usage->machine?->machine_name ?? '—' }}</strong>
                                                </div>
                                                <div class="col-sm-4"><small class="text-muted d-block">Source
                                                        Status</small><span
                                                        class="badge bg-{{ $usage->source_status === 'full' ? 'primary' : 'warning' }}">{{ ucfirst($usage->source_status) }}</span>
                                                </div>
                                                <div class="col-sm-4"><small
                                                        class="text-muted d-block">Quantity</small><strong>{{ number_format($usage->production_quantity, 2) }}</strong>
                                                </div>
                                                <div class="col-sm-4"><small class="text-muted d-block">Width
                                                        Splits</small><strong>{{ $usage->output_roll_count }}</strong>
                                                </div>
                                                <div class="col-sm-4"><small class="text-muted d-block">Roll
                                                        Length</small><strong>{{ number_format($usage->roll_length, 2) }}
                                                        m</strong></div>
                                                <div class="col-sm-4"><small class="text-muted d-block">Output
                                                        Width</small><strong>{{ number_format($usage->output_roll_width, 2) }}
                                                        mm</strong></div>
                                                <div class="col-sm-4"><small class="text-muted d-block">Width
                                                        Waste</small><strong>{{ number_format($usage->width_waste, 2) }}
                                                        mm</strong></div>
                                                <div class="col-sm-4"><small class="text-muted d-block">Available
                                                        Capacity</small><strong>{{ number_format($actualAvailableLength, 2) }}
                                                        m</strong></div>
                                                <div class="col-sm-4"><small class="text-muted d-block">Total
                                                        Usage</small><strong>{{ number_format($actualUsedLength, 2) }}
                                                        m</strong></div>
                                                <div class="col-sm-4"><small class="text-muted d-block">Balance length After
                                                        Production</small><strong>{{ number_format($actualBalanceAfter, 2) }}
                                                        m</strong></div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                                            <small
                                                class="{{ $usage->status_selection_type === 'manual' ? 'text-primary' : 'text-muted' }}">{{ ucfirst($usage->status_selection_type ?? 'automatic') }}
                                                status</small>
                                            <a href="{{ route('item.production.edit', $usage->production_id) }}"
                                                class="btn btn-sm btn-outline-primary"><i class="bx bx-show me-1"></i>View
                                                Production</a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center text-muted border rounded py-5">No production usage recorded.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Sl No.</th>
                                        <th>Date</th>
                                        <th>Production</th>
                                        <th>Product</th>
                                        <th>Machine</th>
                                        <th>Source Status</th>
                                        <th>Quantity</th>
                                        <th>Roll Length (m)</th>
                                        <th>Output Width (mm)</th>
                                        <th>Width Splits</th>
                                        <th>Available Capacity (m)</th>
                                        <th>Total Usage (m)</th>
                                        <th>Width Waste (mm)</th>
                                        <th>Balance (m)</th>
                                        <th>Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stock->usages as $usage)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $usage->created_at->format('d M Y h:i a') }}</td>
                                            <td><a
                                                    href="{{ route('item.production.edit', $usage->production_id) }}">#{{ $usage->production_id }}</a>
                                            </td>
                                            <td>{{ $usage->production?->item?->item_name ?? ($usage->production?->item?->name ?? '—') }}
                                            </td>
                                            <td>{{ $usage->machine?->machine_name ?? '—' }}</td>
                                            <td><span
                                                    class="badge bg-{{ $usage->source_status === 'full' ? 'success' : 'warning' }}">{{ ucfirst($usage->source_status) }}</span>
                                            </td>
                                            <td>{{ number_format($usage->production_quantity, 2) }}</td>
                                            <td>{{ number_format($usage->roll_length, 2) }} m</td>
                                            <td>{{ number_format($usage->output_roll_width, 2) }} mm</td>
                                            <td>{{ $usage->output_roll_count }}</td>
                                            <td>{{ number_format($usage->total_output_length, 2) }} m</td>
                                            <td>{{ number_format($usage->consumed_length, 2) }} m</td>
                                            <td>{{ number_format($usage->width_waste, 2) }} mm</td>
                                            <td>{{ number_format($usage->balance_before, 2) }} →
                                                {{ number_format($usage->balance_after, 2) }} m
                                            </td>
                                            <td><span
                                                    class="badge bg-{{ $usage->resulting_status === 'finished' ? 'secondary' : 'warning' }}">{{ ucfirst($usage->resulting_status) }}</span>
                                                <div
                                                    class="small mt-1 {{ $usage->status_selection_type === 'manual' ? 'text-primary' : 'text-muted' }}">
                                                    {{ ucfirst($usage->status_selection_type ?? 'automatic') }}
                                                    @if ($usage->status_selection_type === 'manual' && $usage->calculated_status)
                                                        (calculated: {{ ucfirst($usage->calculated_status) }})
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="15" class="text-center text-muted">No production usage recorded.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection