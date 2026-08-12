@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Item Sales and Purchase Summary</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Summary</button>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.item-sales-purchase-summary') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search Item / Code</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" id="tableSearchInput" value="{{ $search ?? '' }}" class="form-control" placeholder="Search item, part no, variant...">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Category Type</label>
                    <select name="item_type" class="form-select">
                        <option value="">All Categories</option>
                        <option value="Spare Part" {{ ($itemType ?? '') === 'Spare Part' ? 'selected' : '' }}>Spare Part</option>
                        <option value="Vehicle" {{ ($itemType ?? '') === 'Vehicle' ? 'selected' : '' }}>Vehicle</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date Filter</label>
                    <select name="date_filter" id="dateFilterSelect" class="form-select">
                        <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_30_days" {{ $dateFilter === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Financial Year</option>
                        <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                    </select>
                </div>

                <div class="col-md-2 custom-date-field" style="display: {{ $dateFilter === 'custom' ? 'block' : 'none' }};">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" name="custom_from" value="{{ $customFrom ?? '' }}" class="form-control">
                </div>

                <div class="col-md-2 custom-date-field" style="display: {{ $dateFilter === 'custom' ? 'block' : 'none' }};">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" name="custom_to" value="{{ $customTo ?? '' }}" class="form-control">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    @if(!empty($search) || !empty($itemType) || $dateFilter !== 'this_month')
                        <a href="{{ route('admin.reports.item-sales-purchase-summary') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="bx bx-refresh"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Item Sales & Purchase Movement Summary</h5>
            <span class="badge bg-label-primary fs-7">{{ count($summary) }} Item(s) Shown</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle" id="summaryTable">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>Code / Part No</th>
                        <th>Item Name</th>
                        <th class="text-center">Purchase Qty</th>
                        <th class="text-end">Purchase Amount</th>
                        <th class="text-center">Sales Qty</th>
                        <th class="text-end">Sales Amount</th>
                        <th class="text-center">Net Qty Stocked</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summary as $row)
                    <tr>
                        <td><span class="badge {{ $row['type'] === 'Vehicle' ? 'bg-label-primary' : 'bg-label-info' }}">{{ $row['type'] }}</span></td>
                        <td class="fw-bold">{{ $row['code'] }}</td>
                        <td class="fw-semibold">{{ $row['name'] }}</td>
                        <td class="text-center fw-bold text-primary">{{ $row['purchase_qty'] }}</td>
                        <td class="text-end">₹{{ number_format($row['purchase_amt'], 2) }}</td>
                        <td class="text-center fw-bold text-success">{{ $row['sales_qty'] }}</td>
                        <td class="text-end fw-bold text-success">₹{{ number_format($row['sales_amt'], 2) }}</td>
                        <td class="text-center fw-bold {{ $row['net_qty'] >= 0 ? 'text-dark' : 'text-danger' }}">
                            {{ $row['net_qty'] }}
                        </td>
                    </tr>
                    @empty
                    <tr id="noResultsRow">
                        <td colspan="8" class="text-center py-4 text-muted">No items found for summary.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tableSearchInput');
    const tableRows = document.querySelectorAll('#summaryTable tbody tr:not(#noResultsRow)');
    const dateFilterSelect = document.getElementById('dateFilterSelect');
    const customDateFields = document.querySelectorAll('.custom-date-field');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    if (dateFilterSelect) {
        dateFilterSelect.addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            customDateFields.forEach(el => {
                el.style.display = isCustom ? 'block' : 'none';
            });
        });
    }
});
</script>
@endsection
