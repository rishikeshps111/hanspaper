@extends('layouts.app')
@section('title', __('app.StockLists'))

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
		@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
					<x-breadcrumb :langArray="[
											'item.items',
											'app.StockLists',
										]"/>



                    <div class="card">

					<div class="card-header px-4 py-3 d-flex justify-content-between">
					    <!-- Other content on the left side -->
					    <div>
							<h5 class="mb-0 text-uppercase">{{ __('app.StockLists') }}</h5>
					    </div>
					    
					   
					</div>
					<div class="card-body">
                        @if (Session::has('error'))
    <div class="alert alert-success">
       <h5>{{ Session::get('error') }}</h5>
        
    </div>
@endif
						<div class="table-responsive">
								<table class="table table-striped table-bordered border w-100" id="datatable">
									<thead>
										<tr>
                                            <th>{{ __('S.No') }}</th>
											<th>{{ __('item.item_name') }}</th>
											<th>{{ __('Current Stock') }}</th>
                                            <th>{{ __('Committed Stock') }}</th>
                                             <th>{{ __(' Available Qty') }}</th>
											<th>{{ __('item.created_date') }}</th>
											<th>{{ __('item.updated_date') }}</th>
											<th>{{ __('Action') }}</th>
										</tr>
									</thead>
									<tbody>
                                     
										@foreach($itemTransactions as $transaction)
											<tr>
                                                    <td><a>{{ $transaction['id'] }}</a></td>
												<td><a href="{{ route('item.transaction.edit', $transaction['id']) }}">{{ $transaction['name']}}</a></td>
												<td><a href="{{ route('item.transaction.edit', $transaction['id']) }}" class="text-dark">{{ $transaction['quantity'] }}</a></td>
                                                <td>{{$transaction['comm_stock']}}</td>
                                                <td><a href="{{ route('item.transaction.edit', $transaction['id']) }}" class="text-dark">{{ $transaction['avaquantity'] }}</a></td>
											    <td>{{ Carbon\Carbon::parse($transaction['created_at'])->format('H:i:s d-m-Y') }}</td>
                                                <td>{{ Carbon\Carbon::parse($transaction['updated_at'])->format('H:i:s d-m-Y') }}</td>
                                                <td><a href="{{ route('item.transaction.edit', $transaction['id']) }}" class="btn btn-sm btn-secondary">Edit</a></td>
											</tr>
										@endforeach
									</tbody>
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
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
 <!--<script src="{{ versionedAsset('custom/js/items/item-transaction-list.js') }}"></script> -->
 <script>
$(document).ready(function() {
     var exportColumns = [0,1,2,3];
    $('#datatable').DataTable({
        responsive: false,
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        lengthMenu: [[10, 25, 50, 500], [10, 25, 50, 500]],
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries"
        },
        pagingType: "full_numbers",
        dom: "<'row' "+
                    "<'col-sm-12' "+
                        "<'float-start' l"+
                            /* card-body class - auto created here */
                        ">"+
                        "<'float-end' fr"+
                            /* card-body class - auto created here */
                        ">"+
                        "<'float-end ms-2'"+
                            "<'card-body ' B >"+
                        ">"+
                    ">"+
                  ">"+
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            buttons: [
              
                // Apply exportOptions only to Copy button
                {
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: exportColumns
                    }
                },
                // Apply exportOptions only to Excel button
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: exportColumns
                    }
                },
                // Apply exportOptions only to CSV button
                {
                    extend: 'csvHtml5',
                    exportOptions: {
                        columns: exportColumns
                    }
                },
                // Apply exportOptions only to PDF button
                {
                    extend: 'pdfHtml5',
                    orientation: 'portrait',//or "landscape"
                    exportOptions: {
                        columns: exportColumns,
                    },
                },

            ],
        drawCallback: function() {
            // Remove first/last buttons after each draw
            $('.dataTables_paginate .first, .dataTables_paginate .last').remove();
        }
    });
});

</script>
@endsection
