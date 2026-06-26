@extends('layouts.app')
@section('title', __('Finished Reals'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reals']" />
            <div class="card">
                <div class="card-header px-4 py-3 d-flex justify-content-between">
                    <!-- Other content on the left side -->
                    <div>
                        <h5 class="mb-0 text-uppercase">{{ __('Finished Reals ') }}</h5>
                    </div>

                
                </div>


                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered border w-100" id="datatableone">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Real ID</th>
                                    <th>Real Number</th>
                                    <th>Brand</th>
                                    <th>Category</th>
                                    <th>GSM</th>
                                    <th>Subcode</th>
                                    <th>Width</th>
                                    <th>Length</th>
                                    <th>Weight</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="manageStockModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Manage Stock – <span id="modal_real_no"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('reals.js')
@endsection