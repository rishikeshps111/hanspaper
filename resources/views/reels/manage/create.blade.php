@extends('layouts.app')

@section('title', 'Create Reel')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['Reels', 'Manage Reels', 'Create Reel']" />
        <div class="card">
            <div class="card-header px-4 py-3">
                <h5 class="mb-0 text-uppercase">Create Reel</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('reels.manage.store') }}" method="POST">
                    @include('reels.manage._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
@include('reels.manage.script')
@endsection
