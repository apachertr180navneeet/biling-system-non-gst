@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Attendance Master - Daily Entry</h4>
        <div>
            <a href="{{ route('admin.attendances.monthly-report') }}" class="btn btn-outline-info me-2"><i class="bx bx-calendar-event"></i> Monthly Report</a>
            <a href="{{ route('admin.attendances.export', ['date' => $date]) }}" class="btn btn-outline-success"><i class="bx bx-file-export"></i> Export Excel</a>
        </div>
    </div>

    <!-- Date selector -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attendances.index') }}">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Select Date:</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label d-block">&nbsp;</label>
                        <span class="badge bg-primary p-2 fs-6"><i class="bx bx-calendar"></i> {{ \Carbon\Carbon::parse($date)->format('d F Y (l)') }}</span>
                    </div>
                    <div class="col-md-4 text-end">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-outline-success me-1" onclick="markAll('present')"><i class="bx bx-check"></i> Mark All Present</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')"><i class="bx bx-x"></i> Mark All Absent</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Employee Attendance List</h5>
            <small class="text-muted">Total Active Employees: {{ $employees->count() }}</small>
        </div>
        <form action="{{ route('admin.attendances.save-bulk') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Employee Name</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $index => $emp)
                        @php
                            $att = $existingAttendances->get($emp->id);
                            $currentStatus = $att ? $att->status : 'present';
                            $checkIn = $att ? $att->check_in_time : '09:30';
                            $checkOut = $att ? $att->check_out_time : '18:30';
                            $remarks = $att ? $att->remarks : '';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge bg-label-primary">{{ $emp->employee_code }}</span></td>
                            <td><strong>{{ $emp->full_name }}</strong></td>
                            <td>{{ $emp->designation ?? '-' }}</td>
                            <td style="width: 180px;">
                                <select name="attendances[{{ $emp->id }}][status]" class="form-select form-select-sm status-select" data-emp="{{ $emp->id }}">
                                    <option value="present" {{ $currentStatus == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ $currentStatus == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="half_day" {{ $currentStatus == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                    <option value="leave" {{ $currentStatus == 'leave' ? 'selected' : '' }}>Leave</option>
                                </select>
                            </td>
                            <td style="width: 140px;">
                                <input type="time" name="attendances[{{ $emp->id }}][check_in_time]" class="form-control form-control-sm" value="{{ $checkIn }}">
                            </td>
                            <td style="width: 140px;">
                                <input type="time" name="attendances[{{ $emp->id }}][check_out_time]" class="form-control form-control-sm" value="{{ $checkOut }}">
                            </td>
                            <td>
                                <input type="text" name="attendances[{{ $emp->id }}][remarks]" class="form-control form-control-sm" value="{{ $remarks }}" placeholder="Optional notes">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No active employees found. Please add employees first.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->count() > 0)
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Attendance Records</button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    function markAll(status) {
        $('.status-select').val(status);
    }
</script>
@endsection
