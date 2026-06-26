@extends('layouts.app')
@section('title', __('Dispatch Edit'))

		@section('content')
         <style>
            .myinput[type="checkbox"] {
 
  width: 20px;
  height: 20px;
  border-width: 3px;
border-style: solid;
  border-color: rgb(0, 140, 255) rgb(0, 140, 255) rgb(0, 140, 255) rgb(0, 140, 255)
;
}
</style>
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<x-breadcrumb :langArray="[
											'Dispatch',
											'Edit',
										]"/>
				<div class="row">
					<div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                              <h5 class="mb-0">{{ __('Dispatch Edit') }}</h5>
                            </div>
                            <div class="card-body p-4">
                                    <form method="POST" class="row g-3 needs-validation" id="dispatcheditForm" action="{{ route('dispatch.store') }}" enctype="multipart/form-data">
    
                                    @csrf
                                    @method('POST')
                                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                                    <input type="hidden" name="approved_by" value='{{ $dispatch->id }}'>
                                    <input type="hidden" name="operation" id="operation" value='update'>
                                    <input type="hidden" name="dispatch_id" value="{{ $dispatch->id }}">
                                    <input type="hidden" name="purchase_order_id" value="{{ $dispatch->purchase_order_id }}">
                                    <div class="col-md-4">
                                        <x-label for="item_id" name="{{ __('Work Order') }}"/>
                                        <div class="input-group">
                                            <br><b>{{ $dispatch->purchase_order_identifier }}</b>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <x-label for="customer_id" name="{{ __('Customer.0') }}"/>
                                        <div class="input-group">
                                            <br><b>{{ $dispatch->customer->first_name . ' ' . $dispatch->customer->last_name }}</b>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-none">
                                        <x-label for="remarks" name="{{ __('Mode Of Delivery') }}"/>
                                        <div class="input-group">
                                            <br><b>{{ $dispatch->mode_of_delivery }}</b>
                                        </div>
                                    </div>
                                     <div class="col-md-4 dispatch-class">
                                        <x-label for="mode_of_delivery" name="Mode OF Delivery" />
                                        <!--<x-dropdown-dispatch-type dropdownName="dispatch_status" />-->
                                        <x-dropdown-dispatch-status selected="{{ $dispatch->mode_of_delivery }}" dropdownName='mode_of_delivery'/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-label for="remarks" name="{{ __('Remarks') }}"/>
                                        <div class="input-group">
                                            <!--<br><b>{{ $dispatch->remarks }}</b>-->
                                            <x-textarea name="remarks" value="{{ $dispatch->remarks }}"/>
                                        </div>
                                    </div>
                                  
                                    <div class="col-md-3 dispatch-class">
                                        <x-label for="dispatch-type" name="Dispatch Status" />
                                        <!--<x-dropdown-dispatch-type dropdownName="dispatch_status" />-->
                                        <x-dropdown-dispatch-type selected="{{ $dispatch->status }}" dropdownName='dispatch_status'/>
                                    </div>
                                   
                                    <div class="col-12">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                     <th> <input class="form-check-input row-select myinput" type="checkbox" value="all" onclick="toggle(this);"> </th>
                                                    <th>Sl</th>
                                                    <th>Products</th>
                                                    <th>Customer Req Qty</th>
                                                    <th>Work Comp Qty</th>
                                                     <th>Dispatched Qty</th>
                                                       <th>Rem to be Dispatched</th>
                                                     <th>Enter Qty to be Dispatched</th>
                                                    <th>Remarks</th>
                                                     <th>Status</th>

                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach($dispatch->purchaseOrder->items as $orderItem)
                                                          @php if($dispatch->id==$orderItem->dispatches_id)
                                                              {@endphp

                                                              @php
                                                              $reqqty=0;
                                                              $totalreqqty=0;
                                                              $balance_dispatched=0;
                                                              $orginal_qty=0;
                                                              foreach($mdispatch as $md)
                                                              {


                                                                $product_item_id=$orderItem->id;
                                                                if($md->purchase_order_items_id==$product_item_id)
                                                                {     $orginal_qty=$orderItem->quantity;
                                                                       $reqqty=$md->required_qty;
                                                                      $totalreqqty=$totalreqqty+$reqqty;
                                                                }
                                                              }
                                                                  $display_qty=$orderItem->quantity-$totalreqqty;
                                                                $balance_dispatched=$orderItem->quantity-$totalreqqty;

                                                              @endphp

                                                              @php
                                                              if($balance_dispatched!=0)
                                                              {
                                                                $status="Not Fully Dispatched";
                                                                @endphp
                                                                <input class="form-check-input row-select myinput" type="hidden" name="bal_qty[]" value="<?php echo $balance_dispatched;?> " onclick="">
                                                                @php
                                                              }
                                                              else{

                                                              $status="Dispatched";

                                                              }
                                                              @endphp
                                        <input class="form-check-input row-select myinput" type="hidden" name="pur_item_id[]" value="<?php echo $orderItem->id;?> " onclick="">
                                                    <tr>
                                                        <th> <input class="form-check-input row-select myinput" type="checkbox" name="sel[]" value="<?php echo $orderItem->id;?> " onclick=""></th>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $orderItem->product->name ?? 'Product Not Found' }}</td>
                                                        <td>{{ $orderItem->quantity }}</td>
                                                         <td>{{ $orderItem->quantity }}</td>
                                                         <td>{{ $totalreqqty }}</td>
                                                         <td>{{ $balance_dispatched }}</td>
                                                          <td><input type="text" name="new_qty[]" value="<?php echo $balance_dispatched;?>"> </td>
                                                        <td><textarea  type="text" name="dispatch_remarks[]" id="remarks" class="form-control" placeholder="Remarks">{{ $orderItem->dispatch_remarks ?? '-' }}</textarea></td>
                                                        <td>{{$status}}</td>
                                                    </tr>
 @php   
                                                                                            }@endphp


                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-12 mb-3 px-4 text-end">
                                        <div class="gap-3">
                                            <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                            <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
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
<script src="{{ versionedAsset('custom/js/sale/dispatch-edit.js') }}"></script>
<script>
    function toggle(source) {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i] != source)
            checkboxes[i].checked = source.checked;
    }
}
</script>
@endsection
