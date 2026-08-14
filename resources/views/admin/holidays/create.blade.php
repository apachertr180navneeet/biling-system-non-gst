@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">HR & Payroll / <a href="{{ route('admin.holidays.index') }}" class="text-muted">Holiday Master</a> /</span> Add New Holiday
        </h4>
        <a href="{{ route('admin.holidays.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Holidays List
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0"><i class="bx bx-calendar-plus text-primary me-2"></i>New Holiday Details</h5>
                </div>
                <div class="card-body pt-4">
                    <form action="{{ route('admin.holidays.store') }}" method="POST" id="holidayForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Holiday / Occasion Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Independence Day, Diwali, Christmas, Annual Day" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">From Date <span class="text-danger">*</span></label>
                                <input type="date" name="from_date" id="from_date" class="form-control @error('from_date') is-invalid @enderror" value="{{ old('from_date', date('Y-m-d')) }}" required>
                                @error('from_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">To Date <small class="text-muted">(Leave empty or same for single-day)</small></label>
                                <input type="date" name="to_date" id="to_date" class="form-control @error('to_date') is-invalid @enderror" value="{{ old('to_date') }}">
                                @error('to_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" id="durationAlert">
                            <i class="bx bx-info-circle fs-4 me-2"></i>
                            <div>Duration: <strong id="durationText">1 Day</strong></div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Holiday Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="public" {{ old('type') == 'public' ? 'selected' : '' }}>Public Holiday</option>
                                    <option value="national" {{ old('type') == 'national' ? 'selected' : '' }}>National Holiday</option>
                                    <option value="company" {{ old('type') == 'company' ? 'selected' : '' }}>Company / Restricted Holiday</option>
                                    <option value="optional" {{ old('type') == 'optional' ? 'selected' : '' }}>Optional Holiday</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_recurring_yearly" value="1" id="is_recurring_yearly" {{ old('is_recurring_yearly') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_recurring_yearly">
                                        <strong>Recurs Annually</strong>
                                        <small class="text-muted d-block">Mark if this holiday repeats on the same date every year</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Description / Notes</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Optional notes, circular details, or instructions for staff...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.holidays.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="bx bx-save me-1"></i> Save Holiday</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    function calculateDuration() {
        var fromVal = $('#from_date').val();
        var toVal = $('#to_date').val();

        if (!fromVal) return;

        if (!toVal) {
            $('#durationText').text('1 Day');
            return;
        }

        var d1 = new Date(fromVal);
        var d2 = new Date(toVal);

        if (d2 < d1) {
            $('#durationText').text('Invalid date range (To Date is before From Date)');
            return;
        }

        var diffTime = Math.abs(d2 - d1);
        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        $('#durationText').text(diffDays + (diffDays > 1 ? ' Days' : ' Day'));
    }

    $('#from_date, #to_date').on('change', calculateDuration);
    calculateDuration();
});
</script>
@endsection
