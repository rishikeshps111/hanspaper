@extends('layouts.app')
@section('title', __('item.edit'))

		@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<x-breadcrumb :langArray="[
											'item.production',
											'item.edit',
										]"/>
				<div class="row">
					<div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                              <h5 class="mb-0">{{ __('item.edit') }}</h5>
                            </div>
                            <div class="card-body p-4">
                                    <form  class="row g-3 needs-validation" id="itemForm1" action="{{ route('item.production.store') }}" enctype="multipart/form-data">
    
                                    @csrf
                                    @method('POST')

                                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                                    <input type="hidden" name="approved_by" value='{{ $user->id }}'>
                                    <input type="hidden" id="operation" name="operation" value="update">
                                    <input type="hidden" name="row_count" value="0">
                                    <input type="hidden" name="production_id" value="{{ $productionItemMaster->id }}">
                                    <div class="col-md-8">
                                        <x-label for="description" name="{{ __('item.remarks') }}" />
                                        <x-textarea name="remarks" value="" :required="true" value="{{ $productionItemMaster->remarks }}"/>
                                    </div>

                                    <div class="col-md-12 table-responsive">
                                        <table class="table mb-0 table-striped table-bordered" id="productionItemsTable">
                                            <thead>
                                                <tr class="text-uppercase">
                                                    <!--<th scope="col">{{ __('item.SlNo') }}</th>-->
                                                    <th scope="col">{{ __('item.item_name') }}</th>
                                                    <th scope="col">{{ __('item.requested_quantity') }}</th>
                                                    <th scope="col">{{ __('item.remaining_quantity') }}</th>
                                                    <th scope="col">{{ __('item.approved_quantity') }}</th>
                                                    <th scope="col">{{ __('item.production_quantity') }}</th>
                                                    <th scope="col">{{ __('item.production_status') }}</th>
                                                    <!--<th scope="col">{{ __('item.production_action') }}</th>-->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="7" class="text-center fw-light fst-italic default-row">
                                                        No items found!!
                                                    </td>
                                                </tr>
                                            </tbody>
                                           
                                        </table>
                                    </div>
                                    <div class="col-md-12 mb-3 px-4">
                                        <div class="d-md-flex d-grid align-items-center gap-3">
                                            <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                            <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                        </div>
                                    </div>
                                </form>

                            </div>

                        </div>
					</div>
				</div>
				<!--end row-->
			</div>
		</div>
        <!-- Import Modals -->
     

		@endsection

@section('js')
<script type="text/javascript">
window.productionItemsData = @json($productionItemMaster);
// const productionItemsData = @json($productionItemsJson);
</script>

<script src="{{ versionedAsset('custom/js/items/production.js') }}"></script>
{{-- <script src="{{ versionedAsset('custom/js/items/serial-tracking.js') }}"></script> --}}
{{-- <script src="{{ versionedAsset('custom/js/items/batch-tracking.js') }}"></script> --}}
{{-- <script src="{{ versionedAsset('custom/js/modals/tax/tax.js') }}"></script> --}}
{{-- <script src="{{ versionedAsset('custom/js/modals/item/brand/brand.js') }}"></script> --}}
{{-- <script src="{{ versionedAsset('custom/js/modals/item/category/category.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/unit/unit.js') }}"></script> --}}

@endsection
