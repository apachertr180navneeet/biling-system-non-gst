@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Salary Slip Detail</h4>
        <div>
            <a href="{{ route('admin.salary-slips.pdf', $salarySlip->id) }}" target="_blank" class="btn btn-success me-2"><i class="bx bx-printer"></i> Print / PDF</a>
            <a href="{{ route('admin.salary-slips.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between border-bottom pb-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1">{{ config('app.name') }}</h3>
                    <p class="text-muted mb-0">PAYSLIP FOR {{ strtoupper(date('F Y', mktime(0, 0, 0, $salarySlip->month, 1, $salarySlip->year))) }}</p>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold text-primary mb-1">SLIP NO: {{ $salarySlip->slip_number }}</h5>
                    <span class="badge {{ $salarySlip->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }} fs-6">
                        Status: {{ ucfirst($salarySlip->payment_status) }}
                    </span>
                </div>
            </div>

            <!-- Employee Info Grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Employee Name:</td>
                            <td class="fw-bold">{{ $salarySlip->employee ? $salarySlip->employee->full_name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Employee Code:</td>
                            <td class="fw-bold">{{ $salarySlip->employee ? $salarySlip->employee->employee_code : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Designation:</td>
                            <td>{{ $salarySlip->employee ? $salarySlip->employee->designation : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Department:</td>
                            <td>{{ $salarySlip->employee ? $salarySlip->employee->department : '-' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Payment Date:</td>
                            <td>{{ $salarySlip->payment_date ? $salarySlip->payment_date->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Payment Mode:</td>
                            <td>{{ $salarySlip->payment_mode ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bank Name:</td>
                            <td>{{ $salarySlip->employee ? $salarySlip->employee->bank_name : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account No / IFSC:</td>
                            <td>
                                {{ $salarySlip->employee ? $salarySlip->employee->account_number : '-' }}
                                @if($salarySlip->employee && $salarySlip->employee->ifsc_code)
                                    ({{ $salarySlip->employee->ifsc_code }})
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="p-3 bg-light rounded mb-4">
                <h6 class="fw-bold mb-2 text-dark">Attendance Summary</h6>
                <div class="row text-center g-2">
                    <div class="col-3">
                        <div class="border rounded p-2 bg-white">
                            <small class="text-muted d-block">Total Days</small>
                            <span class="fw-bold fs-5">{{ $salarySlip->total_days }}</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border rounded p-2 bg-white">
                            <small class="text-success d-block">Present Days</small>
                            <span class="fw-bold fs-5 text-success">{{ $salarySlip->present_days }}</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border rounded p-2 bg-white">
                            <small class="text-danger d-block">Absent Days</small>
                            <span class="fw-bold fs-5 text-danger">{{ $salarySlip->absent_days }}</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border rounded p-2 bg-white">
                            <small class="text-info d-block">Half Days / Leaves</small>
                            <span class="fw-bold fs-5 text-info">{{ $salarySlip->half_days }} HD / {{ $salarySlip->paid_leaves }} L</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings and Deductions Table -->
            <div class="row g-0 border rounded mb-4">
                <div class="col-md-6 border-end">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Earnings Description</th>
                                <th class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Basic Salary / Rate</td>
                                <td class="text-end">₹{{ number_format($salarySlip->basic_salary, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Earned Salary (Attendance basis)</td>
                                <td class="text-end fw-bold">₹{{ number_format($salarySlip->earned_salary, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Allowances / Overtime / Bonus</td>
                                <td class="text-end text-success">+₹{{ number_format($salarySlip->allowances, 2) }}</td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td>Gross Earnings</td>
                                <td class="text-end">₹{{ number_format($salarySlip->earned_salary + $salarySlip->allowances, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Deductions Description</th>
                                <th class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Other Deductions / Penalties</td>
                                <td class="text-end text-danger">₹{{ number_format($salarySlip->deductions, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Employee Advance Recovery</td>
                                <td class="text-end text-danger">₹{{ number_format($salarySlip->advance_deduction ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td>Total Deductions</td>
                                <td class="text-end text-danger">₹{{ number_format($salarySlip->deductions + ($salarySlip->advance_deduction ?? 0), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Net Salary Summary Banner -->
            <div class="p-4 bg-label-primary rounded d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-1 fw-bold text-primary">NET SALARY PAYABLE:</h5>
                    <small class="text-dark fw-bold">Amount in Words: {{ \App\Http\Controllers\Admin\SalarySlipController::numberToWords($salarySlip->net_salary) }} Only</small>
                </div>
                <h2 class="mb-0 fw-bold text-primary">₹{{ number_format($salarySlip->net_salary, 2) }}</h2>
            </div>

            @if($salarySlip->remarks)
            <div class="alert alert-secondary mb-4">
                <strong>Remarks / Notes:</strong> {{ $salarySlip->remarks }}
            </div>
            @endif

            <div class="row mt-5 pt-4 text-center">
                <div class="col-6">
                    <p class="mb-0">___________________________</p>
                    <small class="text-muted">Employee Signature</small>
                </div>
                <div class="col-6">
                    <p class="mb-0">___________________________</p>
                    <small class="text-muted">Authorized Signatory</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
