@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Customers</h4>
        <div>
            <a href="{{ route('admin.customers.import-template') }}" class="btn btn-outline-secondary me-2"><i class="bx bx-download"></i> Template</a>
            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bx bx-upload"></i> Import</button>
            <a href="{{ route('admin.customers.export', ['search' => request('search')]) }}" class="btn btn-outline-success me-2"><i class="bx bx-file-export"></i> Export</a>
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> New</a>
        </div>
    </div>

    @if(session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <strong>Import completed with some skipped rows/warnings:</strong>
        <ul class="mb-0 mt-2" style="max-height: 200px; overflow-y: auto;">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.customers.index') }}">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search by Name, Phone, Email, GSTIN or Company" value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">All Customers</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>GSTIN</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ ucfirst($customer->type) }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->email ?? '-' }}</td>
                        <td>{{ $customer->gstin ?? '-' }}</td>
                        <td>
                            <label class="switch switch-success">
                                <input type="checkbox" class="toggle-status" data-url="{{ route('admin.customers.toggle-status', $customer) }}" {{ $customer->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-primary">Edit</a>
                            <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('admin.customers.destroy', $customer) }}">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $customers->links() }}</div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.customers.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Customers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt,.xls,.xlsx" required>
                        <div class="form-text text-muted mt-2">
                            Please upload a valid CSV/Excel file using the template headers:<br>
                            <code>type, name, company_name, phone, email, address, state, gstin, pan_no, aadhaar_no</code><br>
                            <span class="text-danger">*Note: Type must be either 'individual' or 'corporate'. Phone must be 10 digits.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
<script>
$(function(){
    $('.btn-delete').click(function() {
        var url = $(this).data('url');
        var btn = $(this);
        Swal.fire({ title: 'Are you sure?', text: 'This will be soft deleted.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete!' }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({ url: url, type: 'POST', data: { _token: '{{ csrf_token() }}', _method: 'DELETE' }, success: function(resp) {
                    if (resp.success) { btn.closest('tr').remove(); setFlash('success', resp.message); }
                }});
            }
        });
    });
});
</script>
@endsection
