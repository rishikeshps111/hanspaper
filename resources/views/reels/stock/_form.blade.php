@csrf
@isset($stock) @method('PUT') @endisset
<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Stock Code</label>
        <input class="form-control" value="{{ $stock->stock_code ?? 'Generated automatically when saved' }}" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label">Actual Code</label>
        <input type="text" name="actual_code" class="form-control" maxlength="100"
            value="{{ old('actual_code', $stock->actual_code ?? '') }}" placeholder="Optional physical reel code">
        @error('actual_code')
        <div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Reel Product <span class="text-danger">*</span></label>
        <select name="reel_id" id="reel_id" class="form-select single-select-clear-field w-100"
            data-placeholder="Select Reel" required>
            <option value=""></option>
            @foreach($reels as $reel)
                <option value="{{ $reel->id }}" data-length="{{ $reel->length }}" data-purchase="{{ $reel->unit_price }}"
                    data-selling="{{ $reel->selling_price }}" @selected(old('reel_id', $stock->reel_id ?? '') == $reel->id)>
                    {{ $reel->code }} | {{ $reel->brand->name }} / {{ $reel->type->name }} / {{ $reel->gsm->gsm }} GSM /
                    {{ $reel->width }} mm / {{ $reel->length }} m
                </option>
            @endforeach
        </select>
        @error('reel_id')
        <div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Provider <span class="text-danger">*</span></label>
        <select name="reel_provider_id" class="form-select single-select-clear-field w-100"
            data-placeholder="Select Provider" required>
            <option value=""></option>
            @foreach($providers as $provider)
                <option value="{{ $provider->id }}" @selected(old('reel_provider_id', $stock->reel_provider_id ?? '') == $provider->id)>{{ $provider->name }}</option>
            @endforeach
        </select>
        @error('reel_provider_id')
        <div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Warehouse <span class="text-danger">*</span></label>
        <select name="reel_warehouse_id" class="form-select single-select-clear-field w-100"
            data-placeholder="Select Warehouse" required>
            <option value=""></option>
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected(old('reel_warehouse_id', $stock->reel_warehouse_id ?? '') == $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
        @error('reel_warehouse_id')
        <div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Original Length (m) <span class="text-danger">*</span></label>
        <input type="number" step="0.001" min="0.001" name="original_length" id="original_length" class="form-control"
            value="{{ old('original_length', $stock->original_length ?? '') }}" required>
        @error('original_length')
        <div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Received At <span class="text-danger">*</span></label>
        <input type="date" name="received_at" class="form-control"
            value="{{ old('received_at', isset($stock) ? $stock->received_at->format('Y-m-d') : now()->format('Y-m-d')) }}"
            required>
        @error('received_at')
        <div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Purchase Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" class="form-control"
            value="{{ old('purchase_price', $stock->purchase_price ?? '') }}" required>
        @error('purchase_price')
        <div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Selling Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" class="form-control"
            value="{{ old('selling_price', $stock->selling_price ?? '') }}" required>
        @error('selling_price')
        <div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="is_active" class="form-select single-select-field w-100" required>
            <option value="1" @selected(old('is_active', isset($stock) ? (int) $stock->is_active : 1) == 1)>Active</option>
            <option value="0" @selected(old('is_active', isset($stock) ? (int) $stock->is_active : 1) == 0)>Inactive
            </option>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $stock->remarks ?? '') }}</textarea>
    </div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary px-5">{{ isset($stock) ? 'Update Stock' : 'Save Stock' }}</button>
    <a href="{{ route('reels.stock.index') }}" class="btn btn-secondary">Cancel</a>
</div>