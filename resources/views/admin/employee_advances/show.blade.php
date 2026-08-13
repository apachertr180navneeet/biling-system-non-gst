@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Advance Payment Details - {{ $employeeAdvance->advance_number }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.employee-advances.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Advance Summary Card -->
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-primary">Advance Information</h5>
                    @if($employeeAdvance->status === 'pending')
                        <span class="badge bg-label-warning fs-6">Pending</span>
                    @elseif($employeeAdvance->status === 'partially_deducted')
                        <span class="badge bg-label-info fs-6">Partially Recovered</span>
                    @elseif($employeeAdvance->status === 'fully_deducted')
                        <span class="badge bg-label-success fs-6">Fully Recovered</span>
                    @else
                        <span class="badge bg-label-secondary fs-6">{{ ucfirst($employeeAdvance->status) }}</span>
                    @endif
                </div>
                <div class="card-body mt-3">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted ps-0">Advance Number:</td>
                            <td class="fw-bold text-end pe-0">{{ $employeeAdvance->advance_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Advance Date:</td>
                            <td class="fw-bold text-end pe-0">{{ $employeeAdvance->advance_date ? $employeeAdvance->advance_date->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Employee Name:</td>
                            <td class="fw-bold text-end pe-0">{{ $employeeAdvance->employee ? $employeeAdvance->employee->full_name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Employee Code:</td>
                            <td class="text-end pe-0">{{ $employeeAdvance->employee ? $employeeAdvance->employee->employee_code : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Payment Mode:</td>
                            <td class="text-end pe-0">{{ $employeeAdvance->payment_mode }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="text-dark fw-bold ps-0">Total Amount Given:</td>
                            <td class="fw-bold text-primary fs-5 text-end pe-0">₹{{ number_format($employeeAdvance->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Total Deducted (Recovered):</td>
                            <td class="fw-bold text-success text-end pe-0">₹{{ number_format($employeeAdvance->deducted_amount, 2) }}</td>
                        </tr>
                        <tr class="table-light">
                            <td class="text-danger fw-bold ps-2">Remaining Outstanding:</td>
                            <td class="fw-bold text-danger fs-5 text-end pe-2">₹{{ number_format($employeeAdvance->remaining_amount, 2) }}</td>
                        </tr>
                    </table>

                    @if($employeeAdvance->remarks)
                        <div class="mt-3 p-3 bg-light rounded">
                            <small class="text-muted d-block fw-bold mb-1">Remarks / Purpose:</small>
                            <p class="mb-0 small text-dark">{{ $employeeAdvance->remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Deduction History / Audit Log -->
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0 fw-bold text-dark"><i class="bx bx-history me-1"></i> Payroll Deduction Log</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr class="table-light">
                                <th>#</th>
                                <th>Salary Slip</th>
                                <th>Month / Year</th>
                                <th class="text-end">Amount Deducted</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employeeAdvance->salarySlipDeductions as $index => $deduction)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('admin.salary-slips.show', $deduction->salarySlip->id) }}" class="fw-bold text-primary">
                                            {{ $deduction->salarySlip->slip_number }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ date('F Y', mktime(0, 0, 0, $deduction->salarySlip->month, 1, $deduction->salarySlip->year)) }}
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        ₹{{ number_format($deduction->amount, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.salary-slips.show', $deduction->salarySlip->id) }}" class="btn btn-sm btn-outline-primary" title="View Salary Slip">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bx bx-info-circle fs-4 d-block mb-1"></i>
                                        No salary slip deductions processed against this advance yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
