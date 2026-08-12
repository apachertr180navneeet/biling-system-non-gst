@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Salary Slips</h4>
        <a href="{{ route('admin.salary-slips.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> Generate Salary Slip</a>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.salary-slips.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by Employee Code or Name..." value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="month" class="form-select">
                            <option value="">-- All Months --</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="year" class="form-select">
                            <option value="">-- All Years --</option>
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i></button>
                            <a href="{{ route('admin.salary-slips.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Salary Slips</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Slip No</th>
                        <th>Employee</th>
                        <th>Month / Year</th>
                        <th>Attendance</th>
                        <th>Basic Salary</th>
                        <th>Allowances</th>
                        <th>Deductions</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salarySlips as $index => $slip)
                    <tr>
                        <td>{{ $salarySlips->firstItem() + $index }}</td>
                        <td><a href="{{ route('admin.salary-slips.show', $slip->id) }}" class="fw-bold">{{ $slip->slip_number }}</a></td>
                        <td>
                            <strong>{{ $slip->employee ? $slip->employee->full_name : 'N/A' }}</strong>
                            <div class="text-muted small">{{ $slip->employee ? $slip->employee->employee_code : '' }}</div>
                        </td>
                        <td><span class="badge bg-label-info">{{ date('F', mktime(0, 0, 0, $slip->month, 1)) }} {{ $slip->year }}</span></td>
                        <td>
                            <small class="d-block text-success">P: {{ $slip->present_days }} days</small>
                            <small class="d-block text-danger">A: {{ $slip->absent_days }} days</small>
                        </td>
                        <td>₹{{ number_format($slip->basic_salary, 2) }}</td>
                        <td class="text-success">+₹{{ number_format($slip->allowances, 2) }}</td>
                        <td class="text-danger">-₹{{ number_format($slip->deductions, 2) }}</td>
                        <td class="fw-bold text-primary fs-6">₹{{ number_format($slip->net_salary, 2) }}</td>
                        <td>
                            <span class="badge {{ $slip->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($slip->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.salary-slips.show', $slip->id) }}"><i class="bx bx-show me-1"></i> View Slip</a>
                                    <a class="dropdown-item" href="{{ route('admin.salary-slips.pdf', $slip->id) }}" target="_blank"><i class="bx bx-printer me-1"></i> Print / PDF</a>
                                    <button type="button" class="dropdown-item text-danger delete-btn" data-id="{{ $slip->id }}"><i class="bx bx-trash me-1"></i> Delete</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">No salary slips found. Click "Generate Salary Slip" to create one.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end">
            {{ $salarySlips->links() }}
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.delete-btn').on('click', function() {
            var slipId = $(this).data('id');
            if (confirm('Are you sure you want to delete this salary slip?')) {
                $.ajax({
                    url: '/admin/salary-slips/' + slipId,
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
                        toastr.error('Failed to delete salary slip.');
                    }
                });
            }
        });
    });
</script>
@endsection
