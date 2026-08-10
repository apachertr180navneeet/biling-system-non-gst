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
            <form method="GET" action="{{ route('admin.reports.item-sales-purchase-summary') }}" class="row g-3">
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

    <!-- Table Card -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Item Sales & Purchase Movement Summary</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
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
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No items found for summary.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
