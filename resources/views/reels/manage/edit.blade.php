@extends('layouts.app')

@section('title', 'Edit Reel')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['Reels', 'Manage Reels', 'Edit Reel']" />
        <div class="card">
            <div class="card-header px-4 py-3">
                <h5 class="mb-0 text-uppercase">Edit Reel #{{ $reel->id }}</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('reels.manage.update', $reel) }}" method="POST">
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
