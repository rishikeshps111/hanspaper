<form id="modalRealForm">
    @csrf

    <div class="row g-3">

        <div class="col-md-4">
            <x-label for="real_no" name="Real No" />
            <input type="text" name="real_no" class="form-control">
            <div class="text-danger small error_real_no"></div>
        </div>

        <div class="col-md-4">
            <x-label for="brand" name="Brand" />
            <select name="brand" class="form-select select3">
                <option value="">Select Brand</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
            <div class="text-danger small error_brand"></div>
        </div>

        <div class="col-md-4">
            <x-label for="category" name="Category" />
            <select name="category" class="form-select select3">
                <option value="">Select Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <div class="text-danger small error_category"></div>
        </div>

        <div class="col-md-4">
            <x-label for="gsm" name="GSM" />
            <input type="text" name="gsm" class="form-control">
            <div class="text-danger small error_gsm"></div>
        </div>

        <div class="col-md-4">
            <x-label for="subcode" name="Subcode" />
            <input type="text" name="subcode" class="form-control">
            <div class="text-danger small error_subcode"></div>
        </div>

        <div class="col-md-4">
            <x-label for="width" name="Width" />
            <input type="number" step="0.01" name="width" class="form-control">
            <div class="text-danger small error_width"></div>
        </div>

        <div class="col-md-4">
            <x-label for="length" name="Length" />
            <input type="number" step="0.01" name="length" class="form-control">
            <div class="text-danger small error_length"></div>
        </div>

        <div class="col-md-4">
            <x-label for="weight" name="Weight" />
            <input type="number" step="0.01" name="weight" class="form-control">
            <div class="text-danger small error_weight"></div>
        </div>

        <div class="col-md-4">
            <x-label for="is_active" name="Status" />
            <select name="is_active" class="form-select">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary px-4">
                Submit
            </button>
        </div>

    </div>
</form>