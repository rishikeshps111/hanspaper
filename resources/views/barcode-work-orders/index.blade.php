@extends('layouts.app')
@section('title', 'Barcode Work Orders')

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Barcode WorkOrder', 'Work Order']" />
            @include('layouts.session')
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">BARCODE WORK ORDERS</h5><a href="{{ route('barcode-work-orders.create') }}"
                        class="btn btn-primary"><i class="bx bx-plus me-1"></i>Create Work Order</a>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><label class="form-label">Customer</label><select id="customerFilter"
                                class="form-select listing-select2" data-placeholder="All Customers">
                                <option value=""></option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ trim($customer->first_name . ' ' . $customer->last_name) }}{{ $customer->mobile ? ' - ' . $customer->mobile : '' }}
                                    </option>
                                @endforeach
                            </select></div>
                        <div class="col-md-3"><label class="form-label">Due Date</label><input type="date"
                                id="dueDateFilter" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Status</label><select id="statusFilter"
                                class="form-select listing-select2" data-placeholder="All Statuses">
                                <option value=""></option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select></div>
                        <div class="col-md-2 d-flex align-items-end"><button type="button" id="resetBarcodeFilters"
                                class="btn btn-outline-secondary w-100"><i class="bx bx-reset me-1"></i>Reset</button></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle w-100" id="barcodeWorkOrdersTable">
                            <thead>
                                <tr>
                                    <th>Sl No.</th>
                                    <th>Work Order</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeWorkOrderStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Status - <span id="statusWorkOrderCode"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="statusChangeError" class="alert alert-danger d-none"></div>
                    <label for="newWorkOrderStatus" class="form-label">New Status <span class="text-danger">*</span></label>
                    <select id="newWorkOrderStatus" class="form-select"></select>
                    <div id="statusDateWrap" class="mt-3 d-none">
                        <label for="workOrderStatusDate" class="form-label"><span id="statusDateLabel">Status Date</span> <span class="text-danger">*</span></label>
                        <input type="date" id="workOrderStatusDate" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <small class="text-muted d-block mt-2">Only valid forward statuses are available.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmWorkOrderStatus" class="btn btn-success">Update Status</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(function() {
            @if (session('success'))
                iziToast.success({
                    title: 'Success',
                    message: @json(session('success'))
                });
            @endif

            const statusModal = new bootstrap.Modal(document.getElementById('changeWorkOrderStatusModal'));
            let statusUpdateUrl = null;
            const datedStatuses = ['completed', 'dispatched', 'delivered'];
            const updateStatusDateField = () => {
                const status = $('#newWorkOrderStatus').val();
                const needsDate = datedStatuses.includes(status);
                $('#statusDateWrap').toggleClass('d-none', !needsDate);
                $('#statusDateLabel').text(status ? `${status.replace(/_/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase())} Date` : 'Status Date');
                if (needsDate && !$('#workOrderStatusDate').val()) $('#workOrderStatusDate').val(@json(now()->format('Y-m-d')));
            };
            $('.listing-select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true
            });
            const table = $('#barcodeWorkOrdersTable').DataTable({
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: @json(route('barcode-work-orders.data', [], false)),
                    data: d => {
                        d.customer_id = $('#customerFilter').val();
                        d.due_date = $('#dueDateFilter').val();
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'code',
                    name: 'code'
                }, {
                    data: 'customer_name',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'work_order_date',
                    name: 'work_order_date'
                }, {
                    data: 'due_date',
                    name: 'due_date'
                }, {
                    data: 'status',
                    name: 'status'
                }, {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }]
            });
            $('.listing-select2,#dueDateFilter').on('change', () => table.ajax.reload());
            $('#resetBarcodeFilters').on('click', () => {
                $('.listing-select2').val(null).trigger('change.select2');
                $('#dueDateFilter').val('');
                table.ajax.reload();
            });
            $(document).on('click', '.cancel-work-order', function() {
                const button = $(this),
                    url = this.dataset.url,
                    code = this.dataset.code;
                swal({
                    title: 'Cancel Work Order?',
                    text: `Are you sure you want to cancel ${code}?`,
                    icon: 'warning',
                    buttons: ['No', 'Yes, cancel it'],
                    dangerMode: true
                }).then(confirmed => {
                    if (!confirmed) return;
                    button.prop('disabled', true);
                    $.ajax({
                        url,
                        type: 'POST',
                        data: {
                            _token: @json(csrf_token())
                        },
                        success: response => {
                            table.ajax.reload(null, false);
                            swal('Cancelled', response.message, 'success');
                        },
                        error: xhr => swal('Unable to cancel', xhr.responseJSON?.message ||
                            'Please try again.', 'error'),
                        complete: () => button.prop('disabled', false)
                    });
                });
            });

            $(document).on('click', '.change-work-order-status', function() {
                statusUpdateUrl = this.dataset.url;
                const statuses = JSON.parse(this.dataset.statuses || '[]');
                $('#statusWorkOrderCode').text(this.dataset.code);
                $('#newWorkOrderStatus').html(statuses.map(status =>
                    `<option value="${status}">${status.replace(/_/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase())}</option>`
                ).join(''));
                $('#workOrderStatusDate').val(@json(now()->format('Y-m-d')));
                updateStatusDateField();
                $('#statusChangeError').addClass('d-none').empty();
                statusModal.show();
            });

            $('#newWorkOrderStatus').on('change', updateStatusDateField);

            $('#confirmWorkOrderStatus').on('click', function() {
                const button = $(this).prop('disabled', true);
                $('#statusChangeError').addClass('d-none').empty();
                $.ajax({
                    url: statusUpdateUrl,
                    type: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        status: $('#newWorkOrderStatus').val(),
                        status_date: $('#statusDateWrap').hasClass('d-none') ? '' : $('#workOrderStatusDate').val()
                    },
                    success: response => {
                        statusModal.hide();
                        table.ajax.reload(null, false);
                        swal('Status Updated', response.message, 'success');
                    },
                    error: xhr => $('#statusChangeError').text(xhr.responseJSON?.errors?.status_date?.[0] ||
                        xhr.responseJSON?.errors?.status?.[0] || xhr.responseJSON?.message ||
                        'Unable to change status.').removeClass('d-none'),
                    complete: () => button.prop('disabled', false)
                });
            });
        });
    </script>
@endsection
