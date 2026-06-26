<div class="row g-3">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Total Stock</label>
            <input type="number" name="quantity" value="{{ $real->total_stock }}" class="form-control" disabled>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Available Full Stock</label>
            <input type="number" name="quantity" value="{{ $real->available_stock }}" class="form-control" disabled>
        </div>
    </div>
   <!-- <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Fully Used Stock</label>
            <input type="number" name="quantity" value="{{ $real->full_used_stock }}" class="form-control" disabled>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Available Bit Stock</label>
            <input type="number" name="quantity" value="{{ $real->bit_stock }}" class="form-control" disabled>
        </div>
    </div>-->
</div>
{{-- Add New Stock Form --}}
<div class="row mt-2">
    <div class="col-12">
        <form id="addStockForm" class="row">
            @csrf
            <input type="hidden" name="real_id" value="{{ $real->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">Add Stock</label>
                    <input type="number" name="quantity" min="1" class="form-control" placeholder="Quantity" required>
                </div>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-success">Add Stock</button>
            </div>
        </form>
    </div>
</div>