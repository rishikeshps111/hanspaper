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
                            <h5 class="mb-0 text-uppercase">{{ __('Dispatch List') }}  {{$status}}</h5>
                        </div>

                      
                    </div>
                          <input type="hidden" name="cstatus" value=" {{$status}}">

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
                                           <!-- <th>Mode</th>-->
                                            <th>Date</th>
                                            <th>Ageing</th>
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

        @endsection
@section('js')
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
<script src="{{ versionedAsset('custom/js/sale/dispatchlist-filter.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/email/send.js') }}"></script>
<script src="{{ versionedAsset('custom/js/sms/sms.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/status-history/status-history.js') }}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endsection
