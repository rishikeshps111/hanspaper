@extends('layouts.app')
@section('title', __('Edit'))

		@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<x-breadcrumb :langArray="[
											'Machines',
											'Edit',
										]"/>
				<div class="row">
					<div class="col-12 col-lg-12">
                        <div class="card">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                              <h5 class="mb-0">{{ __('Edit') }}</h5>
                            </div>
                            <div class="card-body p-4">
                                <form action="{{ route('machine.update', $machine->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                   <div class="row">
                                        <div class="col-md-4">
                                            <x-label for="machine_name" name="{{ __('Machine Name') }}" />
                                            <x-input type="text" name="machine_name" :required="true" value="{{ $machine->machine_name }}"/>
                                        </div>
                                        <div class="col-md-4">
                                            <x-label for="status" name="{{ __('Status') }}" />
                                            <select name="status" class="form-control">
                                                <option value="Active" {{ $machine->status == 'Active' ? 'selected' : '' }}>Active</option>
                                                <option value="Inactive" {{ $machine->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                        
                                   </div>
                                   
                                    <div class="col-md-8 mb-3 mt-4 px-4 text-end">
                                        <div class="gap-3">
                                            <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                            <x-button type="submit" class="primary px-4" text="{{ __('Update') }}" />
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

@endsection
