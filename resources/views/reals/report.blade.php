@extends('layouts.app')
@section('title', __('View Report'))
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
            <x-breadcrumb :langArray="['Reals', 'Edit']" />

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('View Report') }}</h5>
                        </div>

                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('reals.update', $real->id) }}"
                                enctype="multipart/form-data" class="row g-3 needs-validation">
                                @csrf
                                @method('PUT')

                              
                                            <div class="col-md-12 table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Sl</th>
                                                            <th>REAL ID</th>
                                                            <th>REAL NUMBER</th>
                                                            <th>BRAND</th>
                                                            <th>Category</th>
                                                            <th>Width</th>
                                                            <th>Length</th>
                                                             <th>Weight</th>
                                                                <th>Total length</th>
                                                            <th>Status: Full Used/Bit</th>
                                                             <th> Used Real </th>
                                                           <th>Available Real </th>
                                                            <th>Excess/Waste </th>
                                                        
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                            <tr>
                                                                 <td>1</td>
                                                                <td>{{ 'REAL' . str_pad($real->id, 3, '0', STR_PAD_LEFT) }}</td> 
                                                                <td>{{$real->real_no ?? 'Product Not Found' }}</td> 
                                                                <td>{{ $real->brandRelation->name ?? 'N/A' }}</td> 
                                                                <td>{{ $real->categoryRelation->name ?? 'N/A' }}</td> 
                                                                <td>{{ $real->width ?? 'N/A' }}</td>
                                                                <td>{{ $real->length ?? 'N/A' }}</td>
                                                                <td>{{ $real->weight ?? 'N/A' }}</td> 


                                                                  @php
                if(isset($real->stocksRelation))
                {
 

                       $total=$real->stocksRelation->total_length;
                           $bal=$real->stocksRelation->bal_length;
                             $used=$real->stocksRelation->total_length-$real->stocksRelation->bal_length;

                    @endphp
                   <td> {{ $real->stocksRelation->total_length ?? '0' }} m</td> 
                     <td> {{ $real->stocksRelation->status ?? 'N/A'  }} </td> 

                  @if($real->stocksRelation->status=="bit")
                     <td> {{ $used ?? '0' }} m</td>
                       <td> {{ $real->stocksRelation->bal_length ?? '0' }} m</td>  
              <td></td> 
                     @endif
                  @if($real->stocksRelation->status=="full")
                <td> {{  $used  ?? '0' }} m</td>
                       <td> {{ $real->stocksRelation->bal_length ?? '0' }} m</td> 
                        <td>{{ $real->stocksRelation->stock_status ?? ' ' }}</td>  
                     @endif
                     @php
                 }
                 else
                 {
                 @endphp

         <td></td> 
                  <td></td> 

     <td></td> 
                  <td></td> 



                 @php 
             }
             @endphp

                                                               
                                                            </tr>
                                                    </tbody>
                                                </table>
                                                  </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

    @section('js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
         
        </script>
    @endsection