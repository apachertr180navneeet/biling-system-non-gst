@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Stock Detail Report</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer me-1"></i> Print Report</button>
    </div>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.stock-detail-report') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by Chassis No, Motor No, or Vehicle Variant...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="available" {{ ($status ?? '') === 'available' ? 'selected' : '' }}>Available Only</option>
                        <option value="sold" {{ ($status ?? '') === 'sold' ? 'selected' : '' }}>Sold Only</option>
                        <option value="all" {{ ($status ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Vehicles Stock Details -->
    <div class="card shadow-sm mb-4">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-primary"><i class="bx bx-car me-2"></i> Vehicle Serialized Inventory Details</h5>
            <span class="badge bg-label-primary">{{ $vehicles->total() }} Vehicle Unit(s)</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Vehicle Description</th>
                        <th>Chassis Number</th>
                        <th>Motor / Engine No</th>
                        <th>Color</th>
                        <th>PO Ref</th>
                        <th class="text-end">Purchase Cost</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $idx => $v)
                    <tr>
                        <td>{{ $vehicles->firstItem() + $idx }}</td>
                        <td class="fw-bold">{{ $v->vehicle_description }}</td>
                        <td class="fw-bold text-dark">{{ $v->chassis_number }}</td>
                        <td>{{ $v->motor_number ?? $v->engine_number }}</td>
                        <td>{{ $v->color_name ?? '-' }}</td>
                        <td>{{ $v->purchaseOrder->po_number ?? '-' }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($v->purchase_price, 2) }}</td>
                        <td>
                            @if($v->status === 'available')
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-secondary">Sold</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No vehicle stock units found matching criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicles->hasPages())
        <div class="card-footer border-top">
            {{ $vehicles->links() }}
        </div>
        @endif
    </div>

    <!-- Spare Parts Stock Details -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-success"><i class="bx bx-wrench me-2"></i> Spare Parts Stock Inventory Details</h5>
            <span class="badge bg-label-success">{{ count($parts) }} Part Stock Record(s)</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Part Number</th>
                        <th>Part Name</th>
                        <th class="text-center">Current Quantity</th>
                        <th class="text-center">Min Reorder Level</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Total Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parts as $idx => $s)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="fw-bold">{{ $s->sparePart->part_no ?? '-' }}</td>
                        <td class="fw-semibold">{{ $s->sparePart->name ?? 'Part #' . $s->spare_part_id }}</td>
                        <td class="text-center fw-bold fs-6 {{ $s->quantity <= 0 ? 'text-danger' : 'text-success' }}">
                            {{ $s->quantity }}
                        </td>
                        <td class="text-center text-muted">{{ $s->sparePart->min_stock ?? 0 }}</td>
                        <td class="text-end">₹{{ number_format($s->purchase_price, 2) }}</td>
                        <td class="text-end fw-bold text-primary">₹{{ number_format($s->quantity * $s->purchase_price, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No spare part stock records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
