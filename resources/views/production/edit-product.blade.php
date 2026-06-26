@extends('layouts.app')
@section('title', __('item.edit'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['item.production', 'item.edit']" />
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('Edit Product') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="productEditForm"
                                action="{{ route('item.updateProduct', ['id' => $product->id]) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row mb-2">
                                    <!-- Basic Information Section -->
                                    <div class="col-md-12 mb-4">
                                        <h6 class="mb-3">{{ __('Basic Information') }}</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-label for="name" name="{{ __('Customer Name') }}" required />
                                                <input type="text" name="name" id="name"
                                                    value="{{ old('name', $product->purchaseOrder->party->first_name ?? '') }}"
                                                    class="form-control" disabled>
                                            </div>
                                            <div class="col-md-6">
                                                <x-label for="sku" name="{{ __('Purchase Order') }}" />
                                                <input type="text" name="sku" id="sku"
                                                    value="{{ old('sku', $product->purchaseOrder->purchase_order_id) }}"
                                                    class="form-control" disabled>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category & Brand Section -->
                                    <div class="col-md-12 mb-4">
                                        <h6 class="mb-3">{{ __('Product Details') }}</h6>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <x-label for="requested_quantity" name="{{ __('Product') }}" />
                                                <select class="form-select select2" name="product" id="product" required>
                                                    <option value="">-- Select Product --</option>
                                                    @foreach ($items as $item)
                                                        <option value="{{ $item->id }}"
                                                            data-category="{{ $item->category->name ?? 'N/A' }}"
                                                            data-brand="{{ $item->brand->name ?? 'N/A' }}"
                                                            {{ old('product', $product->item_id) == $item->id ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="requested_quantity" name="{{ __('Requested Quantity') }}" />
                                                <input type="number" step="0.01" name="requested_quantity"
                                                    id="requested_quantity"
                                                    value="{{ old('requested_quantity', $product->requested_qty) }}"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pricing Section -->
                                    <div class="col-md-12 mb-4">
                                        <h6 class="mb-3">{{ __('Assignees') }}</h6>
                                        <div class="row">
                                            <div class="col-md-4 mt-2">
                                                <x-label for="assigned_machine" name="{{ __('Machine') }}" />
                                                <div class="input-group">
                                                    <select class="form-select select" name="assigned_machine"
                                                        data-placeholder="Choose Employee">
                                                        <option value="">-- Select Packing Employee --
                                                        </option>
                                                        @foreach ($machines as $machine)
                                                            <option value="{{ $machine->id }}"
                                                                {{ old('assigned_machine', $product->assigned_machine_id) == $machine->id ? 'selected' : '' }}>
                                                                {{ $machine->machine_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <x-label for="assigned_production_user"
                                                    name="{{ __('Production Employee') }}" />
                                                <div class="input-group">
                                                    <select class="form-select select" name="assigned_production_user"
                                                        data-placeholder="Choose Employee">
                                                        <option value="">-- Select Packing Employee --
                                                        </option>
                                                        @foreach ($employees as $employee)
                                                            <option value="{{ $employee->id }}"
                                                                {{ old('assigned_production_user', $product->assigned_production_user_id ?? '') == $employee->id ? 'selected' : '' }}>
                                                                {{ $employee->full_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mt-2">
                                                <x-label for="assigned_packing_user" name="{{ __('Packing Employee') }}" />
                                                <div class="input-group">
                                                    <select class="form-select select" data-placeholder="Choose Employee"
                                                        name="assigned_packing_user">
                                                        <option value="">-- Select Packing Employee --
                                                        </option>
                                                        @foreach ($employees as $employee)
                                                            <option value="{{ $employee->id }}"
                                                                {{ old('assigned_packing_user', $product->assigned_packing_user_id ?? '') == $employee->id ? 'selected' : '' }}>
                                                                {{ $employee->full_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Inventory Section -->
                                    <div class="col-md-12 mb-4">
                                        <h6 class="mb-3">{{ __('Remarks') }}</h6>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <x-label for="production_remark" name="{{ __('Production Remarks') }}" />
                                                <div class="form-floating">
                                                    <textarea class="form-control" name="production_remark" id="production_remark" placeholder="General notes"
                                                        style="height: 100px">{{ old('production_remark', $product->production_remarks ?? '') }}</textarea>
                                                </div>
                                            </div>

                                            <!-- Storage Remarks -->
                                            <div class="col-md-12">
                                                <x-label for="packing_remark" name="{{ __('Packing Remarks') }}" />
                                                <div class="form-floating">
                                                    <textarea class="form-control" name="packing_remark" id="packing_remark" placeholder="General notes"
                                                        style="height: 100px">{{ old('packing_remark', $product->packing_remarks ?? '') }}</textarea>
                                                </div>
                                            </div>

                                            <!-- Supplier Remarks -->
                                            <div class="col-md-12">
                                                <x-label for="dispatch_remark" name="{{ __('Dispatch Remarks') }}" />
                                                <div class="form-floating">
                                                    <textarea class="form-control" name="dispatch_remark" id="dispatch_remark" placeholder="General notes"
                                                        style="height: 100px">{{ old('dispatch_remark', $product->dispatch_remarks ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Form Actions -->
                                    <div class="col-md-12 mb-3 px-4 text-end">
                                        <div class="gap-3">
                                            <x-anchor-tag href="{{ route('item.production.index') }}"
                                                text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                            <x-button type="submit" class="primary px-4"
                                                text="{{ __('Update Product') }}" />
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end row-->


@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/items/production.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                templateResult: formatOption,
                templateSelection: formatOption
            });
        });

        $(document).ready(function() {
            $('.select').select2();
        });

        function formatOption(option) {
            if (!option.id) return option.text;

            var $container = $(
                '<div class="d-flex justify-content-between">' +
                '<span class="fw-bold" style="width: 40%">' + option.text + '</span>' +
                '<span class="text-muted" style="width: 30%">' + $(option.element).data('category') + '</span>' +
                '<span class="text-muted" style="width: 30%">' + $(option.element).data('brand') + '</span>' +
                '</div>'
            );
            return $container;
        }
    </script>
@endsection
