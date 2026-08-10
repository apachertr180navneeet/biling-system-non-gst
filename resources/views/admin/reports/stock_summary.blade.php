@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Overall Stock Summary & Valuation</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Summary</button>
    </div>

    <!-- Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-label-primary">
                <div class="card-body">
                    <span class="fw-semibold d-block text-muted mb-1">Available Vehicle Units</span>
                    <h3 class="card-title text-primary mb-0">{{ $totalVehicleQty }} Units</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-label-success">
                <div class="card-body">
                    <span class="fw-semibold d-block text-muted mb-1">Vehicle Stock Valuation</span>
                    <h3 class="card-title text-success mb-0">₹{{ number_format($totalVehicleValue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-label-info">
                <div class="card-body">
                    <span class="fw-semibold d-block text-muted mb-1">Spare Parts Stock Units</span>
                    <h3 class="card-title text-info mb-0">{{ $totalPartQty }} Units</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-label-warning">
                <div class="card-body">
                    <span class="fw-semibold d-block text-muted mb-1">Spare Parts Stock Valuation</span>
                    <h3 class="card-title text-warning mb-0">₹{{ number_format($totalPartValue, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Grand Total Valuation Card -->
    <div class="card shadow-sm border border-primary bg-primary text-white mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="text-white mb-1 fw-bold"><i class="bx bx-pie-chart-alt me-2"></i> Total Combined Inventory Asset Valuation</h5>
                <small class="text-white-50">Combined asset valuation of all available vehicles and spare parts in stock</small>
            </div>
            <h2 class="text-white mb-0 fw-bold">₹{{ number_format($totalVehicleValue + $totalPartValue, 2) }}</h2>
        </div>
    </div>

    <!-- Breakdown Table -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Category Wise Stock Asset Summary</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th class="text-center">Total Item Types</th>
                        <th class="text-center">Available Stock Units</th>
                        <th class="text-end">Total Valuation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold"><i class="bx bx-car text-primary me-2"></i> Vehicles (All Variants)</td>
                        <td class="text-center fw-bold">-</td>
                        <td class="text-center fw-bold text-primary fs-6">{{ $totalVehicleQty }}</td>
                        <td class="text-end fw-bold text-primary fs-6">₹{{ number_format($totalVehicleValue, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold"><i class="bx bx-wrench text-success me-2"></i> Spare Parts Inventory</td>
                        <td class="text-center fw-bold">{{ count($partStocks) }}</td>
                        <td class="text-center fw-bold text-success fs-6">{{ $totalPartQty }}</td>
                        <td class="text-end fw-bold text-success fs-6">₹{{ number_format($totalPartValue, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th class="fw-bold">TOTAL INVENTORY ASSETS</th>
                        <th class="text-center fw-bold">{{ count($partStocks) }}</th>
                        <th class="text-center fw-bold text-dark fs-6">{{ $totalVehicleQty + $totalPartQty }}</th>
                        <th class="text-end fw-bold text-primary fs-5">₹{{ number_format($totalVehicleValue + $totalPartValue, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
