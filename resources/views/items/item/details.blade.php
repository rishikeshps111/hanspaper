@extends('layouts.app')
@section('title', __('Product Details'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('custom/css/style.css') }}" rel="stylesheet">
@endsection
@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['item.items', 'Product Details']" />

            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <h6 class="mb-0 text-uppercase">All Products</h6>
                            <div class="table-responsive fixed-height">
                                <table class="table table-striped table-bordered border w-100" id="itemListTable">
                                    <thead class="d-none">
                                        <tr>
                                            <th class="d-none"></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-7 details-panel">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end row-->
@endsection
@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/item-details.js') }}"></script>
@endsection
