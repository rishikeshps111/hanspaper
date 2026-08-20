@extends('layouts.app')
@section('title', 'Edit Reel Stock')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Reels', 'Reel Stock', 'Edit Stock']" />
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">EDIT {{ $stock->stock_code }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('reels.stock.update', $stock) }}">@include('reels.stock._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    @include('reels.stock.script')
@endsection