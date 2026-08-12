@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Employee Master</h4>
        <div>
            <a href="{{ route('admin.employees.export', ['search' => request('search')]) }}" class="btn btn-outline-success me-2"><i class="bx bx-file-export"></i> Export</a>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> New Employee</a>
        </div>
    </div>

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.employees.index') }}">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search by Code, Name, Email, Phone, Designation..." value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">All Employees</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Emp Code</th>
                        <th>Full Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Phone / Email</th>
                        <th>Salary Type</th>
                        <th>Basic Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($employees as $index => $emp)
                    <tr>
                        <td>{{ $employees->firstItem() + $index }}</td>
                        <td><span class="badge bg-label-primary fw-bold">{{ $emp->employee_code }}</span></td>
                        <td>
                            <strong>{{ $emp->full_name }}</strong>
                            @if($emp->user_id)
                                <span class="badge bg-label-info ms-1"><i class="bx bx-user-check"></i> System User</span>
                            @endif
                        </td>
                        <td>{{ $emp->designation ?? '-' }}</td>
                        <td>{{ $emp->department ?? '-' }}</td>
                        <td>
                            <div>{{ $emp->phone ?? '-' }}</div>
                            <small class="text-muted">{{ $emp->email ?? '' }}</small>
                        </td>
                        <td><span class="badge bg-label-secondary">{{ ucfirst($emp->salary_type) }}</span></td>
                        <td class="fw-bold">₹{{ number_format($emp->basic_salary, 2) }}</td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input status-toggle" type="checkbox" data-id="{{ $emp->id }}" {{ $emp->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.employees.edit', $emp->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                    <a class="dropdown-item" href="{{ route('admin.salary-slips.create', ['employee_id' => $emp->id]) }}"><i class="bx bx-receipt me-1"></i> Generate Slip</a>
                                    <button type="button" class="dropdown-item text-danger delete-btn" data-id="{{ $emp->id }}"><i class="bx bx-trash me-1"></i> Delete</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">No employee records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end">
            {{ $employees->links() }}
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.status-toggle').on('change', function() {
            var empId = $(this).data('id');
            $.ajax({
                url: '/admin/employees/' + empId + '/toggle-status',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Status updated successfully.');
                    }
                },
                error: function() {
                    toastr.error('Failed to update status.');
                }
            });
        });

        $('.delete-btn').on('click', function() {
            var empId = $(this).data('id');
            if (confirm('Are you sure you want to delete this employee?')) {
                $.ajax({
                    url: '/admin/employees/' + empId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    },
                    error: function() {
                        toastr.error('Failed to delete employee.');
                    }
                });
            }
        });
    });
</script>
@endsection
