@extends('layouts.app')
@section('title', $workOrder ? 'Edit Barcode Work Order' : 'Create Barcode Work Order')

@section('content')
    @php
        $itemRows = old(
            'items',
            $workOrder?->items
                ->map(
                    fn($item) => [
                        'number_of_rolls' => $item->number_of_rolls,
                        'stickers_per_roll' => $item->stickers_per_roll,
                        'sticker_length' => $item->sticker_length,
                        'sticker_width' => $item->sticker_width,
                        'type' => $item->type,
                        'gap' => $item->gap,
                        'gap_mm' => $item->gap_mm,
                        'is_printing' => $item->is_printing ? 'yes' : 'no',
                        'printing_colors' => $item->printing_colors,
                        'remarks' => $item->remarks,
                    ],
                )
                ->values()
                ->all() ?? [['number_of_rolls' => 1, 'type' => 'DT', 'gap' => 'without_gap', 'is_printing' => 'no']],
        );
    @endphp
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Barcode WorkOrder', $workOrder ? 'Edit Work Order' : 'Create Work Order']" />
            @include('layouts.session')

            <form id="barcodeWorkOrderForm" method="POST"
                action="{{ $workOrder ? route('barcode-work-orders.update', $workOrder) : route('barcode-work-orders.store') }}">
                @csrf
                @if ($workOrder)
                    @method('PUT')
                @endif
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $workOrder ? 'EDIT ' . $workOrder->code : 'CREATE BARCODE WORK ORDER' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id"
                                    class="form-select form-select2 @error('customer_id') is-invalid @enderror"
                                    data-placeholder="Select Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" @selected((string) old('customer_id', $workOrder?->customer_id) === (string) $customer->id)>
                                            {{ trim($customer->first_name . ' ' . $customer->last_name) }}{{ $customer->mobile ? ' - ' . $customer->mobile : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Representative <span class="text-danger">*</span></label>
                                <select name="representative_id"
                                    class="form-select form-select2 @error('representative_id') is-invalid @enderror"
                                    data-placeholder="Select Representative">
                                    <option value=""></option>
                                    @foreach ($representatives as $representative)
                                        <option value="{{ $representative->id }}" @selected((string) old('representative_id', $workOrder?->representative_id) === (string) $representative->id)>
                                            {{ $representative->full_name }}{{ $representative->mobile ? ' - ' . $representative->mobile : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('representative_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3"><label class="form-label">Date <span
                                        class="text-danger">*</span></label><input type="date" name="work_order_date"
                                    class="form-control @error('work_order_date') is-invalid @enderror"
                                    value="{{ old('work_order_date', $workOrder?->work_order_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                                @error('work_order_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3"><label class="form-label">Due Date <span
                                        class="text-danger">*</span></label><input type="date" name="due_date"
                                    class="form-control @error('due_date') is-invalid @enderror"
                                    value="{{ old('due_date', $workOrder?->due_date?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d')) }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">ORDER DETAILS</h5><small class="text-muted">Add one or more barcode roll
                                specifications.</small>
                        </div><button type="button" id="addOrderItem" class="btn btn-primary"><i
                                class="bx bx-plus me-1"></i>Add Item</button>
                    </div>
                    <div class="card-body">
                        <div id="orderItems" class="d-grid gap-3"></div>
                        @error('items')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2"><a
                            href="{{ route('barcode-work-orders.index') }}"
                            class="btn btn-outline-secondary">Cancel</a><button type="submit"
                            class="btn btn-success px-4">{{ $workOrder ? 'Update' : 'Save' }} Work Order</button></div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            $('.form-select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true
            });
            let itemNumber = 0;
            const validationErrors = @json($errors->getMessages());
            const initialItems = @json($itemRows);
            const esc = value => $('<div>').text(value ?? '').html();
            const selected = (value, expected) => String(value ?? '') === expected ? 'selected' : '';
            const error = (index, field) => {
                const message = validationErrors[`items.${index}.${field}`]?.[0];
                return message ? `<div class="invalid-feedback d-block">${esc(message)}</div>` : '';
            };
            const invalid = (index, field) => validationErrors[`items.${index}.${field}`] ? 'is-invalid' : '';

            const addItem = (item = {}, errorIndex = null) => {
                const index = itemNumber++,
                    hasGap = item.gap === 'with_gap',
                    isPrinting = item.is_printing === 'yes' || item.is_printing === true;
                $('#orderItems').append(`<div class="order-item border rounded p-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Item <span class="item-position"></span></h6><button type="button" class="btn btn-sm btn-outline-danger remove-order-item" title="Remove"><i class="bx bx-trash"></i></button></div>
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3"><label class="form-label">Number of Rolls <span class="text-danger">*</span></label><input type="number" class="form-control ${invalid(errorIndex,'number_of_rolls')}" name="items[${index}][number_of_rolls]" min="1" value="${esc(item.number_of_rolls ?? 1)}">${error(errorIndex,'number_of_rolls')}</div>
                <div class="col-sm-6 col-lg-3"><label class="form-label">Stickers per Roll <span class="text-danger">*</span></label><input type="number" class="form-control ${invalid(errorIndex,'stickers_per_roll')}" name="items[${index}][stickers_per_roll]" min="1" value="${esc(item.stickers_per_roll)}">${error(errorIndex,'stickers_per_roll')}</div>
                <div class="col-sm-6 col-lg-3"><label class="form-label">Sticker Length (mm) <span class="text-danger">*</span></label><input type="number" class="form-control ${invalid(errorIndex,'sticker_length')}" name="items[${index}][sticker_length]" min="0.01" step="0.01" value="${esc(item.sticker_length)}">${error(errorIndex,'sticker_length')}</div>
                <div class="col-sm-6 col-lg-3"><label class="form-label">Sticker Width (mm) <span class="text-danger">*</span></label><input type="number" class="form-control ${invalid(errorIndex,'sticker_width')}" name="items[${index}][sticker_width]" min="0.01" step="0.01" value="${esc(item.sticker_width)}">${error(errorIndex,'sticker_width')}</div>
                <div class="col-sm-6 col-lg-3"><label class="form-label">Type <span class="text-danger">*</span></label><select class="form-select ${invalid(errorIndex,'type')}" name="items[${index}][type]"><option value="DT" ${selected(item.type,'DT')}>DT</option><option value="PROMO" ${selected(item.type,'PROMO')}>PROMO</option></select>${error(errorIndex,'type')}</div>
                <div class="col-sm-6 col-lg-2"><label class="form-label">Gap <span class="text-danger">*</span></label><select class="form-select item-gap ${invalid(errorIndex,'gap')}" name="items[${index}][gap]"><option value="without_gap" ${selected(item.gap,'without_gap')}>Without Gap</option><option value="with_gap" ${selected(item.gap,'with_gap')}>With Gap</option></select>${error(errorIndex,'gap')}</div>
                <div class="col-sm-6 col-lg-2"><label class="form-label">Gap (mm)</label><input type="number" class="form-control item-gap-mm ${invalid(errorIndex,'gap_mm')}" name="items[${index}][gap_mm]" min="0.01" step="0.01" value="${esc(item.gap_mm)}" ${hasGap ? '' : 'disabled'}>${error(errorIndex,'gap_mm')}</div>
                <div class="col-sm-6 col-lg-2"><label class="form-label">Is Printing <span class="text-danger">*</span></label><select class="form-select item-printing ${invalid(errorIndex,'is_printing')}" name="items[${index}][is_printing]"><option value="no" ${!isPrinting ? 'selected' : ''}>No</option><option value="yes" ${isPrinting ? 'selected' : ''}>Yes</option></select>${error(errorIndex,'is_printing')}</div>
                <div class="col-sm-6 col-lg-3"><label class="form-label">Printing Colors</label><select class="form-select item-printing-colors ${invalid(errorIndex,'printing_colors')}" name="items[${index}][printing_colors]" ${isPrinting ? '' : 'disabled'}><option value="">Select Colors</option><option value="single_color" ${selected(item.printing_colors,'single_color')}>Single Color</option><option value="two_color" ${selected(item.printing_colors,'two_color')}>2 Color</option><option value="multi_color" ${selected(item.printing_colors,'multi_color')}>Multi Color</option></select>${error(errorIndex,'printing_colors')}</div>
                <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control ${invalid(errorIndex,'remarks')}" name="items[${index}][remarks]" rows="3" maxlength="500">${esc(item.remarks)}</textarea>${error(errorIndex,'remarks')}</div>
            </div></div>`);
                renumberItems();
            };
            const renumberItems = () => $('.order-item .item-position').each((index, element) => $(element).text(
                index + 1));
            initialItems.forEach((item, index) => addItem(item, index));
            $('#addOrderItem').on('click', () => addItem());
            $(document).on('change', '.item-gap', function() {
                const input = $(this).closest('.order-item').find('.item-gap-mm'),
                    enabled = this.value === 'with_gap';
                input.prop('disabled', !enabled);
                if (!enabled) input.val('');
            });
            $(document).on('change', '.item-printing', function() {
                const input = $(this).closest('.order-item').find('.item-printing-colors'),
                    enabled = this.value === 'yes';
                input.prop('disabled', !enabled);
                if (!enabled) input.val('');
            });
            $(document).on('click', '.remove-order-item', function() {
                if ($('.order-item').length === 1) {
                    swal('One item required', 'A work order must contain at least one order item.',
                        'info');
                    return;
                }
                $(this).closest('.order-item').remove();
                renumberItems();
            });
        });
    </script>
@endsection
