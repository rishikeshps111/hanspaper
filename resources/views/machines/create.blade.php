@extends('layouts.app')
@section('title', __('create'))

		@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<x-breadcrumb :langArray="[
											'Machines',
											'Create',
										]"/>
				<div class="row">
					<div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                              <h5 class="mb-0">{{ __('Create') }}</h5>
                            </div>
                            <div class="card-body p-4">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <form method="POST" class="row g-3 needs-validation" id="itemForm1" action="{{ route('machine.store') }}" enctype="multipart/form-data">
    
                                    @csrf
                                    @method('POST')

                                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                                    <input type="hidden" name="operation" id="operation" value='save'>
                                  
                                    <div class="col-md-4">
                                        <x-label for="machine_name" name="{{ __('Machine Name') }}" />
                                        <x-input type="text" name="machine_name" :required="true" value=""/>
                                    </div>

                                    </div>

                                    <div class="col-md-4 mb-3 px-4 text-end">
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

@endsection
