@extends('layouts.app')
@section('title', __('item.create'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['item.production', 'item.production_create']" />
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('item.production_create') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="itemForm1" method="POST"
                                action="{{ route('item.production.store') }}" enctype="multipart/form-data">

                                @csrf
                                @method('POST')

                                <input type="hidden" id="base_url" value="{{ url('/') }}">
                                <input type="hidden" name="requested_by" value='{{ $user->id }}'>
                                <input type="hidden" name="approved_by" value='{{ $user->id }}'>
                                <input type="hidden" name="operation" id="operation" value='save'>
                                <input type="hidden" name="row_count" value="0">

                                <div class="col-md-4">
                                    <x-label for="representative_id" name="{{ __('Representative') }}" />

                                    <a tabindex="0" class="text-primary" data-bs-toggle="popover"
                                        data-bs-trigger="hover focus" data-bs-content="Search by name, mobile"><i
                                            class="fadeIn animated bx bx-info-circle"></i></a>

                                    <div class="input-group">
                                        <x-dropdown-sales-representatives dropdownName="representative_id" selected=""
                                            :required="false" />
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <x-label for="order_date" name="{{ __('app.date') }}" />
                                    <div class="input-group mb-3">
                                        <x-input type="text" additionalClasses="datepicker" name="order_date"
                                            :required="true" value="" />
                                        <span class="input-group-text" id="input-near-focus" role="button"><i
                                                class="fadeIn animated bx bx-calendar-alt"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <x-label for="due_date" name="{{ __('app.due_date') }}" />
                                    <div class="input-group mb-3">
                                        <x-input type="text" additionalClasses="datepicker-edit" name="due_date"
                                            :required="true" value="" />
                                        <span class="input-group-text" id="input-near-focus" role="button"><i
                                                class="fadeIn animated bx bx-calendar-alt"></i></span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="card-header px-4 py-3">
                                        <h5 class="mb-0">{{ __('item.items') }}</h5>
                                    </div>
                                    <div class="card-body p-4 row g-3">
                                        <div class="col-md-3 col-sm-12 col-lg-3 d-none">
                                            <x-label for="warehouse_id" name="{{ __('warehouse.warehouse') }}" />
                                            <x-dropdown-warehouse selected="" dropdownName='warehouse_id' />
                                        </div>
                                        <div class="col-md-9 col-sm-12 col-lg-7">
                                            <x-label for="search_item" name="{{ __('item.enter_item_name') }}" />
                                            <div class="input-group">
                                                <span class="input-group-text" id="basic-addon1"><i
                                                        class="fadeIn animated bx bx-barcode-reader text-primary"></i></span>
                                                <input type="text" id="search_item" value="" class="form-control"
                                                    required placeholder="Scan Barcode/Search Items">
                                                <button type="button" class="btn btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#itemModal"><i
                                                        class="bx bx-plus-circle me-0"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-lg-2 d-none">
                                            <x-label for="show_load_items_modal" name="{{ __('sale.sold_items') }}" />
                                            <x-button type="button" class="btn btn-outline-secondary px-5 rounded-0 w-100"
                                                buttonId="show_load_items_modal" text="{{ __('app.load') }}" />
                                        </div>
                                        <div class="col-md-12 table-responsive">
                                            <table class="table mb-0 table-striped table-bordered" id="invoiceItemsTable">
                                                <thead>
                                                    <tr class="text-uppercase">
                                                        <th scope="col">{{ __('app.action') }}</th>
                                                        <th scope="col">{{ __('item.item') }}</th>
                                                        {{-- <th scope="col">{{ __('Stock') }}</th> --}}
                                                        <th scope="col">{{ __('app.qty') }}</th>
                                                        {{-- <th scope="col">{{ __('Brand') }}</th> --}}
                                                        {{-- <th scope="col">{{ __('Category') }}</th> --}}
                                                        {{-- <th scope="col">{{ __('Status') }}</th> --}}
                                                        <th scope="col">{{ __('Production Remarks') }}</th>
                                                        <th scope="col">{{ __('Packing Remarks') }}</th>
                                                        <th scope="col">{{ __('Dispatch Remarks') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="6"
                                                            class="text-center fw-light fst-italic default-row">
                                                            No items are added yet!!
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3 px-4 text-end">
                                    <div class="gap-3">
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                        <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
    </div>
    </div>
    <!-- Import Modals -->
    @include('modals.item.create')

@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/items/production.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/item/item.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/sale/purchaseorder1.js') }}"></script>

    {{-- <script src="{{ versionedAsset('custom/js/items/serial-tracking.js') }}"></script> --}}
    {{-- <script src="{{ versionedAsset('custom/js/items/batch-tracking.js') }}"></script> --}}
    {{-- <script src="{{ versionedAsset('custom/js/modals/tax/tax.js') }}"></script> --}}
    {{-- <script src="{{ versionedAsset('custom/js/modals/item/brand/brand.js') }}"></script> --}}
    {{-- <script src="{{ versionedAsset('custom/js/modals/item/category/category.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/unit/unit.js') }}"></script> --}}
@endsection
