@extends('layouts.app')
@section('title', __('item.edit'))

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['item.production', 'Production And Packing Tracking']" />
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('Production And Packing Tracking') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-2">
                                <div class="col-md-3">
                                    <x-label for="item_id" name="{{ __('Customer Name') }}" />
                                    @if ($productionItemMaster->purchaseOrder)
                                        <div class="input-group">
                                            <br><b>{{ $productionItemMaster->purchaseOrder->party->first_name ?? 'N/A' }}</b>
                                        </div>
                                    @else
                                        <div class="input-group">
                                            <br><b>Not Available</b>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <x-label for="item_id" name="{{ __('Purchase Order') }}" />
                                    @if ($productionItemMaster->purchaseOrder)
                                        <div class="input-group">
                                            <br><b>{{ $productionItemMaster->purchaseOrder->purchase_order_id }}</b>
                                        </div>
                                    @else
                                        <div class="input-group">
                                            <br><b>Not Available</b>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <x-label for="item_id" name="{{ __('item.item_name') }}" />
                                    <div class="input-group">
                                        <br><b>{{ $productionItemMaster->item->name }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <x-label for="item_id" name="{{ __('Product Brand') }}" />
                                    <div class="input-group">
                                        <br><b>{{ $productionItemMaster->item->brand->name ?? 'Not Available' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Product Category') }}" />
                                    <div class="input-group">
                                        <br><b>{{ $productionItemMaster->item->category->name ?? 'Not Available' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Production Type') }}" />
                                    <div class="input-group">
                                        <br><b>{{ $productionItemMaster->production_type ?? 'Not Available' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Requested Quantity') }}" />
                                    <div class="input-group">
                                        <br><b>{{ $productionItemMaster->requested_qty ?? 'Not Available' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Ordered Date') }}" />
                                    <div class="input-group">
                                        <br><b>
                                            {{ $productionItemMaster->purchaseOrder->po_date
                                                ? \Carbon\Carbon::parse($productionItemMaster->purchaseOrder->po_date)->format('d M Y')
                                                : 'Not Available' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Due Date') }}" />
                                    <div class="input-group">
                                        <br><b>
                                            {{ $productionItemMaster->purchaseOrder->due_date
                                                ? \Carbon\Carbon::parse($productionItemMaster->purchaseOrder->due_date)->format('d M Y')
                                                : 'Not Available' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Production Remaining Qty') }}" />
                                    <div class="input-group">
                                        <br><b>
                                            {{ $productionItemMaster->requested_qty - $productionItemMaster->productionLists()->sum('quantity') }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Packing Remaining Qty') }}" />
                                    <div class="input-group">
                                        <br><b>
                                            {{ $productionItemMaster->requested_qty - $productionItemMaster->packingLists()->sum('quantity') }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Assigned Machine') }}" />
                                    <div class="input-group">
                                        <br><b>{{ $productionItemMaster->assignedMachine->machine_name ?? 'Not Assigned' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Assigned Production Employee') }}" />
                                    <div class="input-group">
                                        <br><b>{{ $productionItemMaster->assignedProductionUser->full_name ?? 'Not Assigned' }}</b>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Assigned Packing Employee') }}" />
                                    <div class="input-group">
                                        <br><b>{{ $productionItemMaster->assignedPackingUser->full_name ?? 'Not Assigned' }}</b>
                                    </div>
                                </div>
                                @php
                                    $status = $productionItemMaster->status ?? null;
                                    $badgeClasses = [
                                        'Assigning Pending' => 'badge bg-warning text-dark',
                                        'Pending' => 'badge bg-warning text-dark',
                                        'Packing Pending' => 'badge bg-warning text-dark',
                                        'Completed' => 'badge bg-success',
                                        'Partial' => 'badge bg-info text-dark',
                                        'Progress' => 'badge bg-primary',
                                        'In Progress' => 'badge bg-primary',
                                        'Cancelled' => 'badge bg-danger',
                                    ];
                                @endphp
                                <div class="col-md-3 mt-4">
                                    <x-label for="item_id" name="{{ __('Status') }}" />
                                    <div class="input-group">
                                        <br>
                                        @if ($status)
                                            <span
                                                class="badge rounded-pill px-3 py-2 fw-semibold {{ $badgeClasses[$status] ?? 'badge bg-secondary' }}">
                                                {{ $status }}
                                            </span>
                                        @else
                                            <span class="text-muted">Not Available</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if ($productionItemMaster->status != 'Assigning Pending')
                                <div class="col-md-12 mt-2">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="text-center">
                                                <h5 class="mb-0">Production Progress</h5>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="row mt-4">
                                                        <div class="col-md-12">
                                                            <x-label for="item_id"
                                                                name="{{ __('Production Completed Quantity') }}" />
                                                            <div class="input-group">
                                                                <br><b>{{ $productionItemMaster->productionLists->sum('quantity') }}</b>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 mt-2">
                                                            @php
                                                                $status =
                                                                    $productionItemMaster->production_status ?? null;
                                                                $badgeClasses = [
                                                                    'Pending' => 'badge bg-warning text-dark',
                                                                    'Completed' => 'badge bg-success',
                                                                    'Partial' => 'badge bg-primary',
                                                                    'Progress' => 'badge bg-info',
                                                                    'In Progress' => 'badge bg-info',
                                                                    'Cancelled' => 'badge bg-danger',
                                                                ];
                                                            @endphp
                                                            <x-label for="item_id" name="{{ __('Production Status') }}" />
                                                            <div class="input-group">
                                                                @if ($status)
                                                                    <span
                                                                        class="badge rounded-pill px-3 py-2 fw-semibold {{ $badgeClasses[$status] ?? 'badge bg-secondary' }}">
                                                                        {{ $status }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">Not Available</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-9 mt-3">
                                                    <form class="row g-3 needs-validation" id="productionForm"
                                                        action="{{ $activeRun ? route('item.production.store-production') : route('item.production.start-production') }}"
                                                        data-mode="{{ $activeRun ? 'finish' : 'start' }}"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        @method('POST')
                                                        <input type="hidden" name="production_id"
                                                            value="{{ $productionItemMaster->id }}">
                                                        @if ($activeRun)
                                                            <input type="hidden" name="production_run_id" value="{{ $activeRun->id }}">
                                                            <div class="col-md-12">
                                                                <div class="alert alert-primary border-primary">
                                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                                        <strong><i class="bx bx-play-circle me-1"></i>Production In Progress</strong>
                                                                        <span class="badge bg-primary">Started {{ $activeRun->started_at->format('d M Y h:i a') }}</span>
                                                                    </div>
                                                                    <div class="row g-3">
                                                                        <div class="col-md-3"><small class="d-block">Physical Reel</small><strong>{{ $activeRun->reelStock->stock_code }}</strong></div>
                                                                        <div class="col-md-3"><small class="d-block">Reel Code</small><strong>{{ $activeRun->reelStock->reel?->code }}</strong></div>
                                                                        <div class="col-md-2"><small class="d-block">Machine</small><strong>{{ $activeRun->machine?->machine_name }}</strong></div>
                                                                        <div class="col-md-2"><small class="d-block">Produced By</small><strong>{{ $activeRun->productionUser?->full_name }}</strong></div>
                                                                        <div class="col-md-2"><small class="d-block">Core</small><strong>{{ $activeRun->core?->code }} ({{ $activeRun->core?->name }})</strong></div>
                                                                        <div class="col-md-1"><small class="d-block">Width</small><strong>{{ number_format($activeRun->output_roll_width, 2) }} mm</strong></div>
                                                                        <div class="col-md-1"><small class="d-block">Roll Length</small><strong>{{ number_format($activeRun->roll_length, 2) }} m</strong></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="col-md-4 mt-2 {{ $activeRun ? '' : 'd-none' }}">
                                                            <x-label for="production_qty" name="{{ __('Quantity') }}" />
                                                            <input type="number" name="production_qty"
                                                                id="production_qty" min="1" step="1"
                                                                value="" class="form-control" {{ $activeRun ? 'required' : 'disabled' }}>
                                                        </div>
                                                        <div class="col-md-4 mt-2 {{ $activeRun ? 'd-none' : '' }}">
                                                            <x-label for="user_id" name="{{ __('Produced By') }}" />
                                                            <div class="input-group">
                                                                <x-dropdown-entered :showSelectOptionAll=true
                                                                    :required="true" :selected="$productionItemMaster->assigned_production_user_id" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mt-2 {{ $activeRun ? 'd-none' : '' }}">
                                                            <x-label for="machines" name="{{ __('Machine') }}" />
                                                            <div class="input-group">
                                                                <select class="form-select single-select-clear-field" id="machines" name="machines" data-placeholder="Choose machine" {{ $activeRun ? '' : 'required' }}>
                                                                    <option value=""></option>
                                                                    @foreach ($availableMachines as $machine)
                                                                        <option value="{{ $machine->id }}" @selected($productionItemMaster->assigned_machine_id == $machine->id)>{{ $machine->machine_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 mt-2">
                                                            <x-label for="reel_stock_id" name="{{ __('Physical Reel Stock') }}" />
                                                            <select class="form-select single-select-clear-field" id="reel_stock_id"
                                                                name="reel_stock_id" data-placeholder="Choose Full or Bit Reel" {{ $activeRun ? 'disabled' : 'required' }}>
                                                                @if ($activeRun)
                                                                    @php
                                                                        $activeStock = $activeRun->reelStock;
                                                                        $activeSourceWidth = (float) ($activeStock->reel?->width ?? 0);
                                                                        $activeCutWidth = (float) ($activeStock->cut_width ?? 0);
                                                                        $activeWidthSplits = $activeCutWidth > 0 ? (int) floor($activeSourceWidth / $activeCutWidth) : 0;
                                                                        $activeActualLength = $activeRun->source_reel_status === 'bit' && $activeWidthSplits > 0
                                                                            ? (float) $activeStock->balance_length / $activeWidthSplits
                                                                            : (float) $activeStock->balance_length;
                                                                    @endphp
                                                                    <option value="{{ $activeRun->reel_stock_id }}" selected
                                                                        data-status="{{ $activeRun->source_reel_status }}"
                                                                        data-width="{{ $activeRun->reelStock->reel?->width }}"
                                                                        data-balance="{{ $activeRun->reelStock->balance_length }}"
                                                                        data-cut-width="{{ $activeRun->reelStock->cut_width ?? 0 }}">
                                                                        {{ $activeRun->reelStock->stock_code }} | {{ $activeRun->reelStock->reel?->code }}
                                                                        @if ($activeRun->source_reel_status === 'bit')
                                                                            | {{ number_format($activeActualLength, 2) }} m
                                                                        @endif
                                                                    </option>
                                                                @else
                                                                    <option value=""></option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <x-label for="core_id" name="{{ __('Core') }}" />
                                                            <select class="form-select" id="core_id" name="core_id"
                                                                data-placeholder="Choose Core" {{ $activeRun ? 'disabled' : '' }}>
                                                                @if ($activeRun?->core)
                                                                    <option value="{{ $activeRun->core_id }}" selected
                                                                        data-available="{{ $activeRun->core->quantity }}"
                                                                        data-name="{{ $activeRun->core->name }}">
                                                                        {{ $activeRun->core->code }} | {{ $activeRun->core->name }}
                                                                    </option>
                                                                @else
                                                                    <option value=""></option>
                                                                @endif
                                                            </select>
                                                            <small class="text-muted" id="coreQuantityPreview">Select a core to see availability.</small>
                                                        </div>
                                                      
                                                        <div class="col-md-3 mt-2">
                                                            <x-label for="output_roll_width" name="{{ __('Output Roll Width (mm)') }}" />
                                                            <input type="number" name="output_roll_width" id="output_roll_width"
                                                                class="form-control" min="0.001" step="0.001"
                                                                value="{{ $activeRun?->output_roll_width }}" {{ $activeRun ? 'readonly' : 'required' }}>
                                                        </div>
                                                          <div class="col-md-3 mt-2">
                                                            <x-label for="roll_length" name="{{ __('Roll Length (m)') }}" />
                                                            <input type="number" name="roll_length" id="roll_length"
                                                                class="form-control" min="0.001" step="0.001"
                                                                value="{{ $activeRun?->roll_length }}" {{ $activeRun ? 'readonly' : 'required' }}>
                                                        </div>
                                                        <input type="hidden" name="reel_status_after_usage" id="reel_status_after_usage" value="">
                                                        <input type="hidden" name="reel_status_selection_type" id="reel_status_selection_type" value="manual">
                                                        <div id="reelStatusAfterUsageError" class="text-danger small mt-1"></div>
                                                        <div class="col-md-12 mt-3 {{ $activeRun ? '' : 'd-none' }}">
                                                            <div class="alert alert-light border mb-0" id="reelCutPreview">
                                                                <div class="row g-2">
                                                                    <div class="col-md"><small class="text-muted">Source Width (mm)</small><div class="fw-bold" id="previewSourceWidth">—</div></div>
                                                                    <div class="col-md"><small class="text-muted">Source Length (m)</small><div class="fw-bold" id="previewBalance">—</div></div>
                                                                    <div class="col-md"><small class="text-muted">Width Splits</small><div class="fw-bold" id="previewRollCount">0</div></div>
                                                                    <div class="col-md"><small class="text-muted">Available Capacity</small><div class="fw-bold" id="previewTotalLength">0.00 m</div></div>
                                                                    <div class="col-md"><small class="text-muted">Usage</small><div class="fw-bold" id="previewUsage">0.00 m</div></div>
                                                                    <div class="col-md"><small class="text-muted">Possible Rolls</small><div class="fw-bold" id="previewPossibleRolls">0</div></div>
                                                                    <div class="col-md"><small class="text-muted">Width Waste (mm)</small><div class="fw-bold" id="previewWaste">0.00 mm</div></div>
                                                                    <div class="col-md"><small class="text-muted">Remaining Length (m)</small><div class="fw-bold" id="previewRemaining">—</div></div>
                                                                    <div class="col-md"><small class="text-muted">Actual Remaining Length (m)</small><div class="fw-bold" id="previewPhysicalRemaining">—</div></div>
                                                                    <div class="col-md"><small class="text-muted">Resulting Status</small><div><span class="badge bg-secondary" id="previewStatus">—</span></div></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 mb-3 px-4 text-end">
                                                            <div class="gap-3">
                                                                <x-button type="submit" class="primary px-4"
                                                                    text="{{ $activeRun ? __('Update Production') : __('Start Production') }}" />
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <div class="row">
                                        <div class="col-md-12 d-flex justify-content-center">
                                            <h5>Packing Progress</h5>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="row mt-4">
                                                        <div class="col-md-12">
                                                            <x-label for="item_id"
                                                                name="{{ __('Packing Completed Quantity') }}" />
                                                            <div class="input-group">
                                                                <br><b>{{ $productionItemMaster->packingLists->sum('quantity') }}</b>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 mt-2">
                                                            @php
                                                                $status = $productionItemMaster->packing_status ?? null;
                                                                $badgeClasses = [
                                                                    'Pending' => 'badge bg-warning text-dark',
                                                                    'Completed' => 'badge bg-success',
                                                                    'Partial' => 'badge bg-primary',
                                                                    'Progress' => 'badge bg-info',
                                                                    'Cancelled' => 'badge bg-danger',
                                                                ];
                                                            @endphp
                                                            <x-label for="item_id" name="{{ __('Packing Status') }}" />
                                                            <div class="input-group">
                                                                @if ($status)
                                                                    <span
                                                                        class="badge rounded-pill px-3 py-2 fw-semibold {{ $badgeClasses[$status] ?? 'badge bg-secondary' }}">
                                                                        {{ $status }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">Not Available</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-9 mt-3">
                                                    <form class="row g-3 needs-validation" id="packingForm"
                                                        action="{{ route('item.production.store-packing') }}"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        @method('POST')
                                                        <input type="hidden" name="production_id"
                                                            value="{{ $productionItemMaster->id }}">
                                                        <div class="col-md-6 mt-2">
                                                            <x-label for="packed_qty" name="{{ __('Quantity') }}" />
                                                            <input type="number" name="packed_qty" id="packed_qty"
                                                                value="" class="form-control">
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <x-label for="packed_by" name="{{ __('Packed By') }}" />
                                                            <div class="input-group">
                                                                <x-dropdown-entered :selected="$productionItemMaster->assigned_packing_user_id"
                                                                    :showSelectOptionAll=true />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 mt-2"><x-label for="packing_box_id" name="{{ __('Packing Box') }}" /><select id="packing_box_id" name="packing_box_id" class="form-select"><option></option></select><small class="text-muted" id="boxStockSummary">Select a packing box.</small></div>
                                                        <div class="col-md-6 mt-2"><x-label for="packing_box_quantity" name="{{ __('Boxes Used') }}" /><input type="number" min="1" name="packing_box_quantity" id="packing_box_quantity" class="form-control" required></div>
                                                        <div class="col-md-6 mt-2"><x-label for="packing_cover_id" name="{{ __('Packing Cover') }}" /><select id="packing_cover_id" name="packing_cover_id" class="form-select"><option></option></select><small class="text-muted" id="coverStockSummary">Select a packing cover.</small></div>
                                                        <div class="col-md-6 mt-2"><x-label for="packing_cover_quantity" name="{{ __('Covers Used') }}" /><input type="number" min="1" name="packing_cover_quantity" id="packing_cover_quantity" class="form-control" required></div>
                                                        <div class="col-md-12 mb-3 px-4 text-end">
                                                            <div class="gap-3">
                                                                <x-button type="submit" class="primary px-4"
                                                                    text="{{ __('Save') }}" />
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-12 mt-4">
                                    <div class="row">
                                        <div class="col-md-12 d-flex justify-content-center">
                                            <h5>Assign Machine And Employees</h5>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-12 mt-3">
                                                    <form class="row g-3 needs-validation" id="assignForm"
                                                        action="{{ route('item.production.assign') }}"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        @method('POST')
                                                        <input type="hidden" name="production_id"
                                                            value="{{ $productionItemMaster->id }}">
                                                        <div class="col-md-4 mt-2">
                                                            <x-label for="assigned_machine" name="{{ __('Machine') }}" />
                                                            <div class="input-group">
                                                                <x-dropdown-machines dropdownName='assigned_machine'
                                                                    :showSelectOptionAll=true />
                                                            </div>
                                                        </div>
                                                        @php
                                                            $employees = App\Models\Employees\Employee::select(
                                                                'id',
                                                                'full_name',
                                                            )->get();
                                                        @endphp
                                                        <div class="col-md-4 mt-2">
                                                            <x-label for="assigned_production_user"
                                                                name="{{ __('Production Employee') }}" />
                                                            <div class="input-group">
                                                                <select class="form-select single-select-clear-field"
                                                                    name="assigned_production_user"
                                                                    data-placeholder="Choose Employee">
                                                                    <option value="">-- Select Packing Employee --
                                                                    </option>
                                                                    @foreach ($employees as $employee)
                                                                        <option value="{{ $employee->id }}"
                                                                            {{ old('assigned_production_user', $selectedProductionUser ?? '') == $employee->id ? 'selected' : '' }}>
                                                                            {{ $employee->full_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4 mt-2">
                                                            <x-label for="assigned_packing_user"
                                                                name="{{ __('Packing Employee') }}" />
                                                            <div class="input-group">
                                                                <select class="form-select single-select-clear-field"
                                                                    data-placeholder="Choose Employee"
                                                                    name="assigned_packing_user">
                                                                    <option value="">-- Select Packing Employee --
                                                                    </option>
                                                                    @foreach ($employees as $employee)
                                                                        <option value="{{ $employee->id }}"
                                                                            {{ old('assigned_packing_user', $selectedPackingUser ?? '') == $employee->id ? 'selected' : '' }}>
                                                                            {{ $employee->full_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12 mb-3 px-4 text-end">
                                                            <div class="gap-3">
                                                                <x-button type="submit" class="primary px-4"
                                                                    text="{{ __('Assign') }}" />
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12 mt-2">
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-center">
                                        <h5>Remarks</h5>
                                    </div>
                                    <div class="col-md-12">
                                        <h6>Production Remarks</h6>
                                        <div class="input-group mt-0">
                                            {{ $productionItemMaster->production_remarks ?? 'Not Available' }}
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <h6>Packing Remarks</h6>
                                        <div class="input-group mt-0">
                                            {{ $productionItemMaster->packing_remarks ?? 'Not Available' }}
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <h6>Dispatch Remarks</h6>
                                        <div class="input-group mt-0">
                                            {{ $productionItemMaster->dispatch_remarks ?? 'Not Available' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-4 text-end">
                                @if ($productionItemMaster->status !== 'Assigning Pending')
                                    <button class="btn btn-secondary print-btn"
                                        data-id="{{ $productionItemMaster->id }}">Print</button>';
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
    <!-- Import Modals -->
    
    <div class="modal fade" id="addRealModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Real</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="realModalBody">
                    <div class="text-center p-3">
                        Loading...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateProductionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Update Production</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Physical Reel Result <span class="text-danger">*</span></label>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><label class="border rounded p-3 w-100"><input class="form-check-input me-2 reel-result-option" type="radio" name="modal_reel_result" value="bit"> <strong>Bit</strong><small class="d-block text-muted ms-4">Keep the remaining reel for later production.</small></label></div>
                        <div class="col-md-6"><label class="border rounded p-3 w-100"><input class="form-check-input me-2 reel-result-option" type="radio" name="modal_reel_result" value="finished"> <strong>Finished</strong><small class="d-block text-muted ms-4">Treat all remaining reel material as wastage.</small></label></div>
                    </div>
                    <div class="card border mb-0"><div class="card-body"><div class="row g-3">
                        <div class="col-md-4"><small class="text-muted d-block">Width Splits</small><strong id="modalWidthSplits">0</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Remaining Output Length</small><strong id="modalRemainingOutput">0.00 m</strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Actual Remaining Length</small><strong id="modalPhysicalRemaining">0.00 m</strong></div>
                    </div></div></div>
                    <div class="alert alert-danger mt-3 mb-0 d-none" id="productionWastageSummary"><strong>Wastage:</strong> <span id="modalWastageOutput">0.00 m</span> output length; actual physical wastage <span id="modalPhysicalWastage">0.00 m</span>.</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="confirmProductionUpdate">Update Production</button></div>
            </div>
        </div>
    </div>


@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="{{ versionedAsset('custom/js/items/production.js') }}"></script>
    <script>
        $(function () {
            let reelStatusManuallySelected = false;
            let latestCutCalculation = null;

            const initPackingMaterial = function(selector, type) {
                $(selector).select2({theme:'bootstrap-5',width:'100%',allowClear:true,placeholder:'Choose '+(type==='box'?'Packing Box':'Packing Cover'),ajax:{url:@json(url('/packing-materials')).replace(/\/$/,'')+'/'+type+'/search',dataType:'json',delay:300,data:p=>({q:p.term||''}),processResults:r=>r}});
            };
            initPackingMaterial('#packing_box_id','box'); initPackingMaterial('#packing_cover_id','cover');
            const updatePackingMaterialSummary=function(){
                const box=$('#packing_box_id').select2('data')[0]||{},cover=$('#packing_cover_id').select2('data')[0]||{};
                $('#boxStockSummary').text(box.id ? `Available: ${box.available_quantity}` : 'Select a packing box.');
                $('#coverStockSummary').text(cover.id ? `Available: ${cover.available_quantity}` : 'Select a packing cover.');
            };
            $('#packing_box_id,#packing_cover_id').on('change',updatePackingMaterialSummary);

            const formatReelStockOption = function (option) {
                if (!option.id) return option.text;

                const status = String(option.status || $(option.element).data('status') || '').toLowerCase();
                let label = String(option.text || '').replace(/\s+/g, ' ').trim();

                const badgeClass = status === 'bit'
                    ? 'bg-warning text-dark'
                    : 'bg-primary';
                const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);

                return $('<span class="d-flex align-items-center justify-content-between gap-2 w-100">' +
                    '<span class="text-truncate">' + $('<div>').text(label).html() + '</span>' +
                    '<span class="badge ' + badgeClass + ' flex-shrink-0">' + statusLabel + '</span>' +
                    '</span>');
            };

            if ($('#reel_stock_id').hasClass('select2-hidden-accessible')) {
                $('#reel_stock_id').select2('destroy');
            }
            $('#reel_stock_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                placeholder: 'Choose Full or Bit Reel',
                ajax: {
                    url: @json(route('item.production.reel-stocks.search', [], false)),
                    dataType: 'json',
                    delay: 300,
                    data: params => ({
                        q: params.term || '',
                        page: params.page || 1
                    }),
                    processResults: response => response,
                    cache: true
                },
                templateResult: formatReelStockOption,
                templateSelection: formatReelStockOption
            });

            $('#core_id').select2({
                theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: 'Choose Core',
                ajax: {
                    url: @json(route('item.production.cores.search', [], false)), dataType: 'json', delay: 300,
                    data: params => ({q: params.term || ''}), processResults: response => response, cache: false
                }
            });

            const updateCorePreview = function () {
                const option = $('#core_id option:selected');
                const core = $('#core_id').select2('data')[0] || {};
                const available = parseInt(core.available_quantity ?? option.data('available')) || 0;
                const required = parseInt($('#production_qty').val()) || 0;
                const remaining = Math.max(0, available - required);
                $('#coreQuantityPreview').text($('#core_id').val()
                    ? `Available: ${available} | Required: ${required} | Remaining: ${remaining}`
                    : 'Select a core to see availability.').toggleClass('text-danger', required > available);
            };
            $('#core_id, #production_qty').on('change input', updateCorePreview);
            updateCorePreview();

            const calculateReelCut = function () {
                const option = $('#reel_stock_id option:selected');
                const stock = $('#reel_stock_id').select2('data')[0] || {};
                const sourceWidth = parseFloat(stock.width ?? option.data('width')) || 0;
                const balance = parseFloat(stock.balance ?? option.data('balance')) || 0;
                const existingCutWidth = parseFloat(stock.cut_width ?? option.data('cut-width')) || 0;
                const stockStatus = String(stock.status ?? option.data('status') ?? '').toLowerCase();
                const rollLength = parseFloat($('#roll_length').val()) || 0;
                const quantity = parseFloat($('#production_qty').val()) || 0;
                const outputWidth = parseFloat($('#output_roll_width').val()) || 0;
                const rollCount = outputWidth > 0 ? Math.floor(sourceWidth / outputWidth) : 0;
                const previousWidthSplits = existingCutWidth > 0
                    ? Math.max(1, Math.floor(sourceWidth / existingCutWidth))
                    : 1;
                const physicalAvailableLength = stockStatus === 'bit' && existingCutWidth > 0
                    ? balance / previousWidthSplits
                    : balance;
                const totalLength = physicalAvailableLength * rollCount;
                const usage = quantity * rollLength;
                const waste = rollCount > 0 ? sourceWidth - (outputWidth * rollCount) : 0;
                const remaining = Math.max(0, totalLength - usage);
                const physicalRemaining = rollCount > 0 ? remaining / rollCount : 0;
                const possibleRolls = rollLength > 0 ? Math.floor(totalLength / rollLength) : 0;
                const valid = sourceWidth > 0 && rollLength > 0 && quantity > 0 && usage <= totalLength &&
                    outputWidth > 0 && outputWidth <= sourceWidth && rollCount > 0;
                const calculatedStatus = remaining <= 0 ? 'finished' : 'bit';
                const statusSelect = $('#reel_status_after_usage');

                statusSelect.find('option[value="bit"]').prop('disabled', valid && remaining <= 0);
                if (valid && (!reelStatusManuallySelected || (statusSelect.val() === 'bit' && remaining <= 0))) {
                    statusSelect.val(calculatedStatus);
                    reelStatusManuallySelected = false;
                } else if (!valid && !reelStatusManuallySelected) {
                    statusSelect.val('');
                }
                $('#reelStatusSelectionHelp')
                    .text(reelStatusManuallySelected ? 'Manually selected. Calculated status: ' +
                        (valid ? calculatedStatus.charAt(0).toUpperCase() + calculatedStatus.slice(1) : '—') :
                        'Automatically calculated from the remaining length.')
                    .toggleClass('text-primary', reelStatusManuallySelected)
                    .toggleClass('text-muted', !reelStatusManuallySelected);
                $('#reel_status_selection_type').val(reelStatusManuallySelected ? 'manual' : 'automatic');

                $('#previewSourceWidth').text(sourceWidth ? sourceWidth.toFixed(2) + ' mm' : '—');
                $('#previewBalance').text(physicalAvailableLength ? physicalAvailableLength.toFixed(2) + ' m' : '—');
                $('#previewRollCount').text(rollCount);
                $('#previewTotalLength').text(totalLength.toFixed(2) + ' m');
                $('#previewUsage').text(usage.toFixed(2) + ' m');
                $('#previewPossibleRolls').text(possibleRolls);
                $('#previewWaste').text(Math.max(0, waste).toFixed(2) + ' mm');
                $('#previewRemaining').text(sourceWidth ? remaining.toFixed(2) + ' m' : '—');
                $('#previewPhysicalRemaining').text(sourceWidth ? physicalRemaining.toFixed(2) + ' m' : '—');
                $('#previewStatus').text(valid ? (remaining <= 0 ? 'Finished' : 'Bit') : '—')
                    .attr('class', 'badge ' + (valid ? (remaining <= 0 ? 'bg-secondary' : 'bg-warning text-dark') : 'bg-secondary'));
                latestCutCalculation = {valid, rollCount, remaining, physicalRemaining, totalLength, usage};
            };

            const applyStoredCutWidth = function () {
                reelStatusManuallySelected = false;
                const option = $('#reel_stock_id option:selected');
                const stock = $('#reel_stock_id').select2('data')[0] || {};
                const cutWidth = parseFloat(stock.cut_width ?? option.data('cut-width')) || 0;
                const widthInput = $('#output_roll_width');
                if ($('#productionForm').data('mode') === 'finish') {
                    widthInput.prop('readonly', true);
                    calculateReelCut();
                    return;
                }
                widthInput.val('').prop('readonly', false);
                calculateReelCut();
            };

            $('#reel_stock_id').on('change', applyStoredCutWidth);
            $('#reel_stock_id, #roll_length, #output_roll_width, #production_qty').on('change input', calculateReelCut);
            $('#reel_status_after_usage').on('change', function () {
                reelStatusManuallySelected = Boolean(this.value);
                calculateReelCut();
            });
            applyStoredCutWidth();

            $('#productionForm').off('submit').on('submit', function (event) {
                event.preventDefault();
                const form = $(this);
                $('#reelStatusAfterUsageError').empty();
                if (!$('#reel_stock_id').val()) {
                    Swal.fire('Error', 'Please select a Physical Reel Stock.', 'error');
                    return;
                }
                if (form.data('mode') === 'start' && !$('#core_id').val()) {
                    Swal.fire('Error', 'Please select a Core.', 'error');
                    return;
                }
                if (form.data('mode') === 'finish' && !form.data('update-confirmed')) {
                    calculateReelCut();
                    if (!latestCutCalculation?.valid) {
                        Swal.fire('Error', 'Enter a valid quantity within the available reel capacity.', 'error');
                        return;
                    }
                    $('input[name="modal_reel_result"]').prop('checked', false);
                    $('#modalWidthSplits').text(latestCutCalculation.rollCount);
                    $('#modalRemainingOutput').text(latestCutCalculation.remaining.toFixed(2) + ' m');
                    $('#modalPhysicalRemaining').text(latestCutCalculation.physicalRemaining.toFixed(2) + ' m');
                    $('#modalWastageOutput').text(latestCutCalculation.remaining.toFixed(2) + ' m');
                    $('#modalPhysicalWastage').text(latestCutCalculation.physicalRemaining.toFixed(2) + ' m');
                    $('#productionWastageSummary').addClass('d-none');
                    $('#updateProductionModal').modal('show');
                    return;
                }
                const button = form.find('button[type="submit"]');
                const originalHtml = button.html();
                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: new FormData(this),
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    headers: {'X-CSRF-TOKEN': form.find('input[name="_token"]').val()},
                    success: function (response) {
                        if (window.iziToast) {
                            iziToast.success({title: 'Success', message: response.message});
                        } else {
                            Swal.fire('Success', response.message, 'success');
                        }
                        if (response.redirect) window.location.href = response.redirect;
                    },
                    error: function (xhr) {
                        form.data('update-confirmed', false);
                        const errors = xhr.responseJSON?.errors;
                        $('#reelStatusAfterUsageError').text(errors?.reel_status_after_usage?.[0] || '');
                        const message = errors
                            ? Object.values(errors).flat()[0]
                            : (xhr.responseJSON?.message || 'Unable to save production.');
                        if (window.iziToast) {
                            iziToast.error({title: 'Error', message: message});
                        } else {
                            Swal.fire('Error', message, 'error');
                        }
                    },
                    complete: function () {
                        button.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            $('.reel-result-option').on('change', function () {
                $('#productionWastageSummary').toggleClass('d-none', this.value !== 'finished');
            });
            $('#confirmProductionUpdate').on('click', function () {
                const result = $('input[name="modal_reel_result"]:checked').val();
                if (!result) {
                    Swal.fire('Required', 'Choose Bit or Finished.', 'warning');
                    return;
                }
                $('#reel_status_after_usage').val(result);
                $('#reel_status_selection_type').val('manual');
                $('#updateProductionModal').modal('hide');
                $('#productionForm').data('update-confirmed', true).trigger('submit');
            });
        });

        // Open modal
        $(document).on('click', '#addReelBtn', function() {

            $('#addRealModal').modal('show');

            $.get("{{ route('reals.modal-create') }}", function(data) {
                $('#realModalBody').html(data);
                $('#modalRealForm .select3').select2({
                    width: '100%',
                    dropdownParent: $('#addRealModal')
                });
            });

        });

        $(document).on('submit', '#modalRealForm', function(e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(this);

            // Clear previous errors
            form.find('.text-danger').text('');

            $.ajax({
                url: "{{ route('reals.modal-store') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,

                success: function(response) {

                    if (response.success) {

                        let newOption = new Option(
                            response.real.text,
                            response.real.id,
                            true,
                            true
                        );

                        // Add title attribute manually
                        $(newOption).attr('title', response.real.title);

                        $('select[name="real_number"]')
                            .append(newOption)
                            .trigger('change');

                        $('#addRealModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Real Created Successfully'
                        });
                    }
                },

                error: function(xhr) {

                    if (xhr.status === 422) {

                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function(key, value) {
                            $('.error_' + key).text(value[0]);
                        });

                    }
                }
            });
        });
    </script>
@endsection
