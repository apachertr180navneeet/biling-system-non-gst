@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Generate Monthly Salary Slip</h4>
        <a href="{{ route('admin.salary-slips.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.salary-slips.store') }}" method="POST" id="salaryForm">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Select Employee</label>
                        <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">-- Choose Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id', $selectedEmployeeId) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }} ({{ $emp->employee_code }}) - ₹{{ number_format($emp->basic_salary, 2) }}/{{ $emp->salary_type }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Select Month</label>
                        <select name="month" id="month" class="form-select @error('month') is-invalid @enderror" required>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('month', $selectedMonth) == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Select Year</label>
                        <select name="year" id="year" class="form-select @error('year') is-invalid @enderror" required>
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ old('year', $selectedYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Attendance & Salary Breakdown Box -->
                    <div class="col-md-12 my-3">
                        <div class="card bg-light border">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3 text-primary"><i class="bx bx-calculator"></i> Attendance & Calculation Metrics</h6>
                                <div class="row g-3">
                                    <div class="col-md-2 col-6">
                                        <label class="form-label small text-muted">Total Days in Month</label>
                                        <input type="number" name="total_days" id="total_days" class="form-control form-control-sm bg-white" value="{{ old('total_days', '30') }}" readonly>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label small text-muted">Present Days</label>
                                        <input type="number" step="0.5" name="present_days" id="present_days" class="form-control form-control-sm bg-white" value="{{ old('present_days', '0') }}">
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label small text-muted">Absent Days</label>
                                        <input type="number" step="0.5" name="absent_days" id="absent_days" class="form-control form-control-sm bg-white" value="{{ old('absent_days', '0') }}">
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label small text-muted">Half Days</label>
                                        <input type="number" name="half_days" id="half_days" class="form-control form-control-sm bg-white" value="{{ old('half_days', '0') }}">
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label small text-muted">Paid Leaves</label>
                                        <input type="number" step="0.5" name="paid_leaves" id="paid_leaves" class="form-control form-control-sm bg-white" value="{{ old('paid_leaves', '0') }}">
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label small text-muted">Holidays</label>
                                        <input type="number" step="0.5" name="holiday_days" id="holiday_days" class="form-control form-control-sm bg-white" value="0" readonly>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label small text-muted">Basic Salary (₹)</label>
                                        <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control form-control-sm bg-white" value="{{ old('basic_salary', '0.00') }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings & Deductions -->
                    <div class="col-md-6">
                        <div class="card border h-100">
                            <div class="card-header bg-label-success py-2 fw-bold">Earnings</div>
                            <div class="card-body mt-3">
                                <div class="mb-3">
                                    <label class="form-label">Earned Salary (₹)</label>
                                    <input type="number" step="0.01" name="earned_salary" id="earned_salary" class="form-control fw-bold text-success" value="{{ old('earned_salary', '0.00') }}" required>
                                    <small class="text-muted">Calculated based on attendance metrics</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Allowances / Bonus / Overtime (₹)</label>
                                    <input type="number" step="0.01" name="allowances" id="allowances" class="form-control" value="{{ old('allowances', '0.00') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border h-100">
                            <div class="card-header bg-label-danger py-2 fw-bold">Deductions</div>
                            <div class="card-body mt-3">
                                <div class="mb-3">
                                    <label class="form-label">Other Deductions / Penalties (₹)</label>
                                    <input type="number" step="0.01" name="deductions" id="deductions" class="form-control text-danger" value="{{ old('deductions', '0.00') }}" required>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label mb-0">Advance Recovery Deduction (₹)</label>
                                        <small class="text-muted">Outstanding: <strong class="text-danger" id="outstanding_advance_display">₹0.00</strong></small>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="advance_deduction" id="advance_deduction" class="form-control text-danger" value="{{ old('advance_deduction', '0.00') }}">
                                    <small class="text-muted">Amount to recover from employee's advance balance</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-select">
                                        <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="unpaid" {{ old('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Salary Banner -->
                    <div class="col-md-12">
                        <div class="p-3 bg-label-primary rounded d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Net Payable Salary:</h5>
                            <h3 class="mb-0 fw-bold text-primary" id="net_salary_display">₹0.00</h3>
                            <input type="hidden" name="net_salary" id="net_salary" value="{{ old('net_salary', '0.00') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Payment Mode</label>
                        <input type="text" name="payment_mode" class="form-control" placeholder="e.g. Bank Transfer, Cash, Cheque" value="{{ old('payment_mode', 'Bank Transfer') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes for payroll record">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bx bx-check-circle me-1"></i> Save & Generate Salary Slip</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        function fetchCalculation() {
            var empId = $('#employee_id').val();
            var month = $('#month').val();
            var year = $('#year').val();

            if (!empId || !month || !year) return;

            $.ajax({
                url: '{{ route("admin.salary-slips.calculate-api") }}',
                type: 'GET',
                data: {
                    employee_id: empId,
                    month: month,
                    year: year
                },
                success: function(res) {
                    if (res.success) {
                        var d = res.data;
                        $('#total_days').val(d.total_days);
                        $('#present_days').val(d.present_days);
                        $('#absent_days').val(d.absent_days);
                        $('#half_days').val(d.half_days);
                        $('#paid_leaves').val(d.paid_leaves);
                        $('#holiday_days').val(d.holiday_days || 0);
                        $('#basic_salary').val(d.basic_salary);
                        $('#earned_salary').val(d.earned_salary);

                        var outAdv = parseFloat(d.outstanding_advance) || 0;
                        $('#outstanding_advance_display').text('₹' + outAdv.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                        $('#advance_deduction').attr('max', outAdv);

                        recalculateNet();
                    }
                }
            });
        }

        function recalculateNet() {
            var earned = parseFloat($('#earned_salary').val()) || 0;
            var allowances = parseFloat($('#allowances').val()) || 0;
            var deductions = parseFloat($('#deductions').val()) || 0;
            var advanceDeduction = parseFloat($('#advance_deduction').val()) || 0;

            var net = earned + allowances - deductions - advanceDeduction;
            if (net < 0) net = 0;

            $('#net_salary').val(net.toFixed(2));
            $('#net_salary_display').text('₹' + net.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        $('#employee_id, #month, #year').on('change', fetchCalculation);
        $('#earned_salary, #allowances, #deductions, #advance_deduction').on('input change', recalculateNet);

        if ($('#employee_id').val()) {
            fetchCalculation();
        }
    });
</script>
@endsection
