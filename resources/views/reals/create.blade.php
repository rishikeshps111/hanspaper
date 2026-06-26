@extends('layouts.app')
@section('title', __('Create Real'))
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
            <x-breadcrumb :langArray="['Reals', 'Create']" />

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('Create Real') }}</h5>
                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="{{ route('reals.store') }}" enctype="multipart/form-data"
                                class="row g-3 needs-validation">
                                @csrf
                                @method('POST')

                                {{-- Real No --}}
                                <div class="col-md-4">
                                    <x-label for="real_no" name="{{ __('Real No') }}" />
                                    <x-input type="text" name="real_no" value="{{ old('real_no') }}" />
                                    @error('real_no')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Brand --}}
                                <div class="col-md-4">
                                    <x-label for="brand" name="{{ __('Brand') }}" />
                                    <select name="brand" class="form-select select2">
                                        <option value="">{{ __('Select Brand') }}</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                {{ old('brand') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Category --}}
                                <div class="col-md-4">
                                    <x-label for="category" name="{{ __('Category') }}" />
                                    <select name="category" class="form-select select2">
                                        <option value="">{{ __('Select Category') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- GSM --}}
                                <div class="col-md-4">
                                    <x-label for="gsm" name="{{ __('GSM') }}" />
                                    <x-input type="text" step="0.01" name="gsm" value="{{ old('gsm') }}" />
                                    @error('gsm')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Subcode --}}
                                <div class="col-md-4">
                                    <x-label for="subcode" name="{{ __('Subcode') }}" />
                                    <x-input type="text" name="subcode" value="{{ old('subcode') }}" />
                                    @error('subcode')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Width --}}
                                <div class="col-md-4">
                                    <x-label for="width" name="{{ __('Width') }}" />
                                    <x-input type="decimal" step="0.01" name="width" value="{{ old('width') }}" />
                                    @error('width')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Length --}}
                                <div class="col-md-4">
                                    <x-label for="length" name="{{ __('Length') }}" />
                                    <x-input type="decimal" step="0.01" name="length" value="{{ old('length') }}" />
                                    @error('length')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror

                                </div>

                                {{-- Weight --}}
                                <div class="col-md-4">
                                    <x-label for="weight" name="{{ __('Weight') }}" />
                                    <x-input type="decimal" step="0.01" name="weight" value="{{ old('weight') }}" />
                                    @error('weight')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Active/Inactive --}}
                                <div class="col-md-4">
                                    <x-label for="is_active" name="{{ __('Status') }}" />
                                    <select name="is_active" class="form-select">
                                        <option value="1" >
                                            {{ __('Active') }}</option>
                                        <option value="0" >
                                            {{ __('Inactive') }}</option>
                                    </select>
                                    @error('is_active')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Submit / Cancel --}}
                                <div class="col-12 text-end">
                                    <x-anchor-tag href="{{ route('reals.index') }}" text="{{ __('app.close') }}"
                                        class="btn btn-light px-4" />
                                    <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                $('.select2').select2({
                    placeholder: "{{ __('Select an option') }}",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>
@endsection