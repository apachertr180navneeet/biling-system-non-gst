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
                            <div class="d-flex align-items-center gap-2">
                                <label class="switch switch-success mb-0">
                                    <input type="checkbox" class="toggle-status" data-url="{{ route('admin.employees.toggle-status', $emp) }}" {{ $emp->is_active ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                                <span class="badge {{ $emp->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $emp->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            @if(!$emp->is_active)
                                <div class="text-danger small mt-1" style="font-size: 0.72rem;"><i class="bx bx-info-circle"></i> Payroll & Attendance disabled</div>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.employees.edit', $emp->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                    @if($emp->is_active)
                                        <a class="dropdown-item" href="{{ route('admin.salary-slips.create', ['employee_id' => $emp->id]) }}"><i class="bx bx-receipt me-1"></i> Generate Slip</a>
                                    @else
                                        <span class="dropdown-item text-muted disabled" title="Cannot generate salary slip for deactivated employee"><i class="bx bx-block me-1"></i> Payroll Disabled</span>
                                    @endif
                                    <button type="button" class="dropdown-item text-danger btn-delete" data-url="{{ route('admin.employees.destroy', $emp) }}"><i class="bx bx-trash me-1"></i> Delete</button>
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
    $(function() {
        $(document).ajaxSuccess(function(event, xhr, settings) {
            if (settings.url && settings.url.indexOf('toggle-status') !== -1) {
                setTimeout(function() { location.reload(); }, 700);
            }
        });

        $('.btn-delete').click(function() {
            var url = $(this).data('url');
            var btn = $(this);
            Swal.fire({
                title: 'Are you sure?',
                text: 'This employee record will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#8592a3'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(resp) {
                            if (resp.success) {
                                btn.closest('tr').remove();
                                setFlash('success', resp.message || 'Employee deleted successfully.');
                            } else {
                                setFlash('error', resp.message || 'Failed to delete employee.');
                            }
                        },
                        error: function() {
                            setFlash('error', 'Failed to delete employee.');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
