@extends('layouts.app')
@section('title', __('Machines'))

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
		@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
					<x-breadcrumb :langArray="[
											'Machines',
										]"/>



                    <div class="card">

					<div class="card-header px-4 py-3 d-flex justify-content-between">
					    <!-- Other content on the left side -->
					    <div>
							<h5 class="mb-0 text-uppercase">{{ __('Machines') }}</h5>
					    </div>
					    
                        <div><x-anchor-tag href="{{ route('machine.create') }}" text="{{ __('Add New Machine') }}" class="btn btn-primary px-5" /></div>
					</div>
					

					<div class="card-body">
						<div class="table-responsive">
								<table class="table table-striped table-bordered border w-100" id="datatable">
									<thead>
										<tr>
											
											<th>{{ __('Machine') }}</th>
											<th>{{ __('item.production_status') }}</th>
											<th>{{ __('item.production_action') }}</th>
										</tr>
									</thead>
									<tbody>
									  @foreach($machines as $machine)
                                            <tr>
                                                <td>{{ $machine->machine_name }}</td>
                                                <td>{{ $machine->status }}</td>
                                                <td>
                                                     <a href="{{ route('machine.edit', $machine->id) }}" class="dropdown-item">
                                                        <i class="bx bx-edit"></i> Edit
                                                     </a>
                                                </td>
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
<script>
$(document).ready(function() {
    $('#datatable').DataTable({
        responsive: true,ordering: false
     
    });
});
</script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<!--<script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>-->
<!-- <script src="{{ versionedAsset('custom/js/items/item-transaction-list.js') }}"></script> -->
@endsection
