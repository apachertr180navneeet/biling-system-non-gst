@extends('admin.layouts.app')

@section('style')
<style>
    .holiday-badge-public {
        background-color: #e8fadf;
        color: #71dd37;
        border: 1px solid #71dd37;
    }
    .holiday-badge-national {
        background-color: #e7e7ff;
        color: #696cff;
        border: 1px solid #696cff;
    }
    .holiday-badge-company {
        background-color: #fff2d6;
        color: #ffab00;
        border: 1px solid #ffab00;
    }
    .holiday-badge-optional {
        background-color: #d7f5fc;
        color: #03c3ec;
        border: 1px solid #03c3ec;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">HR & Payroll /</span> Holiday Master
        </h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attendances.index') }}" class="btn btn-outline-primary">
                <i class="bx bx-calendar-check me-1"></i> Attendance Entry
            </a>
            <a href="{{ route('admin.attendances.monthly-report') }}" class="btn btn-outline-info">
                <i class="bx bx-calendar-event me-1"></i> Monthly Attendance
            </a>
            <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Holiday
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="card h-100 border-start border-primary border-4 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block small mb-1">Total Holidays ({{ $year ?: date('Y') }})</span>
                            <h3 class="card-title text-primary mb-0 fw-bold">{{ $totalHolidaysThisYear }} <span class="fs-6 fw-normal text-muted">Days</span></h3>
                        </div>
                        <div class="avatar bg-light-primary p-2 rounded">
                            <i class="bx bx-calendar-star fs-2 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="card h-100 border-start border-success border-4 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block small mb-1">Public Holidays</span>
                            <h3 class="card-title text-success mb-0 fw-bold">{{ $publicCount }}</h3>
                        </div>
                        <div class="avatar bg-light-success p-2 rounded">
                            <i class="bx bx-world fs-2 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="card h-100 border-start border-info border-4 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block small mb-1">National Holidays</span>
                            <h3 class="card-title text-info mb-0 fw-bold">{{ $nationalCount }}</h3>
                        </div>
                        <div class="avatar bg-light-info p-2 rounded">
                            <i class="bx bx-flag fs-2 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="card h-100 border-start border-warning border-4 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block small mb-1">Company / Optional</span>
                            <h3 class="card-title text-warning mb-0 fw-bold">{{ $companyCount }}</h3>
                        </div>
                        <div class="avatar bg-light-warning p-2 rounded">
                            <i class="bx bx-briefcase-alt fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Holidays Banner if available -->
    @if($upcomingHolidays->count() > 0)
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #e7e7ff 0%, #f4f5fa 100%);">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-2"><i class="bx bx-bell"></i> Upcoming</span>
                    <h6 class="mb-0 fw-bold text-dark">Upcoming Holidays:</h6>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($upcomingHolidays as $up)
                        @php
                            $upFrom = \Carbon\Carbon::parse($up->from_date);
                            $upTo = \Carbon\Carbon::parse($up->to_date);
                            $isMulti = $upFrom->ne($upTo);
                        @endphp
                        <span class="badge bg-white text-dark border shadow-xs py-2 px-3">
                            <strong>{{ $up->name }}</strong>
                            <span class="text-primary ms-1">
                                ({{ $upFrom->format('d M') }}{{ $isMulti ? ' - ' . $upTo->format('d M') : '' }})
                            </span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.holidays.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            @for($y = date('Y') + 1; $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select" onchange="this.form.submit()">
                            <option value="">All Months</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="public" {{ $type === 'public' ? 'selected' : '' }}>Public</option>
                            <option value="national" {{ $type === 'national' ? 'selected' : '' }}>National</option>
                            <option value="company" {{ $type === 'company' ? 'selected' : '' }}>Company</option>
                            <option value="optional" {{ $type === 'optional' ? 'selected' : '' }}>Optional</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Search Holiday</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or notes..." value="{{ $search }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                        <a href="{{ route('admin.holidays.index') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="bx bx-refresh"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Holidays List Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Holidays List</h5>
            <small class="text-muted">Total: {{ $holidays->total() }} Record(s)</small>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Date & Day</th>
                        <th>Holiday Name</th>
                        <th>Type</th>
                        <th class="text-center">Duration</th>
                        <th>Description / Notes</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $index => $holiday)
                        @php
                            $from = \Carbon\Carbon::parse($holiday->from_date);
                            $to = \Carbon\Carbon::parse($holiday->to_date);
                            $isSingle = $from->eq($to);
                            $isPast = $to->isPast();
                        @endphp
                        <tr class="{{ $isPast ? 'text-muted' : '' }}">
                            <td>{{ $holidays->firstItem() + $index }}</td>
                            <td>
                                @if($isSingle)
                                    <div><strong>{{ $from->format('d M Y') }}</strong></div>
                                    <small class="text-muted">{{ $from->format('l') }}</small>
                                @else
                                    <div><strong>{{ $from->format('d M') }} - {{ $to->format('d M Y') }}</strong></div>
                                    <small class="text-muted">{{ $from->format('D') }} to {{ $to->format('D') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $holiday->name }}</div>
                                @if($holiday->is_recurring_yearly)
                                    <span class="badge bg-label-info text-capitalize" style="font-size: 0.7rem;"><i class="bx bx-repeat"></i> Yearly Recurring</span>
                                @endif
                            </td>
                            <td>
                                @if($holiday->type === 'public')
                                    <span class="badge holiday-badge-public">Public Holiday</span>
                                @elseif($holiday->type === 'national')
                                    <span class="badge holiday-badge-national">National Holiday</span>
                                @elseif($holiday->type === 'company')
                                    <span class="badge holiday-badge-company">Company Holiday</span>
                                @elseif($holiday->type === 'optional')
                                    <span class="badge holiday-badge-optional">Optional Holiday</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($holiday->type) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-label-primary px-3 py-2 fs-7">
                                    {{ $holiday->total_days }} {{ Str::plural('Day', $holiday->total_days) }}
                                </span>
                            </td>
                            <td>
                                <span>{{ $holiday->description ?: '-' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('admin.holidays.edit', $holiday->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Holiday">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-holiday-btn" data-url="{{ route('admin.holidays.destroy', $holiday->id) }}" title="Delete Holiday">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bx bx-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                                <h6>No holidays found.</h6>
                                <p class="mb-3">Start by adding your organization's holiday list for attendance and payroll.</p>
                                <a href="{{ route('admin.holidays.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-plus me-1"></i> Add New Holiday
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($holidays->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $holidays->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
$(function(){
    $('.delete-holiday-btn').click(function(){
        var url = $(this).data('url');
        Swal.fire({
            title: 'Delete Holiday?',
            text: "This holiday record will be removed. Attendance calculations might be affected.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#8592a3'
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
