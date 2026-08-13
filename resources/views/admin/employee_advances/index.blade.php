@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">HR & Payroll /</span> Employee Advances
        </h4>
        <a href="{{ route('admin.employee-advances.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Advance Payment
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Summary Metrics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block mb-1">Total Advances Granted</span>
                            <h4 class="card-title text-primary mb-0">₹{{ number_format($totalAdvancesGiven, 2) }}</h4>
                        </div>
                        <div class="avatar bg-light-primary p-2 rounded">
                            <i class="bx bx-money fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block mb-1">Total Recovered (Deducted)</span>
                            <h4 class="card-title text-success mb-0">₹{{ number_format($totalAdvancesDeducted, 2) }}</h4>
                        </div>
                        <div class="avatar bg-light-success p-2 rounded">
                            <i class="bx bx-check-double fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block mb-1">Total Outstanding Balance</span>
                            <h4 class="card-title text-danger mb-0">₹{{ number_format($totalAdvancesOutstanding, 2) }}</h4>
                        </div>
                        <div class="avatar bg-light-danger p-2 rounded">
                            <i class="bx bx-time-five fs-3 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.employee-advances.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="partially_deducted" {{ $status === 'partially_deducted' ? 'selected' : '' }}>Partially Deducted</option>
                            <option value="fully_deducted" {{ $status === 'fully_deducted' ? 'selected' : '' }}>Fully Deducted</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                        <a href="{{ route('admin.employee-advances.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr class="table-light">
                        <th>#</th>
                        <th>Advance No.</th>
                        <th>Date</th>
                        <th>Employee</th>
                        <th class="text-end">Amount Granted</th>
                        <th class="text-end">Deducted</th>
                        <th class="text-end text-danger">Outstanding</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advances as $index => $adv)
                        <tr>
                            <td>{{ $advances->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('admin.employee-advances.show', $adv->id) }}" class="fw-bold text-primary">
                                    {{ $adv->advance_number }}
                                </a>
                            </td>
                            <td>{{ $adv->advance_date ? $adv->advance_date->format('d M Y') : '-' }}</td>
                            <td>
                                <div><strong>{{ $adv->employee ? $adv->employee->full_name : 'N/A' }}</strong></div>
                                <small class="text-muted">{{ $adv->employee ? $adv->employee->employee_code : '' }}</small>
                            </td>
                            <td class="text-end fw-bold">₹{{ number_format($adv->amount, 2) }}</td>
                            <td class="text-end text-success">₹{{ number_format($adv->deducted_amount, 2) }}</td>
                            <td class="text-end text-danger fw-bold">₹{{ number_format($adv->remaining_amount, 2) }}</td>
                            <td>
                                @if($adv->status === 'pending')
                                    <span class="badge bg-label-warning">Pending</span>
                                @elseif($adv->status === 'partially_deducted')
                                    <span class="badge bg-label-info">Partially Recovered</span>
                                @elseif($adv->status === 'fully_deducted')
                                    <span class="badge bg-label-success">Fully Recovered</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ ucfirst($adv->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.employee-advances.show', $adv->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @if($adv->deducted_amount == 0)
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-advance-btn" data-url="{{ route('admin.employee-advances.destroy', $adv->id) }}" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bx bx-info-circle fs-3 d-block mb-2"></i>
                                No employee advance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($advances->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $advances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
$(function(){
    $('.delete-advance-btn').click(function(){
        var url = $(this).data('url');
        Swal.fire({
            title: 'Are you sure?',
            text: "This advance record will be deleted permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Deleted!', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Action failed';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
