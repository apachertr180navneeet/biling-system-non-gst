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
        background-color: #696cff !important;
        color: #ffffff !important;
        font-weight: bold;
    }
    .th-day-clickable {
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }
    .th-day-clickable:hover {
        background-color: #f5f5f9;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .th-holiday.th-day-clickable:hover {
        background-color: #5f61e6 !important;
    }
    .quick-chip {
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .quick-chip:hover {
        background-color: #696cff !important;
        color: #fff !important;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0">Monthly Attendance Report</h4>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-warning text-dark fw-semibold" onclick="openHolidayModal('{{ $startDate->format('Y-m') }}-15', '', 'public', false)">
                <i class="bx bx-gift me-1"></i> Mark Holiday in this Month
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="loadDefaultHolidays()">
                <i class="bx bx-bolt-circle me-1"></i> Load National Holidays
            </button>
            <a href="{{ route('admin.holidays.index') }}" class="btn btn-outline-info"><i class="bx bx-calendar-star me-1"></i> Holiday Master</a>
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

                    <div class="col-md-3 mt-4">
                        <small class="text-muted d-block"><i class="bx bx-info-circle text-primary"></i> <em>Click on any day number header to mark or remove a holiday!</em></small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Attendance Matrix -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Attendance Summary: {{ $startDate->format('F Y') }}</h5>
            <div class="d-flex gap-1 flex-wrap">
                <span class="badge bg-success">P: Present</span>
                <span class="badge bg-danger">A: Absent</span>
                <span class="badge bg-warning text-dark">HD: Half Day</span>
                <span class="badge bg-info">L: Leave</span>
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
                                $dayDateFormatted = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                $dayHoliday = $holidayDaysMap[$d] ?? null;
                            @endphp
                            <th class="text-center th-day-clickable {{ $dayHoliday ? 'th-holiday' : '' }}" 
                                style="width: 25px;" 
                                onclick="openHolidayModal('{{ $dayDateFormatted }}', '{{ $dayHoliday ? addslashes($dayHoliday->name) : '' }}', '{{ $dayHoliday ? $dayHoliday->type : 'public' }}', {{ $dayHoliday ? 'true' : 'false' }})"
                                title="{{ $dayHoliday ? 'Holiday: ' . $dayHoliday->name . ' (Click to manage)' : 'Day ' . $d . ' - Click to mark holiday' }}">
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

                                if ($dayHoliday) {
                                    // By default on holiday dates, show 'H' (Holiday)
                                    if ($status === 'present') {
                                        $code = 'P';
                                        $bgClass = 'badge-P';
                                        $pCount++;
                                    } elseif ($status === 'absent') {
                                        $code = 'A';
                                        $bgClass = 'badge-A';
                                        $aCount++;
                                    } elseif ($status === 'half_day') {
                                        $code = 'HD';
                                        $bgClass = 'badge-HD';
                                        $hdCount++;
                                    } elseif ($status === 'leave') {
                                        $code = 'L';
                                        $bgClass = 'badge-L';
                                        $lCount++;
                                    } else {
                                        // Default is Holiday (H)
                                        $code = 'H';
                                        $bgClass = 'badge-H';
                                        $hCount++;
                                    }
                                } else {
                                    if ($status === 'present') {
                                        $code = 'P';
                                        $bgClass = 'badge-P';
                                        $pCount++;
                                    } elseif ($status === 'absent') {
                                        $code = 'A';
                                        $bgClass = 'badge-A';
                                        $aCount++;
                                    } elseif ($status === 'half_day') {
                                        $code = 'HD';
                                        $bgClass = 'badge-HD';
                                        $hdCount++;
                                    } elseif ($status === 'leave') {
                                        $code = 'L';
                                        $bgClass = 'badge-L';
                                        $lCount++;
                                    } elseif ($status === 'holiday') {
                                        $code = 'H';
                                        $bgClass = 'badge-H';
                                        $hCount++;
                                    }
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
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="bx bx-gift text-primary fs-4 me-2"></i>
                <h6 class="mb-0 fw-bold">Official Holidays in {{ $startDate->format('F Y') }}</h6>
            </div>
            <span class="badge bg-primary">{{ $holidays->count() }} Holiday(s) Declared</span>
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
                        <div class="p-3 border rounded bg-white h-100 shadow-xs">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 fw-bold text-dark">{{ $h->name }}</h6>
                                <span class="badge bg-label-primary">{{ ucfirst($h->type) }}</span>
                            </div>
                            <div class="text-muted small mb-2">
                                <i class="bx bx-calendar"></i>
                                {{ $hFrom->format('d M Y') }}{{ $isMulti ? ' - ' . $hTo->format('d M Y') : '' }}
                                ({{ $h->total_days }} {{ Str::plural('Day', $h->total_days) }})
                            </div>
                            @if($h->description)
                                <p class="small text-muted mb-2">{{ $h->description }}</p>
                            @endif
                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="unmarkHoliday('{{ $hFrom->format('Y-m-d') }}')">
                                <i class="bx bx-trash"></i> Unmark Holiday
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal: Mark Holiday in Monthly Attendance -->
<div class="modal fade" id="markHolidayModal" tabindex="-1" aria-labelledby="markHolidayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="markHolidayModalLabel">
                    <i class="bx bx-gift me-2"></i> Mark Holiday in Monthly Attendance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="markHolidayForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="modal_holiday_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Holiday / Occasion Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="modal_holiday_name" class="form-control" placeholder="e.g. Independence Day, Diwali, Eid, Christmas, Sunday Off" required>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <span class="badge bg-label-secondary quick-chip" onclick="setHolidayName('Independence Day', 'national')">Independence Day</span>
                            <span class="badge bg-label-secondary quick-chip" onclick="setHolidayName('Republic Day', 'national')">Republic Day</span>
                            <span class="badge bg-label-secondary quick-chip" onclick="setHolidayName('Diwali', 'public')">Diwali</span>
                            <span class="badge bg-label-secondary quick-chip" onclick="setHolidayName('Eid-ul-Fitr', 'public')">Eid</span>
                            <span class="badge bg-label-secondary quick-chip" onclick="setHolidayName('Christmas', 'public')">Christmas</span>
                            <span class="badge bg-label-secondary quick-chip" onclick="setHolidayName('Raksha Bandhan', 'public')">Raksha Bandhan</span>
                            <span class="badge bg-label-secondary quick-chip" onclick="setHolidayName('Company Holiday', 'company')">Company Holiday</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Holiday Type <span class="text-danger">*</span></label>
                        <select name="type" id="modal_holiday_type" class="form-select" required>
                            <option value="public">Public Holiday</option>
                            <option value="national">National Holiday</option>
                            <option value="company">Company / Restricted Holiday</option>
                            <option value="optional">Optional Holiday</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description / Notes <small class="text-muted">(Optional)</small></label>
                        <textarea name="description" id="modal_holiday_description" class="form-control" rows="2" placeholder="Optional notes for employees..."></textarea>
                    </div>

                    <div class="alert alert-info py-2 px-3 mb-0 small">
                        <i class="bx bx-check-circle me-1"></i> Marking a holiday will declare it in the Holiday Master and record attendance as <strong>Holiday (H)</strong> for all active employees for this day.
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" id="unmarkBtn" style="display: none;" onclick="unmarkHoliday($('#modal_holiday_date').val())">
                        <i class="bx bx-trash me-1"></i> Remove Holiday
                    </button>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveHolidayBtn">
                            <i class="bx bx-save me-1"></i> Save & Mark Holiday
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function openHolidayModal(dateStr, nameStr, typeStr, isAlreadyHoliday) {
    $('#modal_holiday_date').val(dateStr);
    $('#modal_holiday_name').val(nameStr || '');
    $('#modal_holiday_type').val(typeStr || 'public');
    $('#modal_holiday_description').val('');

    if (isAlreadyHoliday) {
        $('#unmarkBtn').show();
        $('#saveHolidayBtn').html('<i class="bx bx-save me-1"></i> Update Holiday');
    } else {
        $('#unmarkBtn').hide();
        $('#saveHolidayBtn').html('<i class="bx bx-save me-1"></i> Save & Mark Holiday');
    }

    var modal = new bootstrap.Modal(document.getElementById('markHolidayModal'));
    modal.show();
}

function setHolidayName(name, type) {
    $('#modal_holiday_name').val(name);
    if (type) {
        $('#modal_holiday_type').val(type);
    }
}

function loadDefaultHolidays() {
    Swal.fire({
        title: 'Load National Holidays?',
        text: 'This will automatically register standard National Holidays (e.g. Independence Day, Republic Day, Gandhi Jayanti, etc.) for {{ $year }}.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, load them!',
        confirmButtonColor: '#696cff'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.holidays.load-defaults") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    year: '{{ $year }}'
                },
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Loaded!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to load default holidays.', 'error');
                }
            });
        }
    });
}

function unmarkHoliday(dateStr) {
    if (!dateStr) return;

    Swal.fire({
        title: 'Remove Holiday?',
        text: 'Are you sure you want to unmark the holiday for ' + dateStr + ' and clear its holiday attendance?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove it!',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#8592a3'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.attendances.unmark-holiday") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    date: dateStr
                },
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Removed!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to unmark holiday.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        }
    });
}

$(document).ready(function() {
    $('#markHolidayForm').on('submit', function(e) {
        e.preventDefault();

        var btn = $('#saveHolidayBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: '{{ route("admin.attendances.mark-holiday") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Save & Mark Holiday');
                if (res.success) {
                    $('#markHolidayModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Holiday Marked!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message || 'Action failed', 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Save & Mark Holiday');
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error marking holiday.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>
@endsection
