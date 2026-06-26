@extends('layouts.app')
@section('title', __('item.production_list'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

@endsection
@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['item.production_list']" />



            <div class="card">

                <div class="card-header px-4 py-3 d-flex justify-content-between">
                    <!-- Other content on the left side -->
                    <div>
                        <h5 class="mb-0 text-uppercase">{{ __('item.production_list') }} {{$status}}</h5>
                    </div>
                   

                </div>

                                 <input type="hidden" name="cstatus" value=" {{$status}}">

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered border w-100" id="datatable">
                            <thead>
                                <tr>

                                    <th>{{ __('item.production_id') }}</th>
                                    <th>{{ __('item.customer') }}</th>
                                    <th>{{ __('Work Order') }}</th>
                                    <th>{{ __('item.item_name') }}</th>
                                    <th>{{ __('Brand') }}</th>
                                    <th>{{ __('Category') }}</th>
                                     <th class="text-center">{{ __('Req Qty') }}</th>
                                    <th class="text-center">{{ __('Prod Rem Qty') }}</th>
                                    <th class="text-center">{{ __('Pck Rem Qty') }}</th>
                                    <th>{{ __('item.due_date') }}</th>
                                  <th>{{ __('Ageing') }}</th>

                                    <th>{{ __('item.production_status') }}</th>
                                    <th>{{ __('item.production_action') }}</th>
                                </tr>
                            </thead>
                       
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
    <!--end row-->
    </div>
    </div>
@endsection
@section('js')
    <script>

    </script>
   
    <!--<script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>-->
    <!-- <script src="{{ versionedAsset('custom/js/items/item-transaction-list.js') }}"></script> -->



    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
<script src="{{ versionedAsset('custom/js/sale/productlist-filter.js') }}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endsection
