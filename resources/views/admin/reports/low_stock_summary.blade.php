@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Low Stock Summary Report</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Report</button>
    </div>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.low-stock-summary') }}" class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by Part No, Spare Part Name, or Vehicle Variant...">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Items Below Minimum Threshold</h5>
            <span class="badge bg-danger">{{ count($lowStockItems) }} Item(s) Alert</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>Part / Fuel Code</th>
                        <th>Item Name</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Min Threshold</th>
                        <th class="text-center">Shortage</th>
                        <th>Alert Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockItems as $row)
                    <tr>
                        <td><span class="badge {{ $row['type'] === 'Spare Part' ? 'bg-label-info' : 'bg-label-primary' }}">{{ $row['type'] }}</span></td>
                        <td class="fw-bold">{{ $row['code'] }}</td>
                        <td class="fw-semibold">{{ $row['name'] }}</td>
                        <td class="text-center fw-bold fs-6 {{ $row['current_stock'] == 0 ? 'text-danger' : 'text-warning' }}">
                            {{ $row['current_stock'] }}
                        </td>
                        <td class="text-center fw-semibold text-muted">{{ $row['min_stock'] }}</td>
                        <td class="text-center fw-bold text-danger">{{ $row['shortage'] }}</td>
                        <td>
                            @if($row['current_stock'] == 0)
                                <span class="badge bg-danger"><i class="bx bx-error me-1"></i> Out of Stock</span>
                            @else
                                <span class="badge bg-warning"><i class="bx bx-error-circle me-1"></i> Low Stock</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['type'] === 'Spare Part')
                                <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-xs btn-outline-primary">
                                    <i class="bx bx-plus me-1"></i> Create Part PO
                                </a>
                            @else
                                <a href="{{ route('admin.vehicle-purchase-orders.create') }}" class="btn btn-xs btn-outline-primary">
                                    <i class="bx bx-plus me-1"></i> Create Vehicle PO
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-success fw-semibold">
                            <i class="bx bx-check-circle fs-4 d-block mb-1"></i>
                            All inventory stock levels are optimal! No low stock alerts.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
