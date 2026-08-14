@extends('admin.layouts.app')
@section('style')
<style>
    .att-cell {
        text-align: center;
        font-weight: bold;
        font-size: 0.8rem;
        padding: 4px 2px !important;
        min-width: 30px;
        vertical-align: middle;
    }
    .att-cell.badge-P {
        background-color: #28a745 !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }
    .att-cell.badge-A {
        background-color: #dc3545 !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }
    .att-cell.badge-HD {
        background-color: #ffc107 !important;
        color: #000000 !important;
        box-shadow: none !important;
    }
    .att-cell.badge-L {
        background-color: #17a2b8 !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }
    .att-cell.badge-H {
        background-color: #696cff !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }
    .table th.bg-success { background-color: #28a745 !important; color: #ffffff !important; box-shadow: none !important; }
    .table th.bg-danger { background-color: #dc3545 !important; color: #ffffff !important; box-shadow: none !important; }
    .table th.bg-warning { background-color: #ffc107 !important; color: #000000 !important; box-shadow: none !important; }
    .table th.bg-info { background-color: #17a2b8 !important; color: #ffffff !important; box-shadow: none !important; }
    .table th.bg-primary { background-color: #696cff !important; color: #ffffff !important; box-shadow: none !important; }
    .th-holiday {
        background-color: #e7e7ff !important;
        color: #696cff !important;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0">Monthly Attendance Report</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.holidays.index') }}" class="btn btn-outline-primary"><i class="bx bx-calendar-star me-1"></i> Holiday Master</a>
            <a href="{{ route('admin.attendances.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Daily Entry</a>
        </div>
    </div>

    <!-- Filter form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attendances.monthly-report') }}">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" onchange="this.form.submit()">
                            @for($y = date('Y') + 1; $y >= 2024; $y--)
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

    <!-- Monthly Attendance Matrix -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Attendance Summary: {{ $startDate->format('F Y') }}</h5>
            <div>
                <span class="badge bg-success me-1">P: Present</span>
                <span class="badge bg-danger me-1">A: Absent</span>
                <span class="badge bg-warning text-dark me-1">HD: Half Day</span>
                <span class="badge bg-info me-1">L: Leave</span>
                <span class="badge bg-primary">H: Holiday</span>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-bordered align-middle table-sm" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 150px;">Employee</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dayHoliday = $holidayDaysMap[$d] ?? null;
                            @endphp
                            <th class="text-center {{ $dayHoliday ? 'th-holiday' : '' }}" style="width: 25px;" title="{{ $dayHoliday ? $dayHoliday->name : '' }}">
                                {{ $d }}
                                @if($dayHoliday)
                                    <div style="font-size: 0.6rem; line-height: 1;">★</div>
                                @endif
                            </th>
                        @endfor
                        <th class="text-center bg-success text-white">P</th>
                        <th class="text-center bg-danger text-white">A</th>
                        <th class="text-center bg-warning text-dark">HD</th>
                        <th class="text-center bg-info text-white">L</th>
                        <th class="text-center bg-primary text-white">H</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    @php
                        $pCount = 0;
                        $aCount = 0;
                        $hdCount = 0;
                        $lCount = 0;
                        $hCount = 0;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $emp->full_name }}</strong>
                            <div class="text-secondary small fw-semibold">{{ $emp->employee_code }}</div>
                        </td>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $status = $attendanceMap[$emp->id][$d] ?? null;
                                $dayHoliday = $holidayDaysMap[$d] ?? null;
                                $code = '-';
                                $bgClass = '';

                                if ($status == 'present') {
                                    $code = 'P';
                                    $bgClass = 'badge-P';
                                    $pCount++;
                                } elseif ($status == 'absent') {
                                    $code = 'A';
                                    $bgClass = 'badge-A';
                                    $aCount++;
                                } elseif ($status == 'half_day') {
                                    $code = 'HD';
                                    $bgClass = 'badge-HD';
                                    $hdCount++;
                                } elseif ($status == 'leave') {
                                    $code = 'L';
                                    $bgClass = 'badge-L';
                                    $lCount++;
                                } elseif ($status == 'holiday') {
                                    $code = 'H';
                                    $bgClass = 'badge-H';
                                    $hCount++;
                                } elseif ($dayHoliday && !$status) {
                                    // If day is a designated holiday and no overriding attendance was recorded
                                    $code = 'H';
                                    $bgClass = 'badge-H';
                                    $hCount++;
                                }
                            @endphp
                            <td class="att-cell {{ $bgClass }}" title="{{ $dayHoliday ? $dayHoliday->name : '' }}">{{ $code }}</td>
                        @endfor
                        <td class="text-center fw-bold text-success">{{ $pCount }}</td>
                        <td class="text-center fw-bold text-danger">{{ $aCount }}</td>
                        <td class="text-center fw-bold text-warning">{{ $hdCount }}</td>
                        <td class="text-center fw-bold text-info">{{ $lCount }}</td>
                        <td class="text-center fw-bold text-primary">{{ $hCount }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $daysInMonth + 6 }}" class="text-center py-4 text-muted">No employee data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Holidays this month breakdown card -->
    @if(isset($holidays) && $holidays->count() > 0)
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light py-3 d-flex align-items-center">
            <i class="bx bx-gift text-primary fs-4 me-2"></i>
            <h6 class="mb-0 fw-bold">Official Holidays in {{ $startDate->format('F Y') }}</h6>
        </div>
        <div class="card-body pt-3">
            <div class="row g-3">
                @foreach($holidays as $h)
                    @php
                        $hFrom = \Carbon\Carbon::parse($h->from_date);
                        $hTo = \Carbon\Carbon::parse($h->to_date);
                        $isMulti = $hFrom->ne($hTo);
                    @endphp
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-white h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 fw-bold text-dark">{{ $h->name }}</h6>
                                <span class="badge bg-label-primary">{{ ucfirst($h->type) }}</span>
                            </div>
                            <div class="text-muted small mb-1">
                                <i class="bx bx-calendar"></i>
                                {{ $hFrom->format('d M Y') }}{{ $isMulti ? ' - ' . $hTo->format('d M Y') : '' }}
                                ({{ $h->total_days }} {{ Str::plural('Day', $h->total_days) }})
                            </div>
                            @if($h->description)
                                <p class="small text-muted mb-0">{{ $h->description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
