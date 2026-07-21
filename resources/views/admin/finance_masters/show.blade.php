@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Finance Masters /</span> Details
    </h4>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">{{ $financeMaster->name }}</h5>
            <div>
                <a href="{{ route('admin.finance-masters.edit', $financeMaster) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('admin.finance-masters.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr><th style="width:35%">Name</th><td>{{ $financeMaster->name }}</td></tr>
                    <tr><th>Description</th><td>{{ $financeMaster->description ?? '-' }}</td></tr>
                    <tr><th>Status</th><td>{{ $financeMaster->is_active ? 'Active' : 'Inactive' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
@endsection
