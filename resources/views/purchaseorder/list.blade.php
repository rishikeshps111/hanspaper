@extends('layouts.app')
@section('title', __('Work Orders'))

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

@endsection
        @section('content')
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                    <x-breadcrumb :langArray="[
                                            'Work Orders',
                                            'List',
                                        ]"/>

                    <div class="card">

                    <div class="card-header px-4 py-3 d-flex justify-content-between">
                        <!-- Other content on the left side -->
                        <div>
                            <h5 class="mb-0 text-uppercase">{{ __('Work Order List') }}</h5>
                        </div>
                        <div><x-anchor-tag href="{{ route('purchaseorder.create') }}" text="{{ __('sale.CreatePurchaseOrder') }}" class="btn btn-primary px-5" /></div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 d-none">
                            <div class="col-md-3">
                                <x-label for="party_id" name="{{ __('customer.customer') }}" />

                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Search by name, mobile, phone, whatsApp, email"><i class="fadeIn animated bx bx-info-circle"></i></a>

                                <select class="party-ajax form-select" data-party-type='customer' data-placeholder="Select Customer" id="party_id" name="party_id"></select>
                            </div>
                            <div class="col-md-3">
                                <x-label for="user_id" name="{{ __('user.user') }}" />
                                <x-dropdown-user selected="" :showOnlyUsername='true' :canViewAllUsers="auth()->user()->can('sale.order.can.view.other.users.sale.orders')" />
                            </div>
                            <div class="col-md-3">
                                <x-label for="from_date" name="{{ __('app.from_date') }}" />
                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Filter by Order Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                                <div class="input-group mb-3">
                                    <x-input type="text" additionalClasses="datepicker-edit" name="from_date" :required="true" value=""/>
                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <x-label for="to_date" name="{{ __('app.to_date') }}" />
                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Filter by Order Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                                <div class="input-group mb-3">
                                    <x-input type="text" additionalClasses="datepicker-edit" name="to_date" :required="true" value=""/>
                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                </div>
                            </div>
                        </div>

                        <form class="row g-3 needs-validation" id="datatableForm" action="{{ route('sale.order.delete') }}" enctype="multipart/form-data">
                            {{-- CSRF Protection --}}
                            @csrf
                            @method('POST')
                            <input type="hidden" id="base_url" value="{{ url('/') }}">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered border w-100" id="datatable">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Work Order</th> 
                                            <th>Customer ID</th>
                                            <th>PO Date</th>
                                            <th>Due Date</th>
                                            <th>Created Gap</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                          
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
                    </div>
                </div>
                <!--end row-->
            </div>
        </div>

        @include("modals.email.send")
        @include("modals.sms.send")
        @include("modals.status-history.view", ['history_of' => 'sale-order' ])

        @endsection
@section('js')
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
<script src="{{ versionedAsset('custom/js/sale/purchaseorder-list.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/email/send.js') }}"></script>
<script src="{{ versionedAsset('custom/js/sms/sms.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/status-history/status-history.js') }}"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <script>
 

</script>
@endsection
