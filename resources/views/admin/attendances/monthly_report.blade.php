@extends('admin.layouts.app')
@section('style')
<style>
    .att-cell {
        text-align: center;
        font-weight: bold;
        font-size: 0.8rem;
        padding: 4px !important;
        min-width: 28px;
    }
    .badge-P { background-color: #28a745; color: white; }
    .badge-A { background-color: #dc3545; color: white; }
    .badge-HD { background-color: #ffc107; color: black; }
    .badge-L { background-color: #17a2b8; color: white; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Monthly Attendance Report</h4>
        <a href="{{ route('admin.attendances.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Daily Entry</a>
    </div>

    <!-- Filter form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attendances.monthly-report') }}">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select">
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3 mt-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Attendance Summary: {{ $startDate->format('F Y') }}</h5>
            <div>
                <span class="badge bg-success me-1">P: Present</span>
                <span class="badge bg-danger me-1">A: Absent</span>
                <span class="badge bg-warning text-dark me-1">HD: Half Day</span>
                <span class="badge bg-info">L: Leave</span>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-bordered align-middle table-sm" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 150px;">Employee</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            <th class="text-center" style="width: 25px;">{{ $d }}</th>
                        @endfor
                        <th class="text-center bg-success text-white">P</th>
                        <th class="text-center bg-danger text-white">A</th>
                        <th class="text-center bg-warning text-dark">HD</th>
                        <th class="text-center bg-info text-white">L</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    @php
                        $pCount = 0;
                        $aCount = 0;
                        $hdCount = 0;
                        $lCount = 0;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $emp->full_name }}</strong>
                            <div class="text-muted small">{{ $emp->employee_code }}</div>
                        </td>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $status = $attendanceMap[$emp->id][$d] ?? null;
                                $code = '-';
                                $bgClass = '';
                                if ($status == 'present') { $code = 'P'; $bgClass = 'badge-P'; $pCount++; }
                                elseif ($status == 'absent') { $code = 'A'; $bgClass = 'badge-A'; $aCount++; }
                                elseif ($status == 'half_day') { $code = 'HD'; $bgClass = 'badge-HD'; $hdCount++; }
                                elseif ($status == 'leave') { $code = 'L'; $bgClass = 'badge-L'; $lCount++; }
                            @endphp
                            <td class="att-cell {{ $bgClass }}">{{ $code }}</td>
                        @endfor
                        <td class="text-center fw-bold text-success">{{ $pCount }}</td>
                        <td class="text-center fw-bold text-danger">{{ $aCount }}</td>
                        <td class="text-center fw-bold text-warning">{{ $hdCount }}</td>
                        <td class="text-center fw-bold text-info">{{ $lCount }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $daysInMonth + 5 }}" class="text-center py-4 text-muted">No employee data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
