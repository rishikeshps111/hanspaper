@extends('layouts.app')
@section('title', __('Employee'))

        @section('content')
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                <x-breadcrumb :langArray="[
                                            'Employee List',
                                            'Employee Edit',
                                        ]"/>
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header px-4 py-3">
                                <h5 class="mb-0">{{ __('Edit Employee') }}</h5>
                            </div>
                            <div class="card-body p-4">
                                <form class="row g-3 needs-validation" id="employeeForm" action="{{ route('employee.update') }}" enctype="multipart/form-data" method="POST">
                                    {{-- CSRF Protection --}}
                                    @csrf
                                    <input type="hidden" name='id' value="{{ $employee->id }}" />
                                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                                    
                                     <div class="col-md-6">
                                        <x-label for="full_name" name="{{ __('Full Name') }}*" />
                                        <x-input type="text" name="full_name" :required="true"  value="{{ $employee->full_name }}"/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="mobile" name="{{ __('contact Number') }}*" />
                                        <x-input type="number" name="mobile" :required="true"  value="{{ $employee->mobile }}"/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="email" name="{{ __('user.email') }}" />
                                        <x-input type="email" name="email"   value="{{ $employee->email }}"/>
                                    </div>                                   
                                    <div class="col-md-6">
                                        <x-label for="status" name="{{ __('app.status') }}" />
                                         <select name="status" class="form-control">
                                            <option value="Active"  {{ $employee->status == 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Inactive"  {{ $employee->status == 'Active' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                     <div class="col-md-12">
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
        @endsection

@section('js')
<script src="{{ versionedAsset('custom/js/user/user.js') }}"></script>
@endsection
