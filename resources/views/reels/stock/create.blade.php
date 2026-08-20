@extends('layouts.app')
@section('title', 'Add Reel Stock')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reels', 'Reel Stock', 'Add Stock']" />
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">ADD REEL STOCK</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('reels.stock.store') }}">@include('reels.stock._form')</form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    @include('reels.stock.script')
@endsection