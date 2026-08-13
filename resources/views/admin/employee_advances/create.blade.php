@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Record Employee Advance Payment</h4>
        <a href="{{ route('admin.employee-advances.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>

    <div class="card col-md-8 mx-auto">
        <div class="card-body">
            <form action="{{ route('admin.employee-advances.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label required">Select Employee</label>
                        <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">-- Choose Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id', $selectedEmployeeId) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }} ({{ $emp->employee_code }}) - Current Outstanding: ₹{{ number_format($emp->outstandingAdvanceBalance(), 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Advance Date</label>
                        <input type="date" name="advance_date" class="form-control @error('advance_date') is-invalid @enderror" value="{{ old('advance_date', date('Y-m-d')) }}" required>
                        @error('advance_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Advance Amount (₹)</label>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control fw-bold text-primary @error('amount') is-invalid @enderror" placeholder="e.g. 5000" value="{{ old('amount') }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Payment Mode</label>
                        <select name="payment_mode" class="form-select @error('payment_mode') is-invalid @enderror" required>
                            <option value="Cash" {{ old('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Bank Transfer" {{ old('payment_mode', 'Bank Transfer') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer / NEFT</option>
                            <option value="UPI" {{ old('payment_mode') == 'UPI' ? 'selected' : '' }}>UPI / GPay / PhonePe</option>
                            <option value="Cheque" {{ old('payment_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                        @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Remarks / Purpose</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Specify reason or notes for advance payment">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bx bx-check-circle me-1"></i> Save Advance Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
