@extends('layouts.app')
@section('title', __('item.production_list'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
table.dataTable tbody th,
table.dataTable tbody td {
    white-space: nowrap;
}
</style>
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
                        <h5 class="mb-0 text-uppercase">{{ __('item.production_list') }}</h5>
                    </div>
                    <div class="d-flex gap-2"><button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#productionExportModal"><i class="bx bx-export me-1"></i>Export</button><x-anchor-tag href="{{ route('item.production.create') }}" text="{{ __('item.production_create') }}"
                            class="btn btn-primary px-5" /></div>

                </div>


                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered border w-100" id="datatable" >
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
    <div class="modal fade" id="productionExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Export Productions</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><div class="d-flex justify-content-between align-items-center mb-2"><label class="form-label mb-0">Statuses <span class="text-danger">*</span></label><button type="button" id="toggleAllExportStatuses" class="btn btn-sm btn-outline-primary">Select All</button></div>
                <div id="exportStatuses" class="row g-2 border rounded p-2 mx-0" role="group" aria-label="Production statuses">
                    @foreach (\App\Http\Controllers\Items\ProductionExportController::STATUSES as $status)
                        <div class="col-sm-6"><label class="form-check mb-0"><input type="checkbox" class="form-check-input export-status" value="{{ $status }}"><span class="form-check-label">{{ $status }}</span></label></div>
                    @endforeach
                </div><div id="exportStatusError" class="text-danger small mt-2"></div>
                <div id="productionExportLoading" class="d-none mt-3 text-primary"><span class="spinner-border spinner-border-sm me-2"></span>Preparing download...</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="button" id="downloadProductionExport" class="btn btn-success">Download Excel</button></div>
        </div></div>
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
    <script src="{{ versionedAsset('custom/js/sale/productlist.js') }}"></script>
    <script>
        $(function () {
            const modal = $('#productionExportModal');
            const allStatuses = $('.export-status');
            function updateSelectAllLabel() {
                $('#toggleAllExportStatuses').text(allStatuses.filter(':checked').length === allStatuses.length ? 'Clear All' : 'Select All');
            }
            $('#toggleAllExportStatuses').on('click', function () {
                const selectAll = allStatuses.filter(':checked').length !== allStatuses.length;
                allStatuses.prop('checked', selectAll);
                $('#exportStatusError').empty();
                updateSelectAllLabel();
            });
            allStatuses.on('change', updateSelectAllLabel);
            $('#downloadProductionExport').on('click', async function () {
                const statuses = $('.export-status:checked').map(function () { return this.value; }).get();
                if (!statuses.length) { $('#exportStatusError').text('Select at least one status.'); return; }
                $('#exportStatusError').empty();
                const button = $(this);
                button.prop('disabled', true);
                modal.find('[data-bs-dismiss], .export-status, #toggleAllExportStatuses').prop('disabled', true);
                $('#productionExportLoading').removeClass('d-none');
                try {
                    const response = await fetch(@json(route('production.export', [], false)), {
                        method: 'POST', headers: {'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'},
                        body: new URLSearchParams(statuses.map(status => ['statuses[]', status]))
                    });
                    if (!response.ok) throw new Error('Unable to export productions. Please try again.');
                    const blob = await response.blob();
                    const signature = new Uint8Array(await blob.slice(0, 4).arrayBuffer());
                    if (signature[0] !== 0x50 || signature[1] !== 0x4b || signature[2] !== 0x03 || signature[3] !== 0x04) {
                        throw new Error('The server did not return an Excel file. Please try again.');
                    }
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'production-export.xlsx';
                    document.body.appendChild(link);
                    link.click(); link.remove();
                    setTimeout(() => URL.revokeObjectURL(url), 60000);
                    bootstrap.Modal.getInstance(modal[0])?.hide();
                } catch (error) { $('#exportStatusError').text(error.message); }
                finally { button.prop('disabled', false); modal.find('[data-bs-dismiss], .export-status, #toggleAllExportStatuses').prop('disabled', false); $('#productionExportLoading').addClass('d-none'); }
            });
        });
    </script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endsection
