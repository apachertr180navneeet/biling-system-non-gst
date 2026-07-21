@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Reports /</span> Vehicle Stock Ledger
    </h4>

    <!-- Search Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports.vehicle-ledger') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Vehicle Description</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by name/description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="chassis_number" class="form-label">Chassis Number</label>
                    <input type="text" name="chassis_number" id="chassis_number" class="form-control" placeholder="Chassis No..." value="{{ request('chassis_number') }}">
                </div>
                <div class="col-md-3">
                    <label for="engine_number" class="form-label">Engine Number</label>
                    <input type="text" name="engine_number" id="engine_number" class="form-control" placeholder="Engine No..." value="{{ request('engine_number') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <a href="{{ route('admin.reports.vehicle-ledger') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards / Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Stock Balance Summary</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr class="table-light">
                        <th>Vehicle Description</th>
                        <th class="text-center">Total In</th>
                        <th class="text-center">Total Out</th>
                        <th class="text-center">Remaining Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaries as $s)
                    <tr>
                        <td><strong>{{ $s->vehicle_description }}</strong></td>
                        <td class="text-center text-success font-weight-bold">{{ $s->total_in }}</td>
                        <td class="text-center text-danger font-weight-bold">{{ $s->total_out }}</td>
                        <td class="text-center">
                            @php
                                $isLow = isset($s->min_stock) && $s->min_stock > 0 && $s->remaining <= $s->min_stock;
                            @endphp
                            <span class="badge bg-{{ $s->remaining < 1 ? 'secondary' : ($isLow ? 'danger' : 'success') }} fs-6">
                                {{ $s->remaining }}
                            </span>
                            @if($isLow && $s->remaining >= 1)
                            <span class="badge bg-danger ms-1">Low Stock (Min: {{ $s->min_stock }})</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center">No summaries available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Transactions Ledger -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detailed Transaction History</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date (In)</th>
                        <th>Date (Out)</th>
                        <th>Vehicle Description</th>
                        <th>Chassis No.</th>
                        <th>Engine No.</th>
                        <th>Price</th>
                        <th>PO Ref</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $item)
                    <tr>
                        <td>{{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            @if($item->status == 'sold')
                            {{ $item->updated_at ? $item->updated_at->format('Y-m-d H:i') : '-' }}
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $item->vehicle_description }}</td>
                        <td><code>{{ $item->chassis_number ?? '-' }}</code></td>
                        <td><code>{{ $item->engine_number ?? '-' }}</code></td>
                        <td>{{ number_format($item->purchase_price, 2) }}</td>
                        <td>
                            @if($item->purchaseOrder)
                            <a href="{{ route('admin.vehicle-purchase-orders.show', $item->purchaseOrder) }}">{{ $item->purchaseOrder->po_number }}</a>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if($item->status == 'available')
                            <span class="badge bg-success">In Stock</span>
                            @else
                            <span class="badge bg-danger">Sold</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center">No transaction records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $ledger->links() }}
        </div>
    </div>
</div>
@endsection
