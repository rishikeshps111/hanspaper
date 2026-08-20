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
                                                        action="{{ route('item.production.store-production') }}"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        @method('POST')
                                                        <input type="hidden" name="production_id"
                                                            value="{{ $productionItemMaster->id }}">
                                                        <div class="col-md-4 mt-2">
                                                            <x-label for="production_qty" name="{{ __('Quantity') }}" />
                                                            <input type="number" name="production_qty"
                                                                id="production_qty" value="" class="form-control">
                                                        </div>
                                                        <div class="col-md-4 mt-2">
                                                            <x-label for="user_id" name="{{ __('Produced By') }}" />
                                                            <div class="input-group">
                                                                <x-dropdown-entered :showSelectOptionAll=true
                                                                    :required="true" :selected="$productionItemMaster->assigned_production_user_id" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4  mt-2">
                                                            <x-label for="machines" name="{{ __('Machine') }}" />
                                                            <div class="input-group">
                                                                <x-dropdown-machines dropdownName='machines'
                                                                    :showSelectOptionAll=true :required="true"
                                                                    :selected="$productionItemMaster->assigned_machine_id" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 mt-2">
                                                            <x-label for="reel_stock_id" name="{{ __('Physical Reel Stock') }}" />
                                                            <select class="form-select single-select-clear-field" id="reel_stock_id"
                                                                name="reel_stock_id" data-placeholder="Choose Full or Bit Reel" required>
                                                                <option value=""></option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mt-2">
                                                            <x-label for="roll_length" name="{{ __('Roll Length (m)') }}" />
                                                            <input type="number" name="roll_length" id="roll_length"
                                                                class="form-control" min="0.001" step="0.001" required>
                                                        </div>
                                                        <div class="col-md-3 mt-2">
                                                            <x-label for="output_roll_width" name="{{ __('Output Roll Width (mm)') }}" />
                                                            <input type="number" name="output_roll_width" id="output_roll_width"
                                                                class="form-control" min="0.001" step="0.001" required>
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <x-label for="reel_status_after_usage" name="{{ __('Reel Status After Usage') }}" />
                                                            <select name="reel_status_after_usage" id="reel_status_after_usage"
                                                                class="form-select" required>
                                                                <option value="">Select status</option>
                                                                <option value="bit">Bit</option>
                                                                <option value="finished">Finished</option>
                                                            </select>
                                                            <input type="hidden" name="reel_status_selection_type"
                                                                id="reel_status_selection_type" value="automatic">
                                                            <small id="reelStatusSelectionHelp" class="text-muted">Automatically calculated from the remaining length.</small>
                                                            <div id="reelStatusAfterUsageError" class="text-danger small mt-1"></div>
                                                        </div>
                                                        <div class="col-md-12 mt-3">
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
                                                                    <div class="col-md"><small class="text-muted">Resulting Status</small><div><span class="badge bg-secondary" id="previewStatus">—</span></div></div>
                                                                </div>
                                                            </div>
                                                        </div>
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


@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="{{ versionedAsset('custom/js/items/production.js') }}"></script>
    <script>
        $(function () {
            let reelStatusManuallySelected = false;

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

            const calculateReelCut = function () {
                const option = $('#reel_stock_id option:selected');
                const stock = $('#reel_stock_id').select2('data')[0] || {};
                const sourceWidth = parseFloat(stock.width ?? option.data('width')) || 0;
                const balance = parseFloat(stock.balance ?? option.data('balance')) || 0;
                const existingCutWidth = parseFloat(stock.cut_width ?? option.data('cut-width')) || 0;
                const rollLength = parseFloat($('#roll_length').val()) || 0;
                const quantity = parseFloat($('#production_qty').val()) || 0;
                const outputWidth = parseFloat($('#output_roll_width').val()) || 0;
                const rollCount = outputWidth > 0 ? Math.floor(sourceWidth / outputWidth) : 0;
                const totalLength = existingCutWidth > 0 ? balance : balance * rollCount;
                const usage = quantity * rollLength;
                const waste = rollCount > 0 ? sourceWidth - (outputWidth * rollCount) : 0;
                const remaining = Math.max(0, totalLength - usage);
                const possibleRolls = rollLength > 0 ? Math.floor(totalLength / rollLength) : 0;
                const sameCutWidth = existingCutWidth === 0 || Math.abs(existingCutWidth - outputWidth) < 0.0001;
                const valid = sourceWidth > 0 && rollLength > 0 && quantity > 0 && usage <= totalLength &&
                    outputWidth > 0 && outputWidth <= sourceWidth && rollCount > 0 && sameCutWidth;
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
                $('#previewBalance').text(balance ? balance.toFixed(2) + ' m' : '—');
                $('#previewRollCount').text(rollCount);
                $('#previewTotalLength').text(totalLength.toFixed(2) + ' m');
                $('#previewUsage').text(usage.toFixed(2) + ' m');
                $('#previewPossibleRolls').text(possibleRolls);
                $('#previewWaste').text(Math.max(0, waste).toFixed(2) + ' mm');
                $('#previewRemaining').text(sourceWidth ? remaining.toFixed(2) + ' m' : '—');
                $('#previewStatus').text(valid ? (remaining <= 0 ? 'Finished' : 'Bit') : '—')
                    .attr('class', 'badge ' + (valid ? (remaining <= 0 ? 'bg-secondary' : 'bg-warning text-dark') : 'bg-secondary'));
            };

            const applyStoredCutWidth = function () {
                reelStatusManuallySelected = false;
                const option = $('#reel_stock_id option:selected');
                const stock = $('#reel_stock_id').select2('data')[0] || {};
                const cutWidth = parseFloat(stock.cut_width ?? option.data('cut-width')) || 0;
                const widthInput = $('#output_roll_width');
                if (cutWidth > 0) {
                    widthInput.val(cutWidth.toFixed(3)).prop('readonly', true);
                } else {
                    widthInput.val('').prop('readonly', false);
                }
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
