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
                                        <div class="col-md-12 position-relative mb-3">

                                            <!-- Center Heading -->
                                            <div class="text-center">
                                                <h5 class="mb-0">Production Progress</h5>
                                            </div>

                                            <!-- Right Side Button -->
                                            <div class="position-absolute top-50 end-0 translate-middle-y">
                                                <button type="button" class="btn btn-sm btn-primary" id="addReelBtn">
                                                    + Add Reel
                                                </button>
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
                                                        <div class="col-md-3 mt-2">
                                                            <x-label for="production_qty" name="{{ __('Quantity') }}" />
                                                            <input type="number" name="production_qty"
                                                                id="production_qty" value="" class="form-control">
                                                        </div>
                                                        <div class="col-md-3 mt-2">
                                                            <x-label for="user_id" name="{{ __('Produced By') }}" />
                                                            <div class="input-group">
                                                                <x-dropdown-entered :showSelectOptionAll=true
                                                                    :required="true" :selected="$productionItemMaster->assigned_production_user_id" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3  mt-2">
                                                            <x-label for="machines" name="{{ __('Machine') }}" />
                                                            <div class="input-group">
                                                                <x-dropdown-machines dropdownName='machines'
                                                                    :showSelectOptionAll=true :required="true"
                                                                    :selected="$productionItemMaster->assigned_machine_id" />
                                                            </div>
                                                        </div>
                                                         <div class="col-md-3 mt-2">
                                                            <x-label for="real_number" name="{{ __('Real') }}" />
                                                            <select class="form-select single-select-clear-field"
                                                                name="real_number" data-placeholder="Choose Real Number">
                                                                <option value="">-- Select Real Number --</option>
                                                                @foreach ($reals as $real)
                                                                    <option value="{{ $real->id }}"
                                                                        title="{{ $real->formatted_id }} 
        | Brand: {{ $real->brandRelation->name ?? '-' }} 
        | Category: {{ $real->categoryRelation->name ?? '-' }} 
        | GSM: {{ $real->gsm ?? '-' }} 
        | Width: {{ $real->width ?? '-' }} 
        | Length: {{ $real->length ?? '-' }} 
        | Weight: {{ $real->weight ?? '-' }}
        | Subcode: {{ $real->subcode ?? '-' }}"
                                                                        {{ old('real_number') == $real->id ? 'selected' : '' }}>
                                                                        {{ $real->formatted_id }} - {{ $real->real_no }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
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
