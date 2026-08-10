@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Sales Summary - Category Wise</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Report</button>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.sales-summary-category-wise') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date Filter</label>
                    <select name="date_filter" class="form-select" onchange="this.form.submit()">
                        <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_30_days" {{ $dateFilter === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Financial Year</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card shadow-sm mb-4 border border-primary bg-label-primary">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="text-primary mb-1 fw-bold"><i class="bx bx-trending-up me-2"></i> Total Net Revenue Realized</h5>
                <small class="text-muted">Total net sales revenue across all categories for the selected period</small>
            </div>
            <h2 class="text-primary mb-0 fw-bold">₹{{ number_format($grandTotalSales, 2) }}</h2>
        </div>
    </div>

    <!-- Category Sales Table -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Revenue Breakdown By Category</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Category / Model Name</th>
                        <th>Type</th>
                        <th class="text-center">Units Sold</th>
                        <th class="text-end">Gross Amount</th>
                        <th class="text-end">Discounts / Incentives</th>
                        <th class="text-end">Net Revenue</th>
                        <th class="text-center">Revenue Share (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicleCategorySales as $row)
                    @php
                        $share = $grandTotalSales > 0 ? ($row['net_revenue'] / $grandTotalSales) * 100 : 0;
                    @endphp
                    <tr>
                        <td class="fw-bold">{{ $row['category'] }}</td>
                        <td><span class="badge {{ $row['type'] === 'Vehicle' ? 'bg-label-primary' : 'bg-label-info' }}">{{ $row['type'] }}</span></td>
                        <td class="text-center fw-bold fs-6">{{ $row['units_sold'] }}</td>
                        <td class="text-end">₹{{ number_format($row['gross_amount'], 2) }}</td>
                        <td class="text-end text-danger">-₹{{ number_format($row['discount'], 2) }}</td>
                        <td class="text-end fw-bold text-success fs-6">₹{{ number_format($row['net_revenue'], 2) }}</td>
                        <td class="text-center fw-bold text-primary">{{ number_format($share, 1) }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No sales recorded for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2" class="fw-bold">TOTAL SALES REVENUE</th>
                        <th class="text-center fw-bold">{{ array_sum(array_column($vehicleCategorySales, 'units_sold')) }}</th>
                        <th class="text-end fw-bold">₹{{ number_format(array_sum(array_column($vehicleCategorySales, 'gross_amount')), 2) }}</th>
                        <th class="text-end fw-bold text-danger">-₹{{ number_format(array_sum(array_column($vehicleCategorySales, 'discount')), 2) }}</th>
                        <th class="text-end fw-bold text-primary fs-5">₹{{ number_format($grandTotalSales, 2) }}</th>
                        <th class="text-center fw-bold">100%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
