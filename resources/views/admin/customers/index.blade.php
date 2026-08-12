@extends('admin.layouts.app')
@section('style')
<style>
.cust-avatar-sm {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 15px;
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.25);
}
.table-custom-index thead th {
    background-color: #f8fafc;
    color: #475569;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
    padding: 14px 16px;
    border-bottom: 2px solid #e2e8f0;
}
.table-custom-index tbody tr {
    transition: background-color 0.2s ease;
}
.table-custom-index tbody tr:hover {
    background-color: #f1f5f9;
}
.action-btn-group .btn {
    padding: 5px 10px;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: 6px;
}
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Customers Directory</h4>
            <p class="text-muted mb-0 small">Manage customer profiles, statements, and transaction histories</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.customers.import-template') }}" class="btn btn-outline-secondary"><i class="bx bx-download me-1"></i> Template</a>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bx bx-upload me-1"></i> Import</button>
            <a href="{{ route('admin.customers.export', ['search' => request('search')]) }}" class="btn btn-outline-success"><i class="bx bx-file-export me-1"></i> Export</a>
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary shadow-sm"><i class="bx bx-plus me-1"></i> Add Customer</a>
        </div>
    </div>

    @if(session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <strong><i class="bx bx-error-circle me-1"></i> Import completed with some warnings:</strong>
        <ul class="mb-0 mt-2" style="max-height: 200px; overflow-y: auto;">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Search filter -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.customers.index') }}">
                <div class="row g-2 align-items-center">
                    <div class="col-md-9">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search fs-5 text-muted"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search customer by Name, Phone, Email or Company Name..." value="{{ $search ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search me-1"></i> Search</button>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between p-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="bx bx-user me-2 text-primary"></i> Customer List</h5>
            <span class="badge bg-label-primary px-3 py-2 rounded-pill fs-7">
                Total: {{ $customers->total() }} Customer(s)
            </span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-custom-index table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td class="fw-semibold text-muted">{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="cust-avatar-sm">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                        {{ $customer->name }}
                                    </a>
                                    @if($customer->company_name)
                                        <div class="small text-muted"><i class="bx bx-building-house me-1"></i>{{ $customer->company_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $customer->type === 'corporate' ? 'bg-label-info' : 'bg-label-secondary' }}">
                                {{ ucfirst($customer->type) }}
                            </span>
                        </td>
                        <td class="fw-semibold">{{ $customer->phone ?? '-' }}</td>
                        <td>{{ $customer->email ?? '-' }}</td>
                        <td>
                            <label class="switch switch-success">
                                <input type="checkbox" class="toggle-status" data-url="{{ route('admin.customers.toggle-status', $customer) }}" {{ $customer->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1 action-btn-group">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="bx bx-show me-1"></i> View
                                </a>
                                <a href="{{ route('admin.reports.party-statement', ['party_id' => 'customer_' . $customer->id]) }}" class="btn btn-sm btn-warning" title="View Ledger Statement">
                                    <i class="bx bx-receipt me-1"></i> Ledger
                                </a>
                                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-primary" title="Edit Customer">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('admin.customers.destroy', $customer) }}" title="Delete Customer">
                                    <i class="bx bx-trash me-1"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bx bx-user-x fs-1 d-block mb-2 text-secondary"></i>
                            No customers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top d-flex justify-content-between align-items-center p-3">
            <div class="small text-muted">
                Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries
            </div>
            <div>{{ $customers->links() }}</div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.customers.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="bx bx-upload me-2 text-primary"></i> Import Customers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose CSV / Excel File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt,.xls,.xlsx" required>
                        <div class="form-text text-muted mt-2">
                            Please upload a valid CSV/Excel file using the template headers:<br>
                            <code>type, name, company_name, phone, email, address, state, gstin, pan_no, aadhaar_no</code><br>
                            <span class="text-danger">*Note: Type must be either 'individual' or 'corporate'. Phone must be 10 digits.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-upload me-1"></i> Import Now</button>
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
        Swal.fire({ 
            title: 'Are you sure?', 
            text: 'This customer record will be soft deleted.', 
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonText: 'Yes, delete it!' 
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({ 
                    url: url, 
                    type: 'POST', 
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' }, 
                    success: function(resp) {
                        if (resp.success) { 
                            btn.closest('tr').fadeOut(300, function() { $(this).remove(); });
                            setFlash('success', resp.message); 
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection
