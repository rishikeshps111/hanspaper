@extends('layouts.app')
@section('title', __('sale.Purchaseorder'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['sale.Purchaseorder', 'sale.EditPurchaseOrder']" />
            <div class="row">
                <form class="g-3 needs-validation" id="invoiceForm"
                    action="{{ route('work-order.update', ['id' => $purchaseOrder->id]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="work_order_id" value="{{ $purchaseOrder->id }}">
                    <input type="hidden" name="row_count" value="0">
                    <input type="hidden" name="row_count_payments" value="0">
                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                    <input type="hidden" id="operation" name="operation" value="update">

                    <!-- Temprary usage -->
                    <input type="hidden" name="batch_details_json" value=''>

                    <div class="row">
                        <div class="col-12 col-lg-12">
                            <div class="card">
                                <div class="card-header px-4 py-3">
                                    <h5 class="mb-0">{{ __('sale.Purchaseorderdetails') }}</h5>
                                </div>
                                <div class="card-body p-4 row g-3">
                                    <div class="col-md-4">
                                        <x-label for="party_id" name="{{ __('customer.customer') }}" />

                                        <a tabindex="0" class="text-primary" data-bs-toggle="popover"
                                            data-bs-trigger="hover focus"
                                            data-bs-content="Search by name, mobile, phone, whatsApp, email"><i
                                                class="fadeIn animated bx bx-info-circle"></i></a>

                                        <div class="input-group">
                                            <select class="form-select party-ajax" data-party-type='customer'
                                                data-placeholder="Select Customer" id="party_id" name="party_id">
                                                <option value="{{ $purchaseOrder->party->id }}">
                                                    {{ $purchaseOrder->party->first_name . ' ' . $purchaseOrder->party->last_name }}
                                                </option>
                                            </select>
                                            <button type="button" class="input-group-text open-party-model"
                                                data-party-type='customer'>
                                                <i class='text-primary bx bx-plus-circle'></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <x-label for="representative_id" name="{{ __('Representative') }}" />
                                        <a tabindex="0" class="text-primary" data-bs-toggle="popover"
                                            data-bs-trigger="hover focus" data-bs-content="Search by name, mobile"><i
                                                class="fadeIn animated bx bx-info-circle"></i></a>

                                        <div class="input-group">
                                            <x-dropdown-sales-representatives dropdownName="representative_id"
                                                selected="{{ $purchaseOrder->representative_id }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <x-label for="order_date" name="{{ __('app.date') }}" />
                                        <div class="input-group mb-3">
                                            <x-input type="text" additionalClasses="datepicker-edit" name="order_date"
                                                :required="true" value="{{ $purchaseOrder->po_date }}" />
                                            <span class="input-group-text" id="input-near-focus" role="button"><i
                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <x-label for="due_date" name="{{ __('app.due_date') }}" />
                                        <div class="input-group mb-3">
                                            <x-input type="text" additionalClasses="datepicker-edit" name="due_date"
                                                :required="true" value="{{ $purchaseOrder->due_date }}" />
                                            <span class="input-group-text" id="input-near-focus" role="button"><i
                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-none">
                                        <x-label for="order_code" name="{{ __('sale.order.code') }}" />
                                        <!--  -->
                                        <div class="input-group mb-3">
                                            <x-input type="text" name="prefix_code" :required="true"
                                                placeholder="Prefix Code" value="{{ $data['prefix_code'] }}" />
                                            <span class="input-group-text">#</span>
                                            <x-input type="text" name="count_id" :required="true"
                                                placeholder="Serial Number" value="{{ $data['count_id'] }}" />
                                        </div>
                                    </div>
                                    @if (app('company')['tax_type'] == 'gst')
                                        <div class="col-md-4 d-none">
                                            <x-label for="state_id" name="{{ __('app.state_of_supply') }}" />
                                            <x-dropdown-states selected="" dropdownName='state_id' />
                                        </div>
                                    @endif
                                    <div class="col-md-4 d-none">
                                        <x-label for="order_status" name="{{ __('sale.order_status') }}" />
                                        <x-dropdown-general optionNaming="saleOrderStatus" selected=""
                                            dropdownName='order_status' />
                                    </div>



                                </div>
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
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#itemModal"><i
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
                                                    <th scope="col">{{ __('CS Stock') }}</th>
                                                    <th scope="col">{{ __('Ava Stock') }}</th>
                                                    <th scope="col">{{ __('app.qty') }}</th>
                                                    <th scope="col">{{ __('Brand') }}</th>
                                                    <th scope="col">{{ __('Category') }}</th>
                                                    <th scope="col">{{ __('Status') }}</th>
                                                    <th scope="col">{{ __('Production Remarks') }}</th>
                                                    <th scope="col">{{ __('Packing Remarks') }}</th>
                                                    <th scope="col">{{ __('Dispatch Remarks') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="11"
                                                        class="text-center fw-light fst-italic default-row">
                                                        No items are added yet!!
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-md-2 dispatch-class">
                                        <x-label for="dispatch-status" name="Mode Of Delivery" />
                                        <x-dropdown-dispatch-status dropdownName="mode_of_delivery" />
                                    </div>
                                    <div class="col-md-2 dispatch-class">
                                        <x-label for="dispatch-type" name="Dispatch Status" />
                                        <x-dropdown-dispatch-type dropdownName="dispatch_status" />
                                    </div>

                                    <div class="col-md-8 dispatch-class">
                                        <x-label for="dispatch_remarks" name="Dispatch Remarks" />
                                        <x-textarea name='dispatch_remarks' />
                                    </div>
                                </div>


                                <div class="card-header px-4 py-3"></div>
                                <div class="card-body p-4 row g-3">
                                    <div class="col-md-12 text-right">
                                        <div class="text-end gap-3">
                                            <x-button type="button" class="primary px-4" buttonId="submit_form"
                                                text="{{ __('app.update') }}" />
                                            <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                                class="btn btn-light px-4" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <!--end row-->
        </div>
    </div>
    <!-- Import Modals -->
    @include('modals.service.create')
    @include('modals.expense-category.create')
    @include('modals.payment-type.create')
    @include('modals.item.serial-tracking')
    @include('modals.item.batch-tracking-sale')
    @include('modals.party.create')
    @include('modals.item.create')
    @include('modals.sale.order.load-sold-items')

@endsection

@section('js')
    <script>
        const existingItems = @json($purchaseOrder->items);
    </script>
    <script src="{{ versionedAsset('custom/js/sale/purchaseordeEdit.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/currency-exchange.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/serial-tracking.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/serial-tracking-settings.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/batch-tracking-sale.js') }}"></script>
    <!--<script src="{{ versionedAsset('custom/js/modals/payment-type/payment-type.js') }}"></script>-->
    <!-- <script src="{{ versionedAsset('custom/js/payment-types/payment-type-select2-ajax.js') }}"></script> -->
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/party/party.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/item/item.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/sale/order/load-sold-items.js') }}"></script>
@endsection
