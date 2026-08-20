<div class="modal fade" id="addStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="addStockForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Reel Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="stockErrors"></div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Reel <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="openQuickReelModal">
                            <i class="bx bx-plus"></i> Add Reel
                        </button>
                    </div>
                    <select name="reel_id" id="stockReelId" class="form-select w-100" data-placeholder="Search Reel"
                        required>
                        <option value=""></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Provider <span class="text-danger">*</span></label>
                    <select name="reel_provider_id" id="stockProviderId" class="form-select w-100"
                        data-placeholder="Select Provider" required>
                        <option value=""></option>
                        @foreach($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                    <select name="reel_warehouse_id" id="stockWarehouseId" class="form-select w-100"
                        data-placeholder="Select Warehouse" required>
                        <option value=""></option>
                        @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control" min="1" max="1000" value="1" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" id="saveStockButton">Add Stock</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="quickReelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" id="quickReelForm">
            @csrf
            <input type="hidden" name="is_active" value="1">
            <div class="modal-header">
                <h5 class="modal-title">Add Reel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="quickReelErrors"></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Brand <span
                                class="text-danger">*</span></label><select name="reel_brand_id" id="quickReelBrand"
                            class="form-select w-100" required>
                            <option value=""></option>@foreach($brands as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Reel Type <span
                                class="text-danger">*</span></label><select name="reel_type_id" id="quickReelType"
                            class="form-select w-100" required>
                            <option value=""></option>@foreach($types as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                        </select></div>
                    <div class="col-md-4"><label class="form-label">GSM <span
                                class="text-danger">*</span></label><select name="reel_gsm_id" id="quickReelGsm"
                            class="form-select w-100" required>
                            <option value=""></option>@foreach($gsms as $item)
                            <option value="{{ $item->id }}">{{ $item->gsm }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Width (mm) <span
                                class="text-danger">*</span></label><input type="number" name="width"
                            class="form-control" min="0.01" step="0.01" required></div>
                    <div class="col-md-6"><label class="form-label">Length (m) <span
                                class="text-danger">*</span></label><input type="number" name="length"
                            class="form-control" min="0.01" step="0.01" required></div>
                    <div class="col-md-6"><label class="form-label">Unit Price <span
                                class="text-danger">*</span></label><input type="number" name="unit_price"
                            class="form-control" min="0" step="0.01" required></div>
                    <div class="col-md-6"><label class="form-label">Selling Price <span
                                class="text-danger">*</span></label><input type="number" name="selling_price"
                            class="form-control" min="0" step="0.01" required></div>
                    <div class="col-12"><label class="form-label">Remarks <span
                                class="text-danger">*</span></label><textarea name="remarks" class="form-control"
                            rows="3" maxlength="5000" required></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveQuickReelButton">Save Reel</button>
            </div>
        </form>
    </div>
</div>