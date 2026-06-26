@extends('layouts.app')
@section('title', __('Dispatches'))

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>

#pinfo td, #pinfo th {
  border: 1px solid #ddd;
  padding: 8px;
}

#pinfo tr:nth-child(even){background-color: #f2f2f2;}

#pinfo tr:hover {background-color: #ddd;}

#pinfo th {
  padding-top: 2px;
  padding-bottom: 2px;
  text-align: left;
  background-color: #008cff;
  color: white;
}
    </style>
@endsection
        @section('content')
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                    <x-breadcrumb :langArray="[
                                            'Dispatches',
                                            'List',
                                        ]"/>

                    <div class="card">

                    <div class="card-header px-4 py-3 d-flex justify-content-between">
                        <!-- Other content on the left side -->
                        <div>
                            <h5 class="mb-0 text-uppercase">{{ __('Dispatch List') }}</h5>
                        </div>

                      
                    </div>
                    <div class="card-body">
                        

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
                                            <th>Dispatch</th>
                                            <th>Work Order</th> 
                                            <th>Customer</th>
                                            <th>Products Info</th>
                                            <!--<th>Mode</th>-->
                                            <th>Date</th>
                                            <th>Ageing</th>
                                            <th>Status</th> 
                                            <th>Return Status</th>
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
        
        <!-- Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="returnForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Return Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="item_id" id="item_id">
                        <div class="mb-3">
                            <label class="form-label">Is Damaged?</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input is_damaged" type="radio" name="is_damaged" value="1"> Yes
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input is_damaged" type="radio" name="is_damaged" value="0" checked>
                                No
                            </div>
                        </div>
                        <div class="mb-3 reason-box d-none">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="returnDetailsModal" tabindex="-1" aria-labelledby="returnDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnDetailsModalLabel">Return Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Damaged:</strong> <span id="returnDamaged"></span></p>
                    <p id="returnReasonContainer" class="d-none"><strong>Reason:</strong> <span id="returnReason"></span>
                    </p>
                    <p><strong>Returned At:</strong> <span id="returnDate"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

        @endsection
@section('js')
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
<script src="{{ versionedAsset('custom/js/sale/dispatch-list.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/email/send.js') }}"></script>
<script src="{{ versionedAsset('custom/js/sms/sms.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/status-history/status-history.js') }}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endsection
