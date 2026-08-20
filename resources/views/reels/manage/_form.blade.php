@csrf
@isset($reel)
    @method('PUT')
@endisset

<div class="row g-3">
    <div class="col-md-6">
        <label for="code" class="form-label">Reel Code <span class="text-danger">*</span></label>
        <input type="text" name="code" id="code" class="form-control"
            value="{{ old('code', $reel->code ?? '') }}" readonly>
        <div class="form-text">Generated from brand, reel type, GSM, width, and length.</div>
        @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="reel_brand_id" class="form-label">Reel Brand <span class="text-danger">*</span></label>
        <select name="reel_brand_id" id="reel_brand_id" class="form-select single-select-clear-field w-100"
            data-placeholder="Select Brand" style="width: 100%" required>
            <option value="">Select Brand</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" data-short-name="{{ $brand->short_name ?: $brand->name }}" @selected(old('reel_brand_id', $reel->reel_brand_id ?? '') == $brand->id)>
                    {{ $brand->name }}{{ !$brand->is_active ? ' (Inactive)' : '' }}
                </option>
            @endforeach
        </select>
        @error('reel_brand_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="reel_type_id" class="form-label">Reel Type <span class="text-danger">*</span></label>
        <select name="reel_type_id" id="reel_type_id" class="form-select single-select-clear-field w-100"
            data-placeholder="Select Type" style="width: 100%" required>
            <option value="">Select Type</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" data-short-name="{{ $type->short_name ?: $type->name }}" @selected(old('reel_type_id', $reel->reel_type_id ?? '') == $type->id)>
                    {{ $type->name }}{{ !$type->is_active ? ' (Inactive)' : '' }}
                </option>
            @endforeach
        </select>
        @error('reel_type_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="reel_gsm_id" class="form-label">Reel GSM <span class="text-danger">*</span></label>
        <select name="reel_gsm_id" id="reel_gsm_id" class="form-select single-select-clear-field w-100"
            data-placeholder="Select GSM" style="width: 100%" required>
            <option value="">Select GSM</option>
            @foreach($gsms as $gsm)
                <option value="{{ $gsm->id }}" data-short-name="{{ $gsm->gsm }}" @selected(old('reel_gsm_id', $reel->reel_gsm_id ?? '') == $gsm->id)>
                    {{ $gsm->gsm }}{{ !$gsm->is_active ? ' (Inactive)' : '' }}
                </option>
            @endforeach
        </select>
        @error('reel_gsm_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="width" class="form-label">Width (mm) <span class="text-danger">*</span></label>
        <input type="number" name="width" id="width" class="form-control" step="0.01" min="0.01"
            value="{{ old('width', $reel->width ?? '') }}" required>
        @error('width')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="length" class="form-label">Length (m) <span class="text-danger">*</span></label>
        <input type="number" name="length" id="length" class="form-control" step="0.01" min="0.01"
            value="{{ old('length', $reel->length ?? '') }}" required>
        @error('length')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="unit_price" class="form-label">Unit Price <span class="text-danger">*</span></label>
        <input type="number" name="unit_price" id="unit_price" class="form-control" step="0.01" min="0"
            value="{{ old('unit_price', $reel->unit_price ?? '') }}" required>
        @error('unit_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="selling_price" class="form-label">Selling Price <span class="text-danger">*</span></label>
        <input type="number" name="selling_price" id="selling_price" class="form-control" step="0.01" min="0"
            value="{{ old('selling_price', $reel->selling_price ?? '') }}" required>
        @error('selling_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="is_active" id="is_active" class="form-select single-select-clear-field w-100"
            data-placeholder="Select Status" style="width: 100%" required>
            <option value="">Select Status</option>
            <option value="1" @selected((string) old('is_active', isset($reel) ? (int) $reel->is_active : '') === '1')>Active</option>
            <option value="0" @selected((string) old('is_active', isset($reel) ? (int) $reel->is_active : '') === '0')>Inactive</option>
        </select>
        @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="remarks" class="form-label">Remarks <span class="text-danger">*</span></label>
        <textarea name="remarks" id="remarks" class="form-control" rows="4" maxlength="5000" required>{{ old('remarks', $reel->remarks ?? '') }}</textarea>
        @error('remarks')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary px-5">{{ isset($reel) ? 'Update Reel' : 'Save Reel' }}</button>
    <a href="{{ route('reels.manage.index') }}" class="btn btn-secondary px-4">Cancel</a>
</div>
