@extends('layouts.app')
@section('title', __('Employee Production Report'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 40px !important;
        }
    </style>
@endsection
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Employee Production Report']" />
            <div class="card">
                <div class="card-header px-4 py-3 d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-uppercase">{{ __('Employee Production Report') }}</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="employee_filter" class="form-label">Employee</label>
                            <select id="employee_filter" class="form-select">
                                <option value="">{{ __('All Employees') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="from_date" class="form-label">From</label>
                            <input type="date" id="from_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="to_date" class="form-label">To</label>
                            <input type="date" id="to_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button id="reset" class="btn btn-danger w-100">{{ __('Reset') }}</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered border w-100" id="datatable">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Employee Name</th>
                                    <th>Total Production</th>
                                    <th>Total Packing</th>
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

    <div class="modal fade" id="commonModal" tabindex="-1" aria-labelledby="commonModal" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Employee Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- AJAX content -->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>


@endsection
@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>

        $('#employee_filter').select2({
            placeholder: "Select Employee",
            allowClear: true,
            width: '100%'
        });

        $(document).ready(function () {
            var table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                search: false,
                ajax: {
                    url: "{{ route('report.produced_by') }}",
                    data: function (d) {
                        d.employee_id = $('#employee_filter').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'full_name', name: 'full_name' },
                    { data: 'total_production', name: 'total_production' },
                    { data: 'total_packings', name: 'total_packings' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
                dom: 'lrtip'
            });

            $('#employee_filter, #from_date, #to_date').on('change', function () {
                table.draw();
            });

            $('#reset').on('click', function () {
                $('#employee_filter').val('').trigger('change');
                $('#from_date').val('');
                $('#to_date').val('');
                table.draw();
            });

            $(document).on('click', '.view-production-btn', function () {
                let title = $(this).data('title');
                let employeeId = $(this).data('id');

                $('#modalTitle').text(title);
                $('#commonModal').modal('show');
                $.ajax({
                    url: '{{ route('report.produced_by.production') }}',
                    type: 'GET',
                    data: {
                        employee_id: employeeId
                    },
                    success: function (response) {
                        $('#commonModal .modal-body').html(response);
                    },
                    error: function () {
                        $('#commonModal .modal-body').html(
                            '<div class="alert alert-danger">Failed to load data</div>'
                        );
                    }
                });

            });

            $(document).on('click', '.view-packing-btn', function () {
                let title = $(this).data('title');
                let employeeId = $(this).data('id');

                $('#modalTitle').text(title);
                $('#commonModal').modal('show');
                $.ajax({
                    url: '{{ route('report.produced_by.packing') }}',
                    type: 'GET',
                    data: {
                        employee_id: employeeId
                    },
                    success: function (response) {
                        $('#commonModal .modal-body').html(response);
                    },
                    error: function () {
                        $('#commonModal .modal-body').html(
                            '<div class="alert alert-danger">Failed to load data</div>'
                        );
                    }
                });

            });


        });


    </script>
@endsection