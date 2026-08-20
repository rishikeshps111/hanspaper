@extends('layouts.app')
@section('title', 'New Reel Sale')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['Reels', 'Reel Sales', 'New Sale']"/>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">NEW REEL SALE</h5></div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('reels.sales.store') }}" id="saleForm">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select single-select-clear-field w-100" data-placeholder="Select Customer" required>
                                <option value=""></option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                        {{ trim($customer->first_name.' '.$customer->last_name) }} - {{ $customer->mobile }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                            <input type="date" name="sale_date" class="form-control" value="{{ old('sale_date', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">GST Applicable</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="is_gst_applicable" value="0">
                                <input class="form-check-input" type="checkbox" name="is_gst_applicable" id="gstApplicable"
                                       value="1" @checked(old('is_gst_applicable'))>
                                <label class="form-check-label" for="gstApplicable">Apply GST</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Sale Items</h6>
                        <button type="button" class="btn btn-primary btn-sm" id="openItemModal">
                            <i class="bx bx-plus"></i> Add Item
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Reel Product</th>
                                    <th>Warehouse Allocation</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Selling Price</th>
                                    <th style="width:150px">Discount Amount</th>
                                    <th class="text-end">Amount</th>
                                    <th style="width:65px">Action</th>
                                </tr>
                            </thead>
                            <tbody id="saleItems">
                                <tr id="emptyRow"><td colspan="7" class="text-center text-muted py-4">No items added. Click “Add Item” to begin.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-5 col-lg-4">
                            <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong id="subtotalDisplay">0.00</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Discount</span><strong id="discountDisplay">0.00</strong></div>
                            <div id="gstFields" class="d-none">
                                <div class="row g-2 mb-2">
                                    <div class="col-6"><label class="form-label mb-1">SGST %</label><input type="number" name="sgst_percentage" id="sgstPercentage" class="form-control form-control-sm" min="0" max="100" step="0.01" value="{{ old('sgst_percentage', 0) }}"></div>
                                    <div class="col-6"><label class="form-label mb-1">CGST %</label><input type="number" name="cgst_percentage" id="cgstPercentage" class="form-control form-control-sm" min="0" max="100" step="0.01" value="{{ old('cgst_percentage', 0) }}"></div>
                                </div>
                                <div class="d-flex justify-content-between mb-2"><span>SGST Amount</span><strong id="sgstDisplay">0.00</strong></div>
                                <div class="d-flex justify-content-between mb-2"><span>CGST Amount</span><strong id="cgstDisplay">0.00</strong></div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fs-5"><span>Total Amount</span><strong id="totalDisplay">0.00</strong></div>
                        </div>
                    </div>

                    <div class="mt-3"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea></div>
                    <div class="mt-4">
                        <button class="btn btn-primary px-5" id="submitSale" disabled>Complete Sale</button>
                        <a href="{{ route('reels.sales.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Reel Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Reel Product <span class="text-danger">*</span></label>
                    <select id="modalReelId" class="form-select w-100"><option value=""></option>
                        @foreach($reels as $reel)<option value="{{ $reel->id }}">{{ $reel->code }}</option>@endforeach
                    </select>
                </div>
                <div id="modalMessage" class="alert alert-info mb-0">Select a Reel product to see Full-stock counts.</div>
                <div id="modalAvailability" class="d-none">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Full Reel Availability</strong>
                        <span>Selling Price: <strong id="modalPrice">0.00</strong></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead><tr><th>Warehouse</th><th class="text-center">Available</th><th style="width:180px">Quantity</th></tr></thead>
                            <tbody id="modalWarehouseRows"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addModalItem" disabled>Add Item</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(function () {
    const availabilityBase = @json(url('/reels/sales/reels'));
    const oldItems = @json(old('items', []));
    const prefillReelId = @json($prefillReelId);
    const prefillWarehouseId = @json($prefillWarehouseId);
    const cart = [];
    const itemModal = new bootstrap.Modal(document.getElementById('itemModal'));
    let modalAvailability = [];
    let modalPrice = 0;

    $('#modalReelId').select2({theme:'bootstrap-5', width:'100%', dropdownParent:$('#itemModal'), placeholder:'Select Reel Product', allowClear:true});

    function money(value) { return (parseFloat(value) || 0).toFixed(2); }
    function escapeHtml(value) { return $('<div>').text(value ?? '').html(); }

    function calculate() {
        let subtotal = 0, discount = 0;
        cart.forEach(function (item, index) {
            const gross = item.quantity * item.price;
            let rowDiscount = parseFloat($(`.item-discount[data-index="${index}"]`).val()) || 0;
            rowDiscount = Math.max(0, Math.min(rowDiscount, gross));
            item.discount = rowDiscount;
            $(`.item-amount[data-index="${index}"]`).text(money(gross - rowDiscount));
            subtotal += gross; discount += rowDiscount;
        });
        const taxable = Math.max(0, subtotal - discount);
        const gst = $('#gstApplicable').is(':checked');
        const sgst = gst ? taxable * (parseFloat($('#sgstPercentage').val()) || 0) / 100 : 0;
        const cgst = gst ? taxable * (parseFloat($('#cgstPercentage').val()) || 0) / 100 : 0;
        $('#subtotalDisplay').text(money(subtotal));
        $('#discountDisplay').text(money(discount));
        $('#sgstDisplay').text(money(sgst));
        $('#cgstDisplay').text(money(cgst));
        $('#totalDisplay').text(money(taxable + sgst + cgst));
        $('#submitSale').prop('disabled', cart.length === 0);
    }

    function renderCart() {
        $('#saleItems').empty();
        if (!cart.length) {
            $('#saleItems').html('<tr id="emptyRow"><td colspan="7" class="text-center text-muted py-4">No items added. Click “Add Item” to begin.</td></tr>');
            calculate(); return;
        }
        cart.forEach(function (item, index) {
            const allocation = item.warehouses.map(w => `${escapeHtml(w.name)}: ${w.quantity}`).join('<br>');
            let inputs = `<input type="hidden" name="items[${index}][reel_id]" value="${item.reelId}">`;
            item.warehouses.forEach(function (warehouse, warehouseIndex) {
                inputs += `<input type="hidden" name="items[${index}][warehouse_quantities][${warehouseIndex}][warehouse_id]" value="${warehouse.id}">
                    <input type="hidden" name="items[${index}][warehouse_quantities][${warehouseIndex}][quantity]" value="${warehouse.quantity}">`;
            });
            const gross = item.quantity * item.price;
            $('#saleItems').append(`<tr>
                <td>${escapeHtml(item.code)}${inputs}</td>
                <td>${allocation}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">${money(item.price)}</td>
                <td><input type="number" name="items[${index}][discount]" class="form-control item-discount" data-index="${index}" min="0" max="${gross.toFixed(2)}" step="0.01" value="${money(item.discount)}"></td>
                <td class="text-end item-amount" data-index="${index}">${money(gross - item.discount)}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${index}"><i class="bx bx-trash"></i></button></td>
            </tr>`);
        });
        calculate();
    }

    function resetModal() {
        $('#modalReelId').val(null).trigger('change.select2');
        $('#modalWarehouseRows').empty();
        $('#modalAvailability').addClass('d-none');
        $('#modalMessage').removeClass('d-none alert-danger').addClass('alert-info').text('Select a Reel product to see Full-stock counts.');
        $('#addModalItem').prop('disabled', true);
        modalAvailability = []; modalPrice = 0;
    }

    function loadAvailability(reelId, quantities = {}) {
        $('#modalAvailability').addClass('d-none');
        $('#modalMessage').removeClass('d-none alert-danger').addClass('alert-info').text('Loading Full-stock availability...');
        $('#addModalItem').prop('disabled', true);
        return $.get(`${availabilityBase}/${reelId}/availability`).done(function (response) {
            modalPrice = parseFloat(response.selling_price) || 0;
            modalAvailability = response.availability;
            $('#modalPrice').text(money(modalPrice));
            $('#modalWarehouseRows').empty();
            response.availability.forEach(function (warehouse) {
                const quantity = Math.min(parseInt(quantities[warehouse.warehouse_id], 10) || 0, parseInt(warehouse.available, 10));
                $('#modalWarehouseRows').append(`<tr>
                    <td>${escapeHtml(warehouse.warehouse_name)}</td>
                    <td class="text-center"><span class="badge bg-success">${warehouse.available}</span></td>
                    <td><input type="number" class="form-control modal-quantity" data-id="${warehouse.warehouse_id}" data-name="${escapeHtml(warehouse.warehouse_name)}" min="0" max="${warehouse.available}" step="1" value="${quantity}"></td>
                </tr>`);
            });
            if (response.availability.length) {
                $('#modalMessage').addClass('d-none');
                $('#modalAvailability').removeClass('d-none');
                $('.modal-quantity').trigger('input');
            } else {
                $('#modalMessage').text('No Full reels are available for this product.');
            }
        }).fail(function () {
            $('#modalMessage').removeClass('alert-info').addClass('alert-danger').text('Unable to load availability. Please try again.');
        });
    }

    $('#openItemModal').on('click', function () { resetModal(); itemModal.show(); });
    $('#modalReelId').on('change', function () {
        const reelId = this.value;
        if (!reelId) { resetModal(); return; }
        if (cart.some(item => String(item.reelId) === String(reelId))) {
            $('#modalAvailability').addClass('d-none');
            $('#modalMessage').removeClass('d-none alert-info').addClass('alert-danger').text('This Reel product is already in the sale.');
            $('#addModalItem').prop('disabled', true); return;
        }
        loadAvailability(reelId);
    });
    $(document).on('input', '.modal-quantity', function () {
        const max = parseInt($(this).attr('max'), 10) || 0;
        $(this).val(Math.max(0, Math.min(parseInt($(this).val(), 10) || 0, max)));
        let total = 0; $('.modal-quantity').each(function () { total += parseInt($(this).val(), 10) || 0; });
        $('#addModalItem').prop('disabled', total < 1);
    });
    $('#addModalItem').on('click', function () {
        const reelId = $('#modalReelId').val();
        const warehouses = [];
        $('.modal-quantity').each(function () {
            const quantity = parseInt($(this).val(), 10) || 0;
            if (quantity > 0) warehouses.push({id:$(this).data('id'), name:$(this).data('name'), quantity});
        });
        if (!reelId || !warehouses.length) return;
        cart.push({
            reelId, code:$('#modalReelId option:selected').text().trim(), price:modalPrice,
            quantity:warehouses.reduce((sum, warehouse) => sum + warehouse.quantity, 0),
            warehouses, discount:0
        });
        renderCart(); itemModal.hide();
    });
    $(document).on('input', '.item-discount, #sgstPercentage, #cgstPercentage', calculate);
    $(document).on('click', '.remove-item', function () { cart.splice($(this).data('index'), 1); renderCart(); });
    $('#gstApplicable').on('change', function () {
        $('#gstFields').toggleClass('d-none', !this.checked);
        $('#sgstPercentage, #cgstPercentage').prop('required', this.checked);
        calculate();
    }).trigger('change');

    oldItems.forEach(function (oldItem) {
        const quantities = {};
        (oldItem.warehouse_quantities || []).forEach(line => quantities[line.warehouse_id] = line.quantity);
        $.get(`${availabilityBase}/${oldItem.reel_id}/availability`).done(function (response) {
            const warehouses = response.availability.filter(w => (parseInt(quantities[w.warehouse_id], 10) || 0) > 0).map(w => ({
                id:w.warehouse_id, name:w.warehouse_name,
                quantity:Math.min(parseInt(quantities[w.warehouse_id], 10), parseInt(w.available, 10))
            }));
            if (!warehouses.length) return;
            const option = $(`#modalReelId option[value="${oldItem.reel_id}"]`);
            cart.push({
                reelId:oldItem.reel_id, code:option.text().trim(), price:parseFloat(response.selling_price) || 0,
                quantity:warehouses.reduce((sum, warehouse) => sum + warehouse.quantity, 0),
                warehouses, discount:parseFloat(oldItem.discount) || 0
            });
            renderCart();
        });
    });

    if (!oldItems.length && prefillReelId) {
        resetModal();
        itemModal.show();
        $('#modalReelId').val(String(prefillReelId)).trigger('change.select2');
        loadAvailability(prefillReelId).done(function () {
            if (prefillWarehouseId) {
                $(`.modal-quantity[data-id="${prefillWarehouseId}"]`).focus();
            }
        });
    }
});
</script>
@endsection
