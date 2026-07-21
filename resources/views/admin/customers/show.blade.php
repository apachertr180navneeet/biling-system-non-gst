@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Customers /</span> Details
    </h4>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">{{ $customer->first_name }} {{ $customer->last_name }}</h5>
            <div>
                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered">
                <tr><th style="width:35%">Type</th><td>{{ ucfirst($customer->type) }}</td></tr>
                <tr><th>First Name</th><td>{{ $customer->first_name }}</td></tr>
                <tr><th>Last Name</th><td>{{ $customer->last_name }}</td></tr>
                <tr><th>Company Name</th><td>{{ $customer->company_name ?? '-' }}</td></tr>
                <tr><th>Phone</th><td>{{ $customer->phone }}</td></tr>
                <tr><th>Email</th><td>{{ $customer->email ?? '-' }}</td></tr>
                <tr><th>Address</th><td>{{ $customer->address ?? '-' }}</td></tr>
                <tr><th>State</th><td>{{ $customer->state ?? '-' }}</td></tr>
                <tr><th>GSTIN</th><td>{{ $customer->gstin ?? '-' }}</td></tr>
                <tr><th>PAN No</th><td>{{ $customer->pan_no ?? '-' }}</td></tr>
                <tr><th>Aadhaar No</th><td>{{ $customer->aadhaar_no ?? '-' }}</td></tr>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
@endsection
